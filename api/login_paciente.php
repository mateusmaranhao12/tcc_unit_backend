<?php

include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

// Obtém os dados da requisição
$data = json_decode(file_get_contents('php://input'));

if (!isset($data->email) || !isset($data->senha)) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$email = trim($data->email);
$senha = trim($data->senha);

try {
    // Prepara a query para buscar a senha do usuário
    $query = $conn->prepare('SELECT id, senha FROM pacientes WHERE email = :email');
    $query->bindParam(':email', $email);
    $query->execute();

    if ($query->rowCount() > 0) {
        $user = $query->fetch(PDO::FETCH_ASSOC);
        if (password_verify($senha, $user['senha'])) {
            echo json_encode(['success' => true, 'message' => 'Login bem-sucedido', 'user_id' => $user['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Opsss! Senha incorreta, tente novamente.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
