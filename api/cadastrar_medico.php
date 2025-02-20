<?php

include_once '../config/cors.php';
include_once '../db/database.php';

header("Content-Type: application/json");

// Obtém os dados da requisição
$data = json_decode(file_get_contents("php://input"), true);

// Verifica se os dados foram recebidos corretamente
if (
    !$data ||
    empty($data['nome']) ||
    empty($data['sobrenome']) ||
    empty($data['email']) ||
    empty($data['dataNascimento']) ||
    empty($data['genero']) ||
    empty($data['crm']) ||
    empty($data['especialidade']) ||
    empty($data['telefone']) ||
    empty($data['cpf']) ||
    empty($data['endereco']) ||
    (!isset($data['horarios']) || !is_array(json_decode($data['horarios'], true)) || count(json_decode($data['horarios'], true)) === 0) ||
    empty($data['valorConsulta']) ||
    empty($data['imagem'])
) {
    echo json_encode(["success" => false, "message" => "Dados inválidos ou incompletos, preencha todos os campos obrigatórios"]);
    exit;
}

// Validação específica para a senha no back-end (apenas por segurança)
if (strlen($data['senha']) < 5) {
    echo json_encode(["success" => false, "message" => "A senha deve ter no mínimo 5 caracteres."]);
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

// Verifica se o CPF já está cadastrado
$checkQuery = "SELECT id FROM medicos WHERE cpf = :cpf";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bindParam(":cpf", $data['cpf']);
$checkStmt->execute();

if ($checkStmt->rowCount() > 0) {
    echo json_encode(["success" => false, "message" => "CPF já cadastrado"]);
    exit;
}

// Converte os horários selecionados para JSON
$horariosJSON = json_encode(json_decode($data['horarios'], true));

// Query para inserir no banco
$query = "INSERT INTO medicos (nome, sobrenome, email, senha, dataNascimento, genero, crm, especialidade, telefone, cpf, endereco, horarios, valorConsulta, imagem)
          VALUES (:nome, :sobrenome, :email, :senha, :dataNascimento, :genero, :crm, :especialidade, :telefone, :cpf, :endereco, :horarios, :valorConsulta, :imagem)";

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
$stmt->bindParam(":horarios", $horariosJSON);
$stmt->bindParam(":valorConsulta", $data['valorConsulta']);
$stmt->bindParam(":imagem", $imagem, PDO::PARAM_LOB);

// Executa a query e retorna resposta JSON
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Médico cadastrado com sucesso"]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao cadastrar médico"]);
}
