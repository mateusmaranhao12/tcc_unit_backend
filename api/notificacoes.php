<?php
include_once '../config/cors.php';
include_once '../db/database.php';

$id_medico = $_GET['id_medico'] ?? null;

if ($id_medico) {
    $stmt = $conn->prepare("SELECT * FROM notificacoes WHERE id_medico = ? AND lida = 0 ORDER BY criada_em DESC");
    $stmt->execute([$id_medico]);

    $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($notificacoes);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do médico não informado']);
}
