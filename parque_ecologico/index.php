<?php
/**
 * Index - Router Principal do Parque Ecológico
 * 
 * Arquivo de entrada único para toda a aplicação
 * Roteia requisições para controllers apropriados (páginas ou API)
 */

require_once __DIR__ . '/app/core/Session.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
$allowedOrigins = [];

if ($host !== '') {
    $scheme = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
        ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
    ) ? 'https' : 'http';
    $allowedOrigins[] = $scheme . '://' . $host;
}

$configuredOrigin = getenv('APP_URL') ?: '';
if ($configuredOrigin !== '') {
    $allowedOrigins[] = rtrim($configuredOrigin, '/');
}

if ($origin !== '' && in_array(rtrim($origin, '/'), $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header("Access-Control-Allow-Credentials: true");
    header("Vary: Origin");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Cross-Origin-Embedder-Policy: unsafe-none");
header("Cross-Origin-Opener-Policy: same-origin");
header("Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once './app/core/Router.php';

Router::route();

?>
