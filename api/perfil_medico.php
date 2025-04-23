<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$email = $_GET['email'] ?? null;

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email não fornecido']);
    exit;
}

$query = "SELECT nome, sobrenome, email, dataNascimento, genero, crm, especialidade, telefone, cpf, endereco, horarios, valorConsulta, imagem FROM medicos WHERE email = :email LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'message' => 'Médico não encontrado']);
    exit;
}

$pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

// Converte imagem para base64, se houver
if (!empty($pessoa['imagem'])) {
    $pessoa['imagem'] = 'data:image/jpeg;base64,' . base64_encode($pessoa['imagem']);
} else {
    $pessoa['imagem'] = null;
}

// Decodifica os horários (esperando que venham salvos como JSON string no banco)
if (!empty($pessoa['horarios'])) {
    $pessoa['horarios'] = json_decode($pessoa['horarios']);
} else {
    $pessoa['horarios'] = [];
}

// Retorna o resultado como JSON
echo json_encode([
    'success' => true,
    'medico' => $pessoa
]);
