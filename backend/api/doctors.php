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

// ------- GET /doctors ou GET /doctors?id= -------
if ($method === "GET") {

    // ---------- DETALHES DO MÉDICO ----------
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);

        // SELECT com todos os novos campos
        $sql = "
            SELECT 
                d.id AS doctor_id,
                u.name,
                u.email,
                d.crm,
                d.phone,
                d.formation,
                d.location,
                d.specialty,
                d.bio
            FROM doctors d
            JOIN users u ON u.id = d.user_id
            WHERE d.id = $id
        ";

        $res = $conn->query($sql);

        if ($res->num_rows === 0) {
            echo json_encode(["error" => "Médico não encontrado"]);
            exit;
        }

        $doctor = $res->fetch_assoc();

        // --------- TRADUÇÃO DOS DIAS ---------
        $weekdayMap = [
            "mon" => "Segunda-feira",
            "tue" => "Terça-feira",
            "wed" => "Quarta-feira",
            "thu" => "Quinta-feira",
            "fri" => "Sexta-feira",
            "sat" => "Sábado",
            "sun" => "Domingo"
        ];

        // --------- HORÁRIOS ----------
        $sqlHorarios = "
            SELECT weekday, start_time, end_time
            FROM availabilities
            WHERE doctor_id = $id
        ";

        $resultHorarios = $conn->query($sqlHorarios);

        $horarios = [];
        while ($h = $resultHorarios->fetch_assoc()) {
            // garante que exista a chave weekday e converte pra minúsculas
            $weekdayKey = isset($h['weekday']) ? strtolower($h['weekday']) : '';

            if (isset($weekdayMap[$weekdayKey])) {
                $h['weekday_pt'] = $weekdayMap[$weekdayKey];
            } else {
                // se não encontrou tradução, mantém o valor original (ou string vazia)
                $h['weekday_pt'] = isset($h['weekday']) ? $h['weekday'] : '';
            }

            $horarios[] = $h;
        }

        // 🔥 Retorno COMPLETO
        echo json_encode([
            "id"         => $doctor["doctor_id"],
            "nome"       => $doctor["name"],
            "email"      => $doctor["email"],
            "crm"        => $doctor["crm"],
            "phone"      => $doctor["phone"],
            "formation"  => $doctor["formation"],
            "location"   => $doctor["location"],
            "specialty"  => $doctor["specialty"],
            "bio"        => $doctor["bio"],
            "horarios"   => $horarios
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
            "id"   => $row["doctor_id"],
            "nome" => $row["name"]
        ];
    }

    echo json_encode($data);
    exit;
}

// Se cair aqui, método inválido
echo json_encode(["error" => "Método não permitido"]);
exit;

