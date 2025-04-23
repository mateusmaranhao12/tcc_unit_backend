<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$email = $_GET['email'] ?? null;

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email não fornecido']);
    exit;
}

$query = "SELECT nome, sobrenome, email, dataNascimento, cpf, endereco, telefone, genero, convenio, historico, imagem FROM pacientes WHERE email = :email LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'message' => 'Paciente não encontrado']);
    exit;
}

$pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

// Se houver imagem, transforma para base64
if (!empty($pessoa['imagem'])) {
    $pessoa['imagem'] = 'data:image/jpeg;base64,' . base64_encode($pessoa['imagem']);
} else {
    $pessoa['imagem'] = null;
}

echo json_encode([
    'success' => true,
    'paciente' => $pessoa
]);
