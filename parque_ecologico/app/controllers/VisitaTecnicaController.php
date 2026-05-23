<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/VisitaTecnica.php';
require_once __DIR__ . '/../models/Bloqueio.php';
require_once __DIR__ . '/../helpers/rate_limit.php';
require_once __DIR__ . '/../helpers/validation.php';

class VisitaTecnicaController {

    private $conn;
    private $model;
    private $bloqueioModel;

    public function __construct() {
        $this->conn = Database::connect();
        $this->model = new VisitaTecnica($this->conn);
        $this->bloqueioModel = new Bloqueio($this->conn);
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
            FROM visita_tecnica
            WHERE data_visita = ?
            AND guia_id = ?
            AND status NOT IN ('rejeitado', 'cancelado')
            AND (
                horario_entrada < ?
                AND horario_saida > ?
            )
        ";

        $params = [
            $data['data_visita'],
            $data['guia_id'],
            $data['horario_saida'],
            $data['horario_entrada']
        ];

        if ($ignorarId !== null) {
            $sql .= " AND id <> ?";
            $params[] = (int) $ignorarId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }

    private function guiaAtivoExiste($guiaId) {
        $stmt = $this->conn->prepare("SELECT id FROM guias WHERE id = ? AND ativo = 1 LIMIT 1");
        $stmt->execute([(int) $guiaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    private function getGuiaAtivo($guiaId) {
        $stmt = $this->conn->prepare("SELECT id, nome FROM guias WHERE id = ? AND ativo = 1 LIMIT 1");
        $stmt->execute([(int) $guiaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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

        if (!emailValido($data['email'] ?? ''))
            return "E-mail inválido";

        $data['telefone'] = $this->normalizarTelefone($data['telefone'] ?? '');
        if (!preg_match('/^\d{10,11}$/', $data['telefone']))
            return "Telefone inválido";

        if (empty($data['guia_id']) || !ctype_digit((string) $data['guia_id']) || $data['guia_id'] < 1)
            return "Guia inválido";

        if (!$this->guiaAtivoExiste($data['guia_id']))
            return "Guia indisponível";

        if (empty($data['aceite_termos']) || !in_array($data['aceite_termos'], ['1', 1, true, 'true'], true))
            return "É obrigatório aceitar os termos";

        if (empty($data['data_visita']) || !$this->isValidDate($data['data_visita']))
            return "Data inválida";

        $dataVisita = strtotime($data['data_visita'] . ' 00:00:00');
        $minima = strtotime(date('Y-m-d', strtotime('+7 days')) . ' 00:00:00');
        $limite = strtotime(date('Y-m-d', strtotime('+3 months')) . ' 23:59:59');

        if ($dataVisita < $minima)
            return "Visitas devem ser agendadas com no mínimo 7 dia de antecedência";

        if ($dataVisita > $limite)
            return "Visitas podem ser feitas com no máximo 3 meses de antecedência";

        $bloqueio = $this->getBloqueio($data['data_visita']);
        if ($bloqueio) {
            $mensagem = trim($bloqueio['motivo'])
                ? "Data indisponível: " . $bloqueio['motivo']
                : "Data indisponível";
            return $mensagem;
        }

        if (date('N', $dataVisita) >= 6)
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

        if ($duracaoMin < 30)
            return "Tempo mínimo: 30 minutos";

        if ($duracaoMin > 240)
            return "Máximo permitido: 4 horas";

        if (
            empty($data['qtd_visitantes']) ||
            !ctype_digit((string) $data['qtd_visitantes']) ||
            $data['qtd_visitantes'] < 1
        ) {
            return "Quantidade inválida";
        }

        if ($data['qtd_visitantes'] > 4) {
            return "Cada visita técnica permite no máximo 4 visitantes";
        }

        if (!empty($data['faixa_etaria']) && mb_strlen($data['faixa_etaria']) > 60)
            return "Faixa etária inválida";

        if (!empty($data['objetivo']) && mb_strlen($data['objetivo']) > 300)
            return "Objetivo inválido";

        return null;
    }

    public function store() {

        header('Content-Type: application/json; charset=utf-8');

        if (!checkRateLimit('visita_store', 20, 3600)) {
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

            echo json_encode([
                "erro" => "Payload inválido"
            ]);

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
            $guia = $this->getGuiaAtivo($data['guia_id']);
            if (!$guia) {
                http_response_code(422);
                echo json_encode(["erro" => "Guia indisponível"]);
                return;
            }
            $data['guia'] = $guia['nome'];

            $lockKey = sprintf('visita:%s:%s', $data['data_visita'], $data['guia_id']);
            if (!$this->acquireLock($lockKey)) {
                http_response_code(409);
                echo json_encode(["erro" => "Não foi possível confirmar disponibilidade. Tente novamente."]);
                return;
            }

            $this->conn->beginTransaction();

            if ($this->horarioConflitante($data)) {
                $this->conn->rollBack();
                http_response_code(409);
                echo json_encode(["erro" => "Guia já reservado nesse horário"]);
                return;
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO clientes (email, nome_responsavel, nome_instituicao, nome_diretor, telefone)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   nome_responsavel = VALUES(nome_responsavel),
                   nome_instituicao = VALUES(nome_instituicao),
                   nome_diretor = VALUES(nome_diretor),
                   telefone = VALUES(telefone)
                "
            );

            $stmt->execute([
                $data['email'],
                $data['nome_responsavel'],
                $data['nome_instituicao'] ?? null,
                $data['nome_diretor'] ?? null,
                $data['telefone'] ?? null
            ]);

            $this->model->create($data);
            $this->conn->commit();

            echo json_encode([
                "mensagem" => "Pedido de visita técnica realizada com sucesso. Notificaremos por email quando for aprovada ou rejeitada."
            ]);

        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Visita store error: " . $e->getMessage());

            http_response_code(500);

            echo json_encode([
                "erro" => "Erro ao salvar visita técnica"
            ]);
        } finally {
            if (isset($lockKey)) {
                $this->releaseLock($lockKey);
            }
        }
    }

    public function index() {

        header('Content-Type: application/json');

        echo json_encode(
            $this->model->getAll()
        );
    }

    public function aprovar($id) {
        $this->atualizarStatus($id, 'aprovado');
    }

    public function rejeitar($id) {
        $this->atualizarStatus($id, 'rejeitado');
    }

    private function atualizarStatus($id, $status) {
        header('Content-Type: application/json; charset=utf-8');

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

        $stmt = $this->conn->prepare("SELECT * FROM visita_tecnica WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $visita = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$visita) {
            http_response_code(404);
            echo json_encode(["erro" => "Visita técnica não encontrada"]);
            return;
        }

        $lockKey = sprintf('visita:%s:%s', $visita['data_visita'], $visita['guia_id']);

        try {
            if (!$this->acquireLock($lockKey)) {
                http_response_code(409);
                echo json_encode(["erro" => "Não foi possível confirmar disponibilidade. Tente novamente."]);
                return;
            }

            $this->conn->beginTransaction();

            if ($status === 'aprovado' && $this->horarioConflitante($visita, $id)) {
                $this->conn->rollBack();
                http_response_code(409);
                echo json_encode(["erro" => "Existe conflito de horário para este guia"]);
                return;
            }

            $stmt = $this->conn->prepare("UPDATE visita_tecnica SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $this->conn->commit();

            echo json_encode(["mensagem" => ucfirst($status) . " com sucesso"]);
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Visita status error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar status"]);
        } finally {
            if (isset($lockKey)) {
                $this->releaseLock($lockKey);
            }
        }
    }

    public function delete($id) {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["erro" => "ID inválido"]);
            return;
        }

        $stmt = $this->conn->prepare("DELETE FROM visita_tecnica WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(["erro" => "Visita técnica não encontrada"]);
            return;
        }

        echo json_encode(["mensagem" => "Excluído com sucesso"]);
    }
}
?> 
