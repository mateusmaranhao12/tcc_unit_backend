<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
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
