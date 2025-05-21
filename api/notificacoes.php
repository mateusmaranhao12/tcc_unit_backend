<?php
include_once '../config/cors.php';
include_once '../db/database.php';

$destinatario = $_GET['destinatario'] ?? null;
$id = $_GET['id'] ?? null;

if (!$destinatario || !$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetros incompletos']);
    exit;
}

if ($destinatario === 'medico') {
    $stmt = $conn->prepare("SELECT * FROM notificacoes WHERE destinatario = 'medico' AND id_medico = ? ORDER BY criada_em DESC");
    $stmt->execute([$id]);
} elseif ($destinatario === 'paciente') {
    $stmt = $conn->prepare("SELECT * FROM notificacoes WHERE destinatario = 'paciente' AND id_paciente = ? ORDER BY criada_em DESC");
    $stmt->execute([$id]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Destinatário inválido']);
    exit;
}

$notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($notificacoes);
