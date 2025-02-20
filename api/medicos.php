<?php

include_once '../config/cors.php';
include_once '../db/database.php';

header("Content-Type: application/json");

try {
    // Consulta para obter os médicos cadastrados
    $query = "SELECT nome, especialidade, imagem FROM medicos";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Converte a imagem para base64 para exibição no front-end
    foreach ($medicos as &$medico) {
        if ($medico['imagem']) {
            // Detecta o MIME type da imagem dinamicamente
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($medico['imagem']);

            // Converte a imagem para base64 com o MIME type correto
            $medico['imagem'] = 'data:' . $mimeType . ';base64,' . base64_encode($medico['imagem']);
        }
    }

    echo json_encode(["success" => true, "medicos" => $medicos]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erro ao buscar médicos: " . $e->getMessage()]);
}
