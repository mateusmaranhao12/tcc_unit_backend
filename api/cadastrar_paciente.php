<?php

include_once '../config/cors.php';
include_once '../db/database.php';

header("Content-Type: application/json");

// Verifica se os dados foram enviados corretamente
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Dados inválidos"]);
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
