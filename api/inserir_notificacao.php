<?php
include_once '../config/cors.php';
include_once '../db/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$destinatario = $data['destinatario'] ?? null; // 'medico' ou 'paciente'
$id_destinatario = $data['id_destinatario'] ?? null;
$mensagem = $data['mensagem'] ?? null;
$url_destino = $data['url_destino'] ?? null;

if (!$destinatario || !$id_destinatario || !$mensagem || !$url_destino) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Inserir notificação conforme o destinatário
if ($destinatario === 'medico') {
    $stmt = $conn->prepare("INSERT INTO notificacoes (destinatario, id_medico, mensagem, url_destino) VALUES (?, ?, ?, ?)");
    $stmt->execute([$destinatario, $id_destinatario, $mensagem, $url_destino]);
} elseif ($destinatario === 'paciente') {
    $stmt = $conn->prepare("INSERT INTO notificacoes (destinatario, id_paciente, mensagem, url_destino) VALUES (?, ?, ?, ?)");
    $stmt->execute([$destinatario, $id_destinatario, $mensagem, $url_destino]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Destinatário inválido']);
    exit;
}

echo json_encode(['success' => true]);
