<?php
// ------- CORS -------
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ------- CONFIG -------
require_once __DIR__ . '/../config.php';

// ------- CONEXÃO (IGUAL LOGIN.PHP) -------
$conn = new mysqli(
    $config['databaseHost'],
    $config['databaseUser'],
    $config['databasePassword'],
    $config['databaseName']
);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    echo json_encode(["error" => "Erro ao conectar ao banco"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ------- GET /doctors / GET /doctors?id= -------
if ($method === "GET") {

    // ---------- DETALHES DO MÉDICO ----------
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        $sql = "SELECT d.id AS doctor_id, u.name, u.email, d.crm, d.bio
                FROM doctors d
                JOIN users u ON u.id = d.user_id
                WHERE d.id = $id";

        $res = $conn->query($sql);

        if ($res->num_rows === 0) {
            echo json_encode(["error" => "Médico não encontrado"]);
            exit;
        }

        $doctor = $res->fetch_assoc();

        // -------- HORÁRIOS --------
        $sqlHorarios = "SELECT weekday, start_time, end_time
                        FROM availabilities
                        WHERE doctor_id = $id";

        $resultHorarios = $conn->query($sqlHorarios);

        $horarios = [];
        while ($h = $resultHorarios->fetch_assoc()) {
            $horarios[] = $h;
        }

        echo json_encode([
            "id" => $doctor["doctor_id"],
            "nome" => $doctor["name"],
            "email" => $doctor["email"],
            "crm" => $doctor["crm"],
            "bio" => $doctor["bio"],
            "horarios" => $horarios
        ]);
        exit;
    }

    // ---------- LISTA DE MÉDICOS ----------
    $sql = "SELECT d.id AS doctor_id, u.name
            FROM doctors d
            JOIN users u ON u.id = d.user_id";

    $res = $conn->query($sql);

    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = [
            "id" => $row["doctor_id"],
            "nome" => $row["name"]
        ];
    }

    echo json_encode($data);
    exit;
}

echo json_encode(["error" => "Método não permitido"]);
exit;
