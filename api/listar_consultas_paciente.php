<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email não fornecido']);
    exit;
}

// Buscar ID do paciente
$stmt = $conn->prepare("SELECT id FROM pacientes WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
$paciente = $stmt->fetch();

if (!$paciente) {
    echo json_encode(['success' => false, 'message' => 'Paciente não encontrado']);
    exit;
}

// Buscar consultas do paciente com info do médico
$stmt = $conn->prepare("
    SELECT c.id, c.data_consulta, c.horario_consulta, c.status, c.id_medico, m.nome AS nome_medico, m.sobrenome AS sobrenome_medico
    FROM consultas c
    JOIN medicos m ON c.id_medico = m.id
    JOIN pacientes p ON c.id_paciente = p.id
    WHERE p.email = :email AND c.status
    ORDER BY c.data_consulta ASC, c.horario_consulta ASC
");
$stmt->bindParam(':email', $_GET['email']);
$stmt->execute();
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'consultas' => $consultas]);
