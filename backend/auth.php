<?php
require_once('../config.php');
require_once('../vendor/autoload.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Mesma chave secreta usada no login
$jwt_secret = 'S3CR3TK3YDR4G0NB444LW444TCH653976';

// Lê os headers HTTP
$headers = getallheaders();

// Busca o header Authorization ignorando case
$authHeader = null;
foreach ($headers as $key => $value) {
    if (strtolower($key) === 'authorization') {
        $authHeader = $value;
        break;
    }
}

if ($authHeader === null) {
    http_response_code(401);
    echo json_encode(['error' => 'Token não fornecido.']);
    exit;
}

// Extrai o token do formato "Bearer TOKEN"
list($type, $token) = explode(' ', $authHeader, 2);
if (strcasecmp($type, 'Bearer') != 0 || empty($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Formato de token inválido.']);
    exit;
}

try {
    // Decodifica o token
    $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));

    // Exemplo: $decoded->sub é o ID do usuário
    $userId = $decoded->sub;
    $userName = $decoded->name;

    // Disponibiliza os dados do usuário para outras partes do código
    $authUser = [
        'id' => $userId,
        'name' => $userName,
        'nickname' => isset($decoded->nickname) ? $decoded->nickname : null
    ];

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido ou expirado.']);
    exit;
}
