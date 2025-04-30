<?php
include_once '../config/cors.php';
include_once '../db/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// Verifica se o CPF já está em uso por outro médico (com outro e-mail)
$verificaCpf = $conn->prepare("SELECT email FROM medicos WHERE cpf = :cpf AND email != :email");
$verificaCpf->bindParam(':cpf', $data['cpf']);
$verificaCpf->bindParam(':email', $data['email']);
$verificaCpf->execute();

if ($verificaCpf->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Este CPF já está cadastrado por outro médico.']);
    exit;
}

// Verifica se o CRM já está em uso por outro médico (com outro e-mail)
$verificaCrm = $conn->prepare("SELECT email FROM medicos WHERE crm = :crm AND email != :email");
$verificaCrm->bindParam(':crm', $data['crm']);
$verificaCrm->bindParam(':email', $data['email']);
$verificaCrm->execute();

if ($verificaCrm->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Este CRM já está cadastrado por outro médico.']);
    exit;
}

// Validação de CPF
if (strlen($data['cpf']) < 14) {
    echo json_encode(["success" => false, "message" => "CPF inválido."]);
    exit;
}

// Validação de CRM
if (strlen($data['crm']) < 7) {
    echo json_encode(["success" => false, "message" => "CRM inválido."]);
    exit;
}

// Validação de Telefone
if (strlen($data['telefone']) < 15) {
    echo json_encode(["success" => false, "message" => "Número de telefone inválido."]);
    exit;
}

// Validação de idade mínima de 21 anos
$hoje = new DateTime();
$dataNascimento = new DateTime($data['dataNascimento']);
$idade = $hoje->diff($dataNascimento)->y;

if ($idade < 21) {
    echo json_encode(["success" => false, "message" => "O médico deve ter no mínimo 21 anos."]);
    exit;
}

// Prepara o UPDATE
$query = "UPDATE medicos SET 
            nome = :nome,
            sobrenome = :sobrenome,
            dataNascimento = :dataNascimento,
            genero = :genero,
            crm = :crm,
            especialidade = :especialidade,
            telefone = :telefone,
            cpf = :cpf,
            endereco = :endereco,
            horarios = :horarios,
            valorConsulta = :valorConsulta,
            imagem = :imagem
          WHERE email = :email";

$stmt = $conn->prepare($query);

// Trata imagem (caso venha base64)
$imagem = !empty($data['imagem']) ? base64_decode($data['imagem']) : null;

// Trata os horários (converte array em JSON)
$horariosJson = isset($data['horarios']) && is_array($data['horarios']) ? json_encode($data['horarios']) : '[]';

// Bind dos parâmetros
$stmt->bindParam(':nome', $data['nome']);
$stmt->bindParam(':sobrenome', $data['sobrenome']);
$stmt->bindParam(':dataNascimento', $data['dataNascimento']);
$stmt->bindParam(':genero', $data['genero']);
$stmt->bindParam(':crm', $data['crm']);
$stmt->bindParam(':especialidade', $data['especialidade']);
$stmt->bindParam(':telefone', $data['telefone']);
$stmt->bindParam(':cpf', $data['cpf']);
$stmt->bindParam(':endereco', $data['endereco']);
$stmt->bindParam(':horarios', $horariosJson);
$stmt->bindParam(':valorConsulta', $data['valorConsulta']);
$stmt->bindParam(':imagem', $imagem, PDO::PARAM_LOB);
$stmt->bindParam(':email', $data['email']);

// Executa e retorna o resultado
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Dados atualizados com sucesso.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar.']);
}
