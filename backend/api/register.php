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

// Campos do formulário (compatível com PHP antigo, sem ??)
$name           = isset($input['name'])     ? trim(strip_tags($input['name']))     : '';
$email          = isset($input['email'])    ? trim(strip_tags($input['email']))    : '';
$password       = isset($input['password']) ? trim($input['password'])             : '';
$cpf            = isset($input['cpf'])      ? trim(strip_tags($input['cpf']))      : '';
$convenioNumber = isset($input['convenio']) ? trim(strip_tags($input['convenio'])) : '';
$role           = 'patient'; // garante que role saia como patient

// Validar campos obrigatórios
if ($name === '' || $email === '' || $password === '' || $cpf === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Preencha todos os campos obrigatórios.']);
    exit;
}

// Verificar se email ou cpf já estão cadastrados
$check = $conn->prepare("SELECT id FROM users WHERE email = ? OR cpf = ? LIMIT 1");
$check->bind_param("ss", $email, $cpf);
$check->execute();
$exists = $check->get_result();

if ($exists->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Email ou CPF já cadastrado.']);
    exit;
}

// Criptografar senha
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Inserir usuário na tabela users (inclui role explicitamente)
$stmt = $conn->prepare("
    INSERT INTO users (name, email, password, cpf, convenio_number, role, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("ssssss", $name, $email, $hashedPassword, $cpf, $convenioNumber, $role);

// Execução
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Conta criada com sucesso!',
        'user_id' => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar usuário.']);
}
