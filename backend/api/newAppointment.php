<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config.php';

// ----- CONEXÃO MYSQLI -----
$conn = new mysqli(
    $config['databaseHost'],
    $config['databaseUser'],
    $config['databasePassword'],
    $config['databaseName']
);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Erro ao conectar ao banco"]);
    exit;
}

// ----- RECEBE JSON DO ANGULAR -----
$data = json_decode(file_get_contents("php://input"));

$user_id = intval($data->user_id);
$doctor_id = intval($data->doctor_id);
$date = date('Y-m-d', strtotime($data->date));
$time = $data->time;

// ----- INSERT -----
$stmt = $conn->prepare(
    "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time) 
     VALUES (?, ?, ?, ?)"
);

$stmt->bind_param("iiss", $user_id, $doctor_id, $date, $time);

$ok = $stmt->execute();

echo json_encode([
    "success" => $ok,
    "message" => $ok ? "Agendamento criado!" : "Erro ao agendar."
]);

exit;
