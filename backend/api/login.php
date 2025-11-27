<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


require_once('../config.php');
require_once('../vendor/autoload.php'); // JWT lib do composer

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Configurações
$jwt_secret = 'S3CR3TK3YDR4G0NB444LW444TCH653976';
$jwt_expiration = 60 * 15; // 15 minutos

// Conexão com banco
$conn = new mysqli(
  $config['databaseHost'],
  $config['databaseUser'],
  $config['databasePassword'],
  $config['databaseName']
);
$conn->set_charset("utf8");

if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['error' => 'Erro ao conectar no banco']);
  exit;
}

// Ler JSON enviado pelo Angular
$input = json_decode(file_get_contents('php://input'), true);

// Sanitize básico
$name = isset($input['name']) ? trim(strip_tags($input['name'])) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

// Validar
if ($name === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Preencha todos os campos.']);
  exit;
}

// Consultar usuário
$stmt = $conn->prepare("SELECT * FROM users WHERE name = ? LIMIT 1");
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(401);
  echo json_encode(['error' => 'Conta não encontrada.']);
  exit;
}

$user = $result->fetch_assoc();

// Verificar senha
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Senha incorreta.']);
    exit;
}

// Gerar token
$payload = [
  'sub' => $user['id'],
  'name' => htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'),
  'nickname' => isset($user['nickname']) ? htmlspecialchars($user['nickname'], ENT_QUOTES, 'UTF-8') : null,
  'exp' => time() + $jwt_expiration
];

$jwt = JWT::encode($payload, $jwt_secret, 'HS256');

// Resposta
echo json_encode([
  'token' => $jwt,
  'user_id' => $user['id'], // <-- adicione isto
  'name' => htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'),
  'nickname' => isset($user['nickname']) ? htmlspecialchars($user['nickname'], ENT_QUOTES, 'UTF-8') : null,
]);
