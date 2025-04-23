<?php

include_once '../config/cors.php';
include_once '../db/database.php';

header("Content-Type: application/json");

try {
    $query = "SELECT nome, sobrenome, email, imagem FROM pacientes";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pacientes as &$paciente) {
        if (!empty($paciente['imagem'])) {
            $imagem = $paciente['imagem'];
            $mimeType = finfo_buffer(finfo_open(), $imagem, FILEINFO_MIME_TYPE);
            $paciente['imagem'] = 'data:' . $mimeType . ';base64,' . base64_encode($imagem);
        } else {
            $paciente['imagem'] = null; // ou uma string vazia
        }
    }


    echo json_encode(["success" => true, "pacientes" => $pacientes]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro ao buscar pacientes: " . $e->getMessage()]);
}
