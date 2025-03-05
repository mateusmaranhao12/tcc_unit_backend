<?php

include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

// Obtém os dados da requisição
$data = json_decode(file_get_contents('php://input'));

if (!isset($data->email) || !isset($data->crm) || !isset($data->senha)) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$email = trim($data->email);
$crm = trim($data->crm);
$senha = trim($data->senha);

try {
    // Prepara a query para buscar a senha do médico
    $query = $conn->prepare('SELECT id, senha FROM medicos WHERE email = :email AND crm = :crm');
    $query->bindParam(':email', $email);
    $query->bindParam(':crm', $crm);
    $query->execute();

    if ($query->rowCount() > 0) {
        $medico = $query->fetch(PDO::FETCH_ASSOC);
        if (password_verify($senha, $medico['senha'])) {
            echo json_encode(['success' => true, 'message' => 'Login bem-sucedido', 'user_id' => $medico['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Opsss! Senha incorreta, tente novamente.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Médico não encontrado ou CRM incorreto']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
