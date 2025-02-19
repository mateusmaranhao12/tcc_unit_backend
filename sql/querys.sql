/*Criar banco de dados*/
CREATE DATABASE tcc_unit

/*Tabela de pacientes*/
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    dataNascimento DATE NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    endereco VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    genero ENUM('Masculino', 'Feminino', 'Outro') NOT NULL,
    convenio VARCHAR(100) NOT NULL,
    historico TEXT NULL,
    imagem LONGBLOB NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/*Tabela de médicos*/
CREATE TABLE medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    dataNascimento DATE NOT NULL,
    genero ENUM('Masculino', 'Feminino', 'Outro') NOT NULL,
    crm VARCHAR(20) NOT NULL UNIQUE,
    especialidade VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    endereco VARCHAR(255) NOT NULL,
    horario VARCHAR(255) NOT NULL,
    valorConsulta DECIMAL(10, 2) NOT NULL,
    imagem LONGBLOB NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);