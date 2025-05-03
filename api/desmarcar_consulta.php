<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID da consulta não fornecido.']);
    exit;
}

$sql = $conn->prepare("UPDATE consultas SET status = 'cancelada' WHERE id = :id");
$sql->bindParam(':id', $data['id']);

if ($sql->execute()) {
    echo json_encode(['success' => true, 'message' => 'Consulta cancelada com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cancelar consulta.']);
}
