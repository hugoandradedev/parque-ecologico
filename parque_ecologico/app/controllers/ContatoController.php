<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Mensagem.php';
require_once __DIR__ . '/../helpers/rate_limit.php';
require_once __DIR__ . '/../helpers/validation.php';

class ContatoController {

    private $conn;
    private $model;

    public function __construct() {
        $this->conn = Database::connect();
        $this->model = new Mensagem($this->conn);
    }

    private function limpar($v) {
        if (!is_scalar($v)) {
            return '';
        }

        return trim(strip_tags((string) $v));
    }

    private function parseBoolean($value) {
        return in_array($value, [true, 1, '1', 'true'], true);
    }

    public function store() {
        header('Content-Type: application/json; charset=utf-8');

        if (!checkRateLimit('contato_store', 10, 3600)) {
            http_response_code(429);
            echo json_encode(["erro" => "Muitas solicitações. Tente novamente mais tarde."]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["erro" => "Payload inválido"]);
            return;
        }

        // validações simples
        $nome = $this->limpar($data['nome'] ?? '');
        $email = normalizarEmail($this->limpar($data['email'] ?? ''));
        $assunto = $this->limpar($data['assunto'] ?? '');
        $mensagem = $this->limpar($data['mensagem'] ?? '');

        if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
            http_response_code(422);
            echo json_encode(["erro" => "Preencha todos os campos obrigatórios."]);
            return;
        }

        if (mb_strlen($nome) > 120) {
            http_response_code(422);
            echo json_encode(["erro" => "Nome muito longo."]);
            return;
        }

        if (mb_strlen($assunto) > 150) {
            http_response_code(422);
            echo json_encode(["erro" => "Assunto muito longo."]);
            return;
        }

        if (mb_strlen($mensagem) > 2000) {
            http_response_code(422);
            echo json_encode(["erro" => "Mensagem muito longa."]);
            return;
        }

        if (!emailValido($email)) {
            http_response_code(422);
            echo json_encode(["erro" => "E-mail inválido."]);
            return;
        }

        // validar telefone opcional: deve ter 10 ou 11 dígitos quando informado
        $telefoneRaw = is_scalar($data['telefone'] ?? null)
            ? preg_replace('/[^0-9]/', '', (string) $data['telefone'])
            : '';
        if ($telefoneRaw !== '' && !preg_match('/^\d{10,11}$/', $telefoneRaw)) {
            http_response_code(422);
            echo json_encode(["erro" => "Telefone inválido. Use 10 ou 11 dígitos."]);
            return;
        }

        try {
            $this->model->create([
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefoneRaw,
                'assunto' => $assunto,
                'mensagem' => $mensagem
            ]);

            echo json_encode(["mensagem" => "Obrigado! Entraremos em contato em breve."]);

        } catch (Throwable $e) {
            error_log("Contato store error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao enviar mensagem"]);
        }
    }

    // Admin: listar mensagens
    public function index() {
        header('Content-Type: application/json');

        $filters = [];
        if (isset($_GET['lida'])) {
            $filters['lida'] = $_GET['lida'] === '1' || $_GET['lida'] === 'true';
        }
        if (isset($_GET['respondida'])) {
            $filters['respondida'] = $_GET['respondida'] === '1' || $_GET['respondida'] === 'true';
        }

        echo json_encode($this->model->getAll($filters));
    }

    public function updateStatus($id) {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["erro" => "Payload inválido"]);
            return;
        }

        try {
            if ($this->model->updateStatus($id, [
                'lida' => array_key_exists('lida', $data) ? $this->parseBoolean($data['lida']) : null,
                'respondida' => array_key_exists('respondida', $data) ? $this->parseBoolean($data['respondida']) : null,
            ])) {
                echo json_encode(["mensagem" => "Status atualizado"]);
                return;
            }

            http_response_code(422);
            echo json_encode(["erro" => "Nenhum status fornecido"]);
        } catch (Throwable $e) {
            error_log("Contato update error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar mensagem"]);
        }
    }

    public function delete($id) {
        header('Content-Type: application/json');

        $id = (int) $id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["erro" => "ID inválido"]);
            return;
        }

        try {
            if (!$this->model->delete($id)) {
                http_response_code(404);
                echo json_encode(["erro" => "Mensagem não encontrada"]);
                return;
            }
            echo json_encode(["mensagem" => "Mensagem excluída com sucesso"]);
        } catch (Throwable $e) {
            error_log("Contato delete error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao excluir mensagem"]);
        }
    }
}

?>
