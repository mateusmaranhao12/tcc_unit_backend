<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$data_consulta = $data['data_consulta'] ?? null;
$horario_consulta = $data['horario_consulta'] ?? null;

if (!$id || !$data_consulta || !$horario_consulta) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE consultas 
        SET data_consulta = :data_consulta, 
            horario_consulta = :horario_consulta, 
            status = 'agendada' 
        WHERE id = :id
    ");
    $stmt->execute([
        ':data_consulta' => $data_consulta,
        ':horario_consulta' => $horario_consulta,
        ':id' => $id
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
