<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

// Lê o JSON enviado no body
$data = json_decode(file_get_contents('php://input'), true);

// Validação básica
if (
    empty($data['email_paciente']) ||
    empty($data['email_medico']) ||
    empty($data['data_consulta']) ||
    empty($data['horario_consulta'])
) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

// Buscar ID do paciente
$sqlPaciente = $conn->prepare("SELECT id FROM pacientes WHERE email = :email");
$sqlPaciente->bindParam(':email', $data['email_paciente']);
$sqlPaciente->execute();
$paciente = $sqlPaciente->fetch();

if (!$paciente) {
    echo json_encode(['success' => false, 'message' => 'Paciente não encontrado.']);
    exit;
}

// Buscar ID do médico
$sqlMedico = $conn->prepare("SELECT id FROM medicos WHERE email = :email");
$sqlMedico->bindParam(':email', $data['email_medico']);
$sqlMedico->execute();
$medico = $sqlMedico->fetch();

if (!$medico) {
    echo json_encode(['success' => false, 'message' => 'Médico não encontrado.']);
    exit;
}

// Inserir consulta
$insert = $conn->prepare("INSERT INTO consultas (id_paciente, id_medico, data_consulta, horario_consulta) 
                          VALUES (:id_paciente, :id_medico, :data_consulta, :horario_consulta)");

$insert->bindParam(':id_paciente', $paciente['id']);
$insert->bindParam(':id_medico', $medico['id']);
$insert->bindParam(':data_consulta', $data['data_consulta']);
$insert->bindParam(':horario_consulta', $data['horario_consulta']);

if ($insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Consulta agendada com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao agendar consulta.']);
}
