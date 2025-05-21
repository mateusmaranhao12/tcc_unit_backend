<?php
include_once '../config/cors.php';
include_once '../db/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$email_medico = $data['email_medico'] ?? null;
$mensagem = $data['mensagem'] ?? null;
$url_destino = $data['url_destino'] ?? null;

if (!$email_medico || !$mensagem || !$url_destino) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Buscar o ID do médico
$stmt = $conn->prepare("SELECT id FROM medicos WHERE email = ?");
$stmt->execute([$email_medico]);
$medico = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$medico) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Médico não encontrado']);
    exit;
}

$id_medico = $medico['id'];

// Inserir notificação
$stmt = $conn->prepare("INSERT INTO notificacoes (id_medico, mensagem, url_destino) VALUES (?, ?, ?)");
if ($stmt->execute([$id_medico, $mensagem, $url_destino])) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao inserir notificação']);
}
