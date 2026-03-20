CREATE DATABASE IF NOT EXISTS ipca_gestao CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ipca_gestao;

-- Remover tabelas antigas se existirem
DROP TABLE IF EXISTS notas;
DROP TABLE IF EXISTS pautas;
DROP TABLE IF EXISTS matriculas;
DROP TABLE IF EXISTS fichas_aluno;
DROP TABLE IF EXISTS plano_estudos;
DROP TABLE IF EXISTS unidades_curriculares;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS utilizadores;

CREATE TABLE utilizadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('aluno', 'funcionario', 'gestor') NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    estado ENUM('Ativo', 'Inativo') DEFAULT 'Ativo'
);

CREATE TABLE unidades_curriculares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    estado ENUM('Ativo', 'Inativo') DEFAULT 'Ativo'
);

CREATE TABLE plano_estudos (
    curso_id INT NOT NULL,
    uc_id INT NOT NULL,
    ano INT NOT NULL,
    semestre INT NOT NULL,
    PRIMARY KEY(curso_id, uc_id),
    FOREIGN KEY(curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY(uc_id) REFERENCES unidades_curriculares(id) ON DELETE CASCADE
);

CREATE TABLE fichas_aluno (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    curso_id INT NULL,
    nome_aluno VARCHAR(255) NOT NULL,
    turma VARCHAR(50),
    data_nascimento DATE,
    bi VARCHAR(50) NOT NULL,
    foto VARCHAR(255),
    estado ENUM('Rascunho', 'Submetida', 'Aprovada', 'Rejeitada') DEFAULT 'Rascunho',
    observacoes TEXT,
    validado_por INT NULL,
    data_validacao DATETIME NULL,
    FOREIGN KEY(user_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
    FOREIGN KEY(curso_id) REFERENCES cursos(id) ON DELETE SET NULL,
    FOREIGN KEY(validado_por) REFERENCES utilizadores(id) ON DELETE SET NULL
);

CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    curso_id INT NOT NULL,
    estado ENUM('Pendente', 'Aprovado', 'Rejeitado') DEFAULT 'Pendente',
    observacoes TEXT,
    responsavel_id INT NULL,
    data_decisao DATETIME NULL,
    FOREIGN KEY(aluno_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
    FOREIGN KEY(curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY(responsavel_id) REFERENCES utilizadores(id) ON DELETE SET NULL
);

CREATE TABLE pautas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uc_id INT NOT NULL,
    ano_letivo VARCHAR(20) NOT NULL,
    epoca ENUM('Normal', 'Recurso', 'Especial') NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(uc_id) REFERENCES unidades_curriculares(id) ON DELETE CASCADE
);

CREATE TABLE notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pauta_id INT NOT NULL,
    aluno_id INT NOT NULL,
    nota_final DECIMAL(4,2) NULL,
    FOREIGN KEY(pauta_id) REFERENCES pautas(id) ON DELETE CASCADE,
    FOREIGN KEY(aluno_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
    UNIQUE(pauta_id, aluno_id)
);

-- INSERIR DADOS INICIAIS (Senha de todos: 123456)
INSERT INTO utilizadores (nome, email, senha, perfil) VALUES
('Gestor Pedagógico', 'gestor@ipca.pt', '$2y$10$AUMv3NhlC1/cOWtVwHByuOR.Xm29zW/M2R5e7yM1c0R./IohpWzRy', 'gestor'),
('Funcionário SA', 'func@ipca.pt', '$2y$10$AUMv3NhlC1/cOWtVwHByuOR.Xm29zW/M2R5e7yM1c0R./IohpWzRy', 'funcionario'),
('João Aluno', 'aluno@ipca.pt', '$2y$10$AUMv3NhlC1/cOWtVwHByuOR.Xm29zW/M2R5e7yM1c0R./IohpWzRy', 'aluno');

-- Alguns Cursos e UCs exemplo
INSERT INTO cursos (nome) VALUES ('Engenharia Informática'), ('Design Gráfico');
INSERT INTO unidades_curriculares (nome) VALUES ('Programação Web'), ('Bases de Dados'), ('Matemática');

INSERT INTO plano_estudos (curso_id, uc_id, ano, semestre) VALUES 
(1, 1, 1, 1),
(1, 2, 1, 2),
(1, 3, 1, 1);
