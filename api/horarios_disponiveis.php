<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$data = $_GET['data_consulta'] ?? null;
$id_medico = $_GET['id_medico'] ?? null;

if (!$data || !$id_medico) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Buscar os horários cadastrados no perfil do médico
$sql = $conn->prepare("SELECT horarios FROM medicos WHERE id = :id_medico");
$sql->bindParam(':id_medico', $id_medico);
$sql->execute();
$result = $sql->fetch();

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Médico não encontrado']);
    exit;
}

$horariosDisponiveis = json_decode($result['horarios'], true);

// Buscar horários já ocupados para esta data
$stmt = $conn->prepare("
    SELECT horario_consulta FROM consultas 
    WHERE id_medico = :id_medico AND data_consulta = :data_consulta AND status = 'agendada'
");
$stmt->execute([
    ':id_medico' => $id_medico,
    ':data_consulta' => $data
]);

$horariosOcupados = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Filtra os horários realmente disponíveis
$horariosLivres = array_values(array_diff($horariosDisponiveis, $horariosOcupados));

echo json_encode(['success' => true, 'horarios' => $horariosLivres]);
