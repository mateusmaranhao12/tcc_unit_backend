<?php

include_once '../config/cors.php';
include_once '../db/database.php';

header("Content-Type: application/json");

// Verifica se os dados foram enviados corretamente
$data = json_decode(file_get_contents("php://input"), true);

if (
    !$data ||
    empty($data['nome']) ||
    empty($data['sobrenome']) ||
    empty($data['email']) ||
    empty($data['dataNascimento']) ||
    empty($data['cpf']) ||
    empty($data['endereco']) ||
    empty($data['telefone']) ||
    empty($data['genero']) ||
    empty($data['convenio'])
) {
    echo json_encode(["success" => false, "message" => "Preencha todos os campos obrigatórios."]);
    exit;
}

// Validação específica para a senha
if (strlen($data['senha']) < 5) {
    echo json_encode(["success" => false, "message" => "A senha deve ter no mínimo 5 caracteres."]);
    exit;
}

// Validação de e-mail
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "E-mail inválido."]);
    exit;
}

// Validação de CPF
if (strlen($data['cpf']) < 14) {
    echo json_encode(["success" => false, "message" => "CPF inválido."]);
    exit;
}

// Validação de Telefone
if (strlen($data['telefone']) < 15) {
    echo json_encode(["success" => false, "message" => "Número de telefone inválido."]);
    exit;
}

// Verifica se o e-mail já está cadastrado
$checkQuery = "SELECT id FROM pacientes WHERE email = :email";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bindParam(":email", $data['email']);
$checkStmt->execute();

if ($checkStmt->rowCount() > 0) {
    echo json_encode(["success" => false, "message" => "E-mail já cadastrado"]);
    exit;
}

// Verifica se o CPF já está cadastrado
$checkQuery = "SELECT id FROM pacientes WHERE cpf = :cpf";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bindParam(":cpf", $data['cpf']);
$checkStmt->execute();

if ($checkStmt->rowCount() > 0) {
    echo json_encode(["success" => false, "message" => "CPF já cadastrado"]);
    exit;
}

// Query para inserir paciente
$query = "INSERT INTO pacientes (nome, sobrenome, email, senha, dataNascimento, cpf, endereco, telefone, genero, convenio, historico, imagem)
          VALUES (:nome, :sobrenome, :email, :senha, :dataNascimento, :cpf, :endereco, :telefone, :genero, :convenio, :historico, :imagem)";

$stmt = $conn->prepare($query);

// Encripta a senha antes de salvar
$data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);

// Converte imagem para formato BLOB (se enviada)
$imagem = null;
if (!empty($data['imagem'])) {
    $imagem = base64_decode($data['imagem']);
}

// Bind dos valores
$stmt->bindParam(":nome", $data['nome']);
$stmt->bindParam(":sobrenome", $data['sobrenome']);
$stmt->bindParam(":email", $data['email']);
$stmt->bindParam(":senha", $data['senha']);
$stmt->bindParam(":dataNascimento", $data['dataNascimento']);
$stmt->bindParam(":cpf", $data['cpf']);
$stmt->bindParam(":endereco", $data['endereco']);
$stmt->bindParam(":telefone", $data['telefone']);
$stmt->bindParam(":genero", $data['genero']);
$stmt->bindParam(":convenio", $data['convenio']);
$stmt->bindParam(":historico", $data['historico']);
$stmt->bindParam(":imagem", $imagem, PDO::PARAM_LOB);

// Executa a query e retorna resposta JSON
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Paciente cadastrado com sucesso"]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao cadastrar paciente"]);
}
