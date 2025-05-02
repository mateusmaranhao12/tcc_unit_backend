<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

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

$id_paciente = $paciente['id'];
$id_medico = $medico['id'];
$data_consulta = $data['data_consulta'];
$horario_consulta = $data['horario_consulta'];

// Verifica se já existe uma consulta marcada com o mesmo médico, data e horário
$verificaMedico = $conn->prepare("
    SELECT 1 FROM consultas 
    WHERE id_medico = :id_medico AND data_consulta = :data_consulta AND horario_consulta = :horario_consulta
");
$verificaMedico->execute([
    ':id_medico' => $id_medico,
    ':data_consulta' => $data_consulta,
    ':horario_consulta' => $horario_consulta
]);

if ($verificaMedico->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Este médico já possui uma consulta neste horário.']);
    exit;
}

// Verifica se o paciente já marcou com qualquer médico no mesmo dia e horário
$verificaPaciente = $conn->prepare("
    SELECT 1 FROM consultas 
    WHERE id_paciente = :id_paciente AND data_consulta = :data_consulta AND horario_consulta = :horario_consulta
");
$verificaPaciente->execute([
    ':id_paciente' => $id_paciente,
    ':data_consulta' => $data_consulta,
    ':horario_consulta' => $horario_consulta
]);

if ($verificaPaciente->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Você já possui uma consulta marcada neste horário.']);
    exit;
}

// Inserir nova consulta
$insert = $conn->prepare("
    INSERT INTO consultas (id_paciente, id_medico, data_consulta, horario_consulta) 
    VALUES (:id_paciente, :id_medico, :data_consulta, :horario_consulta)
");

$insert->bindParam(':id_paciente', $id_paciente);
$insert->bindParam(':id_medico', $id_medico);
$insert->bindParam(':data_consulta', $data_consulta);
$insert->bindParam(':horario_consulta', $horario_consulta);

if ($insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Consulta agendada com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao agendar consulta.']);
}
