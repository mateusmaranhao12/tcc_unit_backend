<?php
include_once '../config/cors.php';
include_once '../db/database.php';

$email = $_GET['email'] ?? null;

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email não informado']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM pacientes WHERE email = ?");
$stmt->execute([$email]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($paciente) {
    echo json_encode(['success' => true, 'id' => $paciente['id']]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Médico não encontrado']);
}
