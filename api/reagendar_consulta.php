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

// Buscar dados da consulta atual para pegar id_medico e id_paciente
$consultaStmt = $conn->prepare("SELECT id_medico, id_paciente FROM consultas WHERE id = :id");
$consultaStmt->bindParam(':id', $id);
$consultaStmt->execute();
$consulta = $consultaStmt->fetch();

if (!$consulta) {
    echo json_encode(['success' => false, 'message' => 'Consulta não encontrada.']);
    exit;
}

$id_medico = $consulta['id_medico'];
$id_paciente = $consulta['id_paciente'];

// Verificar se o médico já tem outra consulta nesse horário (excluindo essa)
$verificaMedico = $conn->prepare("
    SELECT 1 FROM consultas
    WHERE id_medico = :id_medico
    AND data_consulta = :data_consulta
    AND horario_consulta = :horario_consulta
    AND id != :id
    AND status = 'agendada'
");
$verificaMedico->execute([
    ':id_medico' => $id_medico,
    ':data_consulta' => $data_consulta,
    ':horario_consulta' => $horario_consulta,
    ':id' => $id
]);

if ($verificaMedico->fetch()) {
    echo json_encode(['success' => false, 'message' => 'O médico já possui outra consulta neste horário.']);
    exit;
}

// Verificar se o paciente já tem outra consulta nesse horário (excluindo essa)
$verificaPaciente = $conn->prepare("
    SELECT 1 FROM consultas
    WHERE id_paciente = :id_paciente
    AND data_consulta = :data_consulta
    AND horario_consulta = :horario_consulta
    AND id != :id
    AND status = 'agendada'
");
$verificaPaciente->execute([
    ':id_paciente' => $id_paciente,
    ':data_consulta' => $data_consulta,
    ':horario_consulta' => $horario_consulta,
    ':id' => $id
]);

if ($verificaPaciente->fetch()) {
    echo json_encode(['success' => false, 'message' => 'O paciente ou o médico já possui uma consulta neste horário.']);
    exit;
}

// Se não houver conflitos, atualizar a consulta
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
