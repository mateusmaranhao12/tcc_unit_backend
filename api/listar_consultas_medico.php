<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

if (!isset($_GET['email']) || empty($_GET['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email do médico não fornecido.']);
    exit;
}

$emailMedico = $_GET['email'];

// Buscar ID do medico
$sqlMedico = $conn->prepare("SELECT id FROM medicos WHERE email = :email");
$sqlMedico->bindParam(':email', $emailMedico);
$sqlMedico->execute();
$medico = $sqlMedico->fetch();

if (!$medico) {
    echo json_encode(['success' => false, 'message' => 'Médico não encontrado']);
    exit;
}

$idMedico = $medico['id'];
$dataHoje = date('Y-m-d');

// Buscar consultas futuras com nome do paciente
try {
    $stmt = $conn->prepare("
        SELECT 
            c.id, c.data_consulta, c.horario_consulta,
            p.nome AS nome_paciente, p.sobrenome AS sobrenome_paciente
        FROM consultas c
        JOIN pacientes p ON c.id_paciente = p.id
        WHERE c.id_medico = :id_medico AND c.data_consulta >= :data_hoje
        ORDER BY c.data_consulta, c.horario_consulta
    ");

    $stmt->bindParam(':id_medico', $idMedico);
    $stmt->bindParam(':data_hoje', $dataHoje);
    $stmt->execute();

    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'consultas' => $consultas]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar consultas.']);
}
