<?php

include_once '../config/cors.php';
include_once '../db/database.php';

header("Content-Type: application/json");

// Obtém os dados da requisição
$data = json_decode(file_get_contents("php://input"), true);

// Verifica se os dados foram recebidos corretamente
if (!$data || empty($data['nome']) || empty($data['email']) || empty($data['senha']) || empty($data['crm']) || empty($data['horarios'])) {
    echo json_encode(["success" => false, "message" => "Dados inválidos ou incompletos"]);
    exit;
}

// Verifica se o e-mail já está cadastrado
$checkQuery = "SELECT id FROM medicos WHERE email = :email";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bindParam(":email", $data['email']);
$checkStmt->execute();

if ($checkStmt->rowCount() > 0) {
    echo json_encode(["success" => false, "message" => "E-mail já cadastrado"]);
    exit;
}

// Converte os horários selecionados para JSON
$horariosJSON = json_encode(json_decode($data['horarios'], true));

// Query para inserir no banco
$query = "INSERT INTO medicos (nome, sobrenome, email, senha, dataNascimento, genero, crm, especialidade, telefone, cpf, endereco, horario, valorConsulta, imagem)
          VALUES (:nome, :sobrenome, :email, :senha, :dataNascimento, :genero, :crm, :especialidade, :telefone, :cpf, :endereco, :horario, :valorConsulta, :imagem)";

$stmt = $conn->prepare($query);

// Encripta a senha antes de salvar
$data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);

// Converte imagem para formato BLOB (se enviada)
$imagem = null;
if (!empty($data['imagem'])) {
    $imagem = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data['imagem']));
}

// Bind dos valores
$stmt->bindParam(":nome", $data['nome']);
$stmt->bindParam(":sobrenome", $data['sobrenome']);
$stmt->bindParam(":email", $data['email']);
$stmt->bindParam(":senha", $data['senha']);
$stmt->bindParam(":dataNascimento", $data['dataNascimento']);
$stmt->bindParam(":genero", $data['genero']);
$stmt->bindParam(":crm", $data['crm']);
$stmt->bindParam(":especialidade", $data['especialidade']);
$stmt->bindParam(":telefone", $data['telefone']);
$stmt->bindParam(":cpf", $data['cpf']);
$stmt->bindParam(":endereco", $data['endereco']);
$stmt->bindParam(":horario", $horariosJSON);
$stmt->bindParam(":valorConsulta", $data['valorConsulta']);
$stmt->bindParam(":imagem", $imagem, PDO::PARAM_LOB);

// Executa a query e retorna resposta JSON
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Médico cadastrado com sucesso"]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao cadastrar médico"]);
}
