<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

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

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao conectar ao banco"]);
    exit;
}

// Verifica token
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

// Tradução do Status
$statusMap = [
    'scheduled' => 'Agendado',
    'completed' => 'Concluído',
    'canceled'  => 'Cancelado'
];

// Consulta dos agendamentos
$stmt = $conn->prepare("
    SELECT 
        a.id,
        a.appointment_date AS date,
        a.appointment_time AS time,
        a.status,

        u.name AS doctor_name,
        d.crm,
        d.phone,
        d.formation,
        d.location,
        d.specialty,
        d.bio

    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];

while ($row = $result->fetch_assoc()) {

    // Tradução do status
    if (isset($statusMap[$row["status"]])) {
        $row["status_pt"] = $statusMap[$row["status"]];
    } else {
        $row["status_pt"] = $row["status"];
    }

    $appointments[] = [
        "id"     => $row["id"],
        "date"   => $row["date"],
        "time"   => $row["time"],
        "status_pt" => $row["status_pt"],

        "doctor" => [
            "name"      => $row["doctor_name"],
            "crm"       => $row["crm"],
            "phone"     => $row["phone"],
            "formation" => $row["formation"],
            "location"  => $row["location"],
            "specialty" => $row["specialty"],
            "bio"       => $row["bio"],
        ]
    ];
}

echo json_encode(["appointments" => $appointments]);
