<?php
// Configurações de CORS
header("Access-Control-Allow-Origin: *"); // Permite requisições de qualquer origem
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Cabeçalhos permitidos

// Se for uma requisição OPTIONS, parar o script aqui
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
