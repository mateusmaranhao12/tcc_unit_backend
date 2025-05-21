<?php
include_once '../config/cors.php';
include_once '../db/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM notificacoes WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['status' => 'sucesso']);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da notificação não informado']);
}
