<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

date_default_timezone_set('America/Sao_Paulo'); // fuso horário padrão

$email = $_GET['email'] ?? null;

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email do médico não fornecido.']);
    exit;
}

// Buscar ID do médico
$sql = $conn->prepare("SELECT id FROM medicos WHERE email = :email");
$sql->bindParam(':email', $email);
$sql->execute();
$medico = $sql->fetch();

if (!$medico) {
    echo json_encode(['success' => false, 'message' => 'Médico não encontrado.']);
    exit;
}

$idMedico = $medico['id'];
$dataHoje = date('Y-m-d');
$horaAgora = date('H:i');

try {
    $stmt = $conn->prepare("
        SELECT 
            c.id, 
            c.id_medico,
            c.id_paciente,
            c.data_consulta, 
            c.horario_consulta, 
            c.status, 
            c.modalidade,
            p.nome AS nome_paciente, 
            p.sobrenome AS sobrenome_paciente
        FROM consultas c
        JOIN pacientes p ON c.id_paciente = p.id
        WHERE c.id_medico = :id_medico 
          AND c.status
          AND (
              c.data_consulta > :data_hoje
              OR (c.data_consulta = :data_hoje AND 
                  SUBSTRING_INDEX(c.horario_consulta, ' - ', 1) > :hora_agora)
          )
        ORDER BY c.data_consulta, c.horario_consulta
    ");

    $stmt->bindParam(':id_medico', $idMedico);
    $stmt->bindParam(':data_hoje', $dataHoje);
    $stmt->bindParam(':hora_agora', $horaAgora);
    $stmt->execute();

    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'consultas' => $consultas]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar consultas.']);
}
