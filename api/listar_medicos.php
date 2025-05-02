<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->query("SELECT nome, sobrenome, especialidade, horarios FROM medicos");
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decodifica os horários se estiverem em JSON
    foreach ($medicos as &$medico) {
        if (isset($medico['horarios'])) {
            $medico['horarios'] = json_decode($medico['horarios']);
        }
    }

    echo json_encode(['success' => true, 'medicos' => $medicos]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao listar médicos.']);
}
