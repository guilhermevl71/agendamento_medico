<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("../config.php");
require_once("../vendor/autoload.php");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$jwt_secret = 'S3CR3TK3YDR4G0NB444LW444TCH653976';

// Conecta ao banco
$conn = new mysqli(
  $config['databaseHost'],
  $config['databaseUser'],
  $config['databasePassword'],
  $config['databaseName']
);

$conn->set_charset("utf8");

// Verifica erro no banco
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(["error" => "Erro ao conectar ao banco"]);
  exit;
}

// Token obrigatório
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
  http_response_code(401);
  echo json_encode(["error" => "Token ausente"]);
  exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);

try {
  $decoded = JWT::decode($token, new Key($jwt_secret, 'HS256'));
  $user_id = $decoded->sub;
} catch (Exception $e) {
  http_response_code(401);
  echo json_encode(["error" => "Token inválido"]);
  exit;
}

// Busca agendamentos do usuário logado com nome do médico
$stmt = $conn->prepare("
    SELECT a.id, a.appointment_date AS date, a.appointment_time AS time, a.status, d.bio AS doctor
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
  $appointments[] = $row;
}

echo json_encode(["appointments" => $appointments]);
