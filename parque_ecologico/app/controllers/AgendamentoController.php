<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Agendamento.php';
require_once __DIR__ . '/../models/Bloqueio.php';
require_once __DIR__ . '/../helpers/rate_limit.php';
require_once __DIR__ . '/../helpers/validation.php';

class AgendamentoController {

    private $agendamentoModel;
    private $bloqueioModel;
    private $conn;

    public function __construct() {
        try {
            $this->conn = Database::connect();
            $this->agendamentoModel = new Agendamento($this->conn);
            $this->bloqueioModel = new Bloqueio($this->conn);
        } catch (Throwable $e) {
            error_log("Agendamento init error: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "erro" => "Erro ao inicializar serviço de agendamento"
            ]);
            exit;
        }
    }

    private function limpar($valor) {
        return strip_tags(trim((string) $valor));
    }

    private function normalizarTelefone($valor) {
        return preg_replace('/[^0-9]/', '', (string) $valor);
    }

    private function isValidDate($value) {
        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    private function isValidTime($value) {
        $time = DateTime::createFromFormat('H:i', $value);
        return $time && $time->format('H:i') === $value;
    }

    private function getBloqueio($data) {
        return $this->bloqueioModel->findByDate($data);
    }

    private function dataBloqueada($data) {
        return $this->getBloqueio($data) !== false;
    }

    private function horarioConflitante($data, $ignorarId = null) {
        $sql = "
            SELECT COUNT(*)
            FROM agendamento
            WHERE data_reserva = ?
            AND quiosque_id = ?
            AND status NOT IN ('rejeitado', 'cancelado')
            AND (
                horario_entrada < ?
                AND horario_saida > ?
            )
        ";

        $params = [
            $data['data_reserva'],
            $data['quiosque_id'],
            $data['horario_saida'],
            $data['horario_entrada']
        ];

        if ($ignorarId !== null) {
            $sql .= " AND id <> ?";
            $params[] = (int) $ignorarId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function acquireLock($key) {
        $stmt = $this->conn->prepare("SELECT GET_LOCK(?, 5)");
        $stmt->execute([$key]);
        return (int) $stmt->fetchColumn() === 1;
    }

    private function releaseLock($key) {
        $stmt = $this->conn->prepare("SELECT RELEASE_LOCK(?)");
        $stmt->execute([$key]);
    }

    private function validar($data) {

        foreach ($data as $k => $v) {
            $data[$k] = $this->limpar($v);
        }


        if (empty($data['nome_responsavel']) || strlen($data['nome_responsavel']) < 3)
            return "Responsável inválido";

        $erroEmail = obterErroEmail($data['email'] ?? '');
        if ($erroEmail !== null)
            return $erroEmail;

        $data['telefone'] = $this->normalizarTelefone($data['telefone'] ?? '');
        if (!preg_match('/^\d{10,11}$/', $data['telefone']))
            return "Telefone inválido";

        if (empty($data['aceite_termos']) || !in_array($data['aceite_termos'], ['1', 1, true, 'true'], true))
            return "É obrigatório aceitar os termos";

        if (
            empty($data['quiosque_id']) ||
            !ctype_digit((string) $data['quiosque_id']) ||
            $data['quiosque_id'] < 1 ||
            $data['quiosque_id'] > 20
        )
            return "Quiosque inválido";

        if (
            !isset($data['qtd_visitantes']) ||
            !is_numeric($data['qtd_visitantes']) ||
            $data['qtd_visitantes'] < 1
        )
            return "Quantidade inválida";

        if ($data['qtd_visitantes'] > 8)
            return "Cada quiosque permite no máximo 8 visitantes";

        if (empty($data['data_reserva']) || !$this->isValidDate($data['data_reserva']))
            return "Data inválida";

        $dataReserva = strtotime($data['data_reserva'] . ' 00:00:00');
        $minima = strtotime(date('Y-m-d', strtotime('+4 days')) . ' 00:00:00');
        $limite = strtotime(date('Y-m-d', strtotime('+3 months')) . ' 23:59:59');

        // NÃO pode ser hoje
        if ($dataReserva < $minima)
            return "Reservas devem ser feitas com no mínimo 4 dia de antecedência";

        // máximo 3 meses
        if ($dataReserva > $limite)
            return "Reservas podem ser feitas com no máximo 3 meses de antecedência";

        $bloqueio = $this->getBloqueio($data['data_reserva']);
        if ($bloqueio) {
            $mensagem = trim($bloqueio['motivo'])
                ? "Data indisponível para agendamento: " . $bloqueio['motivo']
                : "Data indisponível para agendamento";
            return $mensagem;
        }

        if (date('N', $dataReserva) >= 6)
            return "Somente dias úteis";

        if (empty($data['horario_entrada']) || !$this->isValidTime($data['horario_entrada']))
            return "Horário de entrada inválido";

        if (empty($data['horario_saida']) || !$this->isValidTime($data['horario_saida']))
            return "Horário de saída inválido";

        $entrada = strtotime($data['horario_entrada']);
        $saida = strtotime($data['horario_saida']);

        $min = strtotime("08:00");
        $max = strtotime("16:00");

        if ($entrada < $min || $entrada > $max)
            return "Entrada inválida";

        if ($saida < $min || $saida > $max)
            return "Saída inválida";

        if ($saida <= $entrada)
            return "Saída deve ser após entrada";

        $duracaoMin = ($saida - $entrada) / 60;

        // mínimo 30 min
        if ($duracaoMin < 30)
            return "O tempo mínimo de reserva é 30 minutos";

        // máximo 4h
        if ($duracaoMin > 240)
            return "Máximo permitido: 4 horas";

        return null;
    }

    public function index() {
        header('Content-Type: application/json');

        echo json_encode($this->agendamentoModel->getAll());
    }

    public function store() {
        

        header('Content-Type: application/json; charset=utf-8');

        if (!checkRateLimit('agendamento_store', 20, 3600)) {
            http_response_code(429);
            echo json_encode(["erro" => "Muitas solicitações. Tente novamente mais tarde."]);
            return;
        }

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["erro" => "Payload inválido"]);
            return;
        }

        $data = array_map([$this, 'limpar'], $data);
        $data['email'] = normalizarEmail($data['email'] ?? '');
        $data['telefone'] = $this->normalizarTelefone($data['telefone'] ?? '');
        $data['aceite_termos'] = isset($data['aceite_termos']) && in_array($data['aceite_termos'], ['1', 1, true, 'true'], true) ? 1 : 0;

        if ($erro = $this->validar($data)) {

            http_response_code(422);

            echo json_encode([
                "erro" => $erro
            ]);

            return;
        }

        try {
            $lockKey = sprintf('agendamento:%s:%s', $data['data_reserva'], $data['quiosque_id']);
            if (!$this->acquireLock($lockKey)) {
                http_response_code(409);
                echo json_encode(["erro" => "Não foi possível confirmar disponibilidade. Tente novamente."]);
                return;
            }

            $this->conn->beginTransaction();

            if ($this->horarioConflitante($data)) {
                $this->conn->rollBack();
                http_response_code(409);
                echo json_encode(["erro" => "Horário indisponível"]);
                return;
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO clientes (email, nome_responsavel, telefone)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   nome_responsavel = VALUES(nome_responsavel),
                   telefone = VALUES(telefone)
                "
            );

            $stmt->execute([
                $data['email'],
                $data['nome_responsavel'],
                $data['telefone'] ?? null
            ]);

            if ($this->agendamentoModel->create($data)) {
                $this->conn->commit();

                echo json_encode([
                    "mensagem" => "Pedido de reserva realizada com sucesso. Notificaremos por email quando for aprovada ou rejeitada."
                ]);

            } else {
                throw new Exception("Erro ao salvar");
            }

        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Agendamento store error: " . $e->getMessage());

            http_response_code(500);

            echo json_encode([
                "erro" => "Erro ao salvar agendamento"
            ]);
        } finally {
            if (isset($lockKey)) {
                $this->releaseLock($lockKey);
            }
        }
    }

    public function aprovar($id) {
        $this->atualizarStatus($id, 'aprovado');
    }

    public function rejeitar($id) {
        $this->atualizarStatus($id, 'rejeitado');
    }

    private function atualizarStatus($id, $status) {

        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["erro" => "ID inválido"]);
            return;
        }

        if (!in_array($status, ['aprovado', 'rejeitado'], true)) {
            http_response_code(400);
            echo json_encode(["erro" => "Status inválido"]);
            return;
        }

        $stmt = $this->conn->prepare("SELECT * FROM agendamento WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$agendamento) {
            http_response_code(404);
            echo json_encode(["erro" => "Agendamento não encontrado"]);
            return;
        }

        $lockKey = sprintf('agendamento:%s:%s', $agendamento['data_reserva'], $agendamento['quiosque_id']);

        try {
            if (!$this->acquireLock($lockKey)) {
                http_response_code(409);
                echo json_encode(["erro" => "Não foi possível confirmar disponibilidade. Tente novamente."]);
                return;
            }

            $this->conn->beginTransaction();

            if ($status === 'aprovado' && $this->horarioConflitante($agendamento, $id)) {
                $this->conn->rollBack();
                http_response_code(409);
                echo json_encode(["erro" => "Existe conflito de horário para este quiosque"]);
                return;
            }

            $stmt = $this->conn->prepare(
                "UPDATE agendamento
                SET status = ?
                WHERE id = ?"
            );

            $stmt->execute([$status, $id]);
            $this->conn->commit();
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Agendamento status error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar status"]);
            return;
        } finally {
            if (isset($lockKey)) {
                $this->releaseLock($lockKey);
            }
        }

        echo json_encode([
            "mensagem" => ucfirst($status) . " com sucesso"
        ]);
    }

    public function delete($id) {

        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["erro" => "ID inválido"]);
            return;
        }

        $stmt = $this->conn->prepare(
            "DELETE FROM agendamento
            WHERE id = ?"
        );

        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["erro" => "Agendamento não encontrado"]);
            return;
        }

        echo json_encode([
            "mensagem" => "Excluído com sucesso"
        ]);
    }
}
?>
