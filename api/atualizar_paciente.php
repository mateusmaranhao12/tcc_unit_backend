<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// Verifica se o CPF já está em uso por outro paciente
$verificaCpf = $conn->prepare("SELECT email FROM pacientes WHERE cpf = :cpf AND email != :email");
$verificaCpf->bindParam(':cpf', $data['cpf']);
$verificaCpf->bindParam(':email', $data['email']);
$verificaCpf->execute();

if ($verificaCpf->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Este CPF já está cadastrado por outro paciente.']);
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

$query = "UPDATE pacientes SET 
            nome = :nome,
            sobrenome = :sobrenome,
            dataNascimento = :dataNascimento,
            cpf = :cpf,
            endereco = :endereco,
            telefone = :telefone,
            genero = :genero,
            convenio = :convenio,
            historico = :historico,
            imagem = :imagem
          WHERE email = :email";

$stmt = $conn->prepare($query);

// Trata imagem (caso venha base64)
$imagem = !empty($data['imagem']) ? base64_decode($data['imagem']) : null;

$stmt->bindParam(':nome', $data['nome']);
$stmt->bindParam(':sobrenome', $data['sobrenome']);
$stmt->bindParam(':dataNascimento', $data['dataNascimento']);
$stmt->bindParam(':cpf', $data['cpf']);
$stmt->bindParam(':endereco', $data['endereco']);
$stmt->bindParam(':telefone', $data['telefone']);
$stmt->bindParam(':genero', $data['genero']);
$stmt->bindParam(':convenio', $data['convenio']);
$stmt->bindParam(':historico', $data['historico']);
$stmt->bindParam(':imagem', $imagem, PDO::PARAM_LOB);
$stmt->bindParam(':email', $data['email']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Dados atualizados com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar.']);
}
