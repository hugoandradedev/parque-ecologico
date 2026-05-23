<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../helpers/validation.php';

class GuiaController {

    private $conn;

    public function __construct() {
        $this->conn = Database::connect();
    }

    private function sanitizeField($value) {
        if (!is_scalar($value)) {
            return '';
        }

        return trim(strip_tags((string) $value));
    }

    private function normalizePhone($value) {
        $phone = preg_replace('/[^0-9]/', '', (string) $value);
        return $phone !== '' ? $phone : null;
    }

    public function index() {
        header('Content-Type: application/json');

        $stmt = $this->conn->prepare("SELECT id, nome, email, telefone, especialidade, ativo FROM guias ORDER BY nome");
        $stmt->execute();

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create() {
        Session::start();

        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["erro" => "Payload inválido"]);
            return;
        }

        $nome = $this->sanitizeField($data['nome'] ?? '');
        $email = normalizarEmail($this->sanitizeField($data['email'] ?? ''));
        $telefone = $this->normalizePhone($data['telefone'] ?? '');
        $especialidade = $this->sanitizeField($data['especialidade'] ?? '');
        $ativo = isset($data['ativo']) && ($data['ativo'] === true || $data['ativo'] === '1' || $data['ativo'] === 1) ? 1 : 0;

        if ($nome === '') {
            http_response_code(400);
            echo json_encode(["erro" => "Nome do guia é obrigatório"]);
            return;
        }

        if (strlen($nome) > 120) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome do guia não pode ter mais de 120 caracteres"]);
            return;
        }

        if ($email !== '' && !emailValido($email)) {
            http_response_code(400);
            echo json_encode(["erro" => "E-mail do guia inválido"]);
            return;
        }

        if (strlen($email) > 120) {
            http_response_code(400);
            echo json_encode(["erro" => "E-mail não pode ter mais de 120 caracteres"]);
            return;
        }

        if ($telefone !== null && !preg_match('/^\d{10,11}$/', $telefone)) {
            http_response_code(400);
            echo json_encode(["erro" => "Telefone inválido. Use 10 ou 11 dígitos"]);
            return;
        }

        if (strlen($especialidade) > 150) {
            http_response_code(400);
            echo json_encode(["erro" => "Especialidade não pode ter mais de 150 caracteres"]);
            return;
        }

        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO guias (nome, email, telefone, especialidade, ativo)
                 VALUES (?, ?, ?, ?, ?)");

            $stmt->execute([
                $nome,
                $email !== '' ? $email : null,
                $telefone !== '' ? $telefone : null,
                $especialidade !== '' ? $especialidade : null,
                $ativo
            ]);

            echo json_encode(["mensagem" => "Guia cadastrado com sucesso"]);

        } catch (Throwable $e) {
            error_log("Guia create error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar guia"]);
        }
    }

    public function update($id) {
        Session::start();

        header('Content-Type: application/json');

        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["erro" => "ID de guia inválido"]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["erro" => "Payload inválido"]);
            return;
        }
        $nome = $this->sanitizeField($data['nome'] ?? '');
        $email = normalizarEmail($this->sanitizeField($data['email'] ?? ''));
        $telefone = $this->normalizePhone($data['telefone'] ?? '');
        $especialidade = $this->sanitizeField($data['especialidade'] ?? '');
        $ativo = isset($data['ativo']) && ($data['ativo'] === true || $data['ativo'] === '1' || $data['ativo'] === 1) ? 1 : 0;

        if ($nome === '') {
            http_response_code(400);
            echo json_encode(["erro" => "Nome do guia é obrigatório"]);
            return;
        }

        if (strlen($nome) > 120) {
            http_response_code(400);
            echo json_encode(["erro" => "Nome do guia não pode ter mais de 120 caracteres"]);
            return;
        }

        if ($email !== '' && !emailValido($email)) {
            http_response_code(400);
            echo json_encode(["erro" => "E-mail do guia inválido"]);
            return;
        }

        if (strlen($email) > 120) {
            http_response_code(400);
            echo json_encode(["erro" => "E-mail não pode ter mais de 120 caracteres"]);
            return;
        }

        if ($telefone !== null && !preg_match('/^\d{10,11}$/', $telefone)) {
            http_response_code(400);
            echo json_encode(["erro" => "Telefone inválido. Use 10 ou 11 dígitos"]);
            return;
        }

        if (strlen($especialidade) > 150) {
            http_response_code(400);
            echo json_encode(["erro" => "Especialidade não pode ter mais de 150 caracteres"]);
            return;
        }

        try {
            $stmt = $this->conn->prepare(
                "UPDATE guias
                 SET nome = ?, email = ?, telefone = ?, especialidade = ?, ativo = ?
                 WHERE id = ?");

            $stmt->execute([
                $nome,
                $email !== '' ? $email : null,
                $telefone !== '' ? $telefone : null,
                $especialidade !== '' ? $especialidade : null,
                $ativo,
                $id
            ]);

            if ($stmt->rowCount() === 0) {
                $check = $this->conn->prepare("SELECT id FROM guias WHERE id = ?");
                $check->execute([$id]);
                if ($check->fetch(PDO::FETCH_ASSOC) === false) {
                    http_response_code(404);
                    echo json_encode(["erro" => "Guia não encontrado"]);
                    return;
                }
            }

            echo json_encode(["mensagem" => "Guia atualizado com sucesso"]);

        } catch (Throwable $e) {
            error_log("Guia update error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar guia"]);
        }
    }

    public function delete($id) {
        Session::start();

        header('Content-Type: application/json');

        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["erro" => "ID de guia inválido"]);
            return;
        }

        try {
            $checkUso = $this->conn->prepare("
                SELECT COUNT(*)
                FROM visita_tecnica
                WHERE guia_id = ?
                AND data_visita >= CURDATE()
                AND status NOT IN ('rejeitado', 'cancelado')
            ");
            $checkUso->execute([$id]);
            if ((int) $checkUso->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(["erro" => "Guia possui visitas futuras vinculadas"]);
                return;
            }

            $stmt = $this->conn->prepare("DELETE FROM guias WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(["erro" => "Guia não encontrado"]);
                return;
            }

            echo json_encode(["mensagem" => "Guia removido com sucesso"]);

        } catch (Throwable $e) {
            error_log("Guia delete error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao remover guia"]);
        }
    }
}

?>
