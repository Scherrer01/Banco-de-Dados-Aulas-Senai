-- Exercicio 2
-- Criar o banco de dados
CREATE DATABASE plataforma_cursos_online;
USE plataforma_cursos_online;

-- Tabela Alunos
CREATE TABLE Alunos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(55) NOT NULL,
    email VARCHAR(55),
    data_nascimento DATE NOT NULL
);

-- Tabela Cursos
CREATE TABLE Cursos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    carga_horaria INT NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo'
);

-- Tabela Inscrições
CREATE TABLE Inscricoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    aluno_id INT NOT NULL,
    curso_id INT NOT NULL,
    data_inscricao DATE NOT NULL,
    FOREIGN KEY (aluno_id) REFERENCES Alunos(id),
    FOREIGN KEY (curso_id) REFERENCES Cursos(id)
);

-- Tabela Avaliações
CREATE TABLE Avaliacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inscricao_id INT NOT NULL UNIQUE,
    nota DECIMAL(3,1) CHECK (nota >= 0 AND nota <= 10),
    comentario TEXT,
    FOREIGN KEY (inscricao_id) REFERENCES Inscricoes(id)
);

-- ==================================================================================================
-- ==================================================================================================

-- Exercicio 3

-- Inserir 5 alunos
INSERT INTO Alunos (nome, email, data_nascimento) VALUES
('João Silva', 'joao.silva@email.com', '1990-05-15'),
('Maria Santos', 'maria.santos@email.com', '1995-08-22'),
('Pedro Oliveira', 'pedro.oliveira@email.com', '1998-12-10'),
('Ana Costa', 'ana.costa@email.com', '1993-03-30'),
('Carlos Lima', 'carlos.lima@email.com', '2000-07-18');

-- 2. Inserir os cursos
INSERT INTO Cursos (titulo, descricao, carga_horaria, status) VALUES
('Programação Python', 'Curso completo de Python para iniciantes', 40, 'ativo'),
('Banco de Dados SQL', 'Fundamentos de SQL e modelagem de dados', 35, 'ativo'),
('Web Development', 'Desenvolvimento web com HTML, CSS e JavaScript', 60, 'ativo'),
('Data Science', 'Introdução à ciência de dados', 45, 'inativo'),
('Mobile Development', 'Desenvolvimento de apps móveis', 50, 'ativo');

-- 3. inserir as inscrições
INSERT INTO Inscricoes (aluno_id, curso_id, data_inscricao) VALUES
(1, 1, '2024-01-15'),  -- Esta será a inscrição ID 1
(2, 1, '2024-01-20'),  -- Esta será a inscrição ID 2  
(3, 2, '2024-02-01'),  -- Esta será a inscrição ID 3
(4, 3, '2024-02-10'),  -- Esta será a inscrição ID 4
(5, 5, '2024-02-15');  -- Esta será a inscrição ID 5


SELECT id, aluno_id, curso_id FROM Inscricoes;

INSERT INTO Avaliacoes (inscricao_id, nota, comentario) VALUES
(6, 9.5, 'Excelente curso, professor muito didático'),
(7, 8.0, 'Bom conteúdo, mas poderia ter mais exercícios'),
(8, 9.8, 'Curso fantástico, superou expectativas');

-- ==================================================================================================
-- ==================================================================================================

-- Exercicio 4
-- Atualizar email de um aluno
UPDATE Alunos SET email = 'joao.novo@gmail.com' WHERE id = 1;

-- Alterar carga horária de um curso
UPDATE Cursos SET carga_horaria = 55 WHERE id = 5;

-- Corrigir nome de aluno
UPDATE Alunos SET nome = 'Ana Carolina Costa' WHERE id = 4;

-- Mudar status de curso
UPDATE Cursos SET status = 'ativo' WHERE id = 4;

-- Alterar nota de uma avaliação
UPDATE Avaliacoes SET nota = 9.0 WHERE id = 2;

-- ====================================================================================================
-- ==================================================================================================

-- Exercício 5
-- Excluir uma inscrição (e sua avaliação associada se existir)
DELETE FROM Avaliacoes WHERE inscricao_id = 5;
DELETE FROM Inscricoes WHERE id = 5;

-- Excluir um curso (primeiro excluir avaliações e inscrições relacionadas)
DELETE av FROM Avaliacoes av
INNER JOIN Inscricoes i ON av.inscricao_id = i.id
WHERE i.curso_id = 4;

DELETE FROM Inscricoes WHERE curso_id = 4;
DELETE FROM Cursos WHERE id = 4;

-- Excluir uma avaliação ofensiva (supondo que a avaliação com id 2 seja ofensiva)
DELETE FROM Avaliacoes WHERE id = 2;

-- Excluir um aluno (primeiro excluir avaliações e inscrições relacionadas)
DELETE FROM Inscricoes WHERE id = 5;
DELETE FROM Avaliacoes WHERE id = 2;
SELECT COUNT(*) FROM Inscricoes WHERE aluno_id = 5;
DELETE FROM Alunos WHERE id = 5;

-- 4. Excluir um curso específico (apenas se não tiver inscrições)
SELECT COUNT(*) FROM Inscricoes WHERE curso_id = 4;

-- Se retornar 0, pode excluir:
DELETE FROM Cursos WHERE id = 4;


DELETE FROM Inscricoes WHERE aluno_id = 5;
DELETE FROM Alunos WHERE id = 5;

-- Excluir todas inscrições de um curso encerrado (supondo curso id 3)
DELETE av FROM Avaliacoes av
INNER JOIN Inscricoes i ON av.inscricao_id = i.id
WHERE i.curso_id = 3;

DELETE FROM Inscricoes WHERE curso_id = 3;

-- ==================================================================================================
-- ==================================================================================================

-- Exercicio 6
-- 1. Listar todos os alunos cadastrados
SELECT * FROM Alunos;

-- 2. Exibir apenas os nomes e e-mails dos alunos
SELECT nome, email FROM Alunos;

-- 3. Listar cursos com carga horária maior que 30 horas
SELECT * FROM Cursos WHERE carga_horaria > 30;

-- 4. Exibir cursos que estão inativos
SELECT * FROM Cursos WHERE status = 'inativo';

-- 5. Buscar alunos nascidos após o ano 1995
SELECT * FROM Alunos WHERE YEAR(data_nascimento) > 1995;

-- 6. Exibir avaliações com nota acima de 9
SELECT * FROM Avaliacoes WHERE nota > 9;

-- 7. Contar quantos cursos estão cadastrados
SELECT COUNT(*) as total_cursos FROM Cursos;

-- 8. Listar os 3 cursos com maior carga horária
SELECT * FROM Cursos ORDER BY carga_horaria DESC LIMIT 3;

-- ==================================================================================================
-- ==================================================================================================

