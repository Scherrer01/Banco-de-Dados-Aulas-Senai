create database livros;

use livros;

CREATE TABLE Autores (
    id_autor INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome_autor VARCHAR(100) NOT NULL,
    nacionalidade VARCHAR(50),
    nascimento_autor DATE
);

CREATE TABLE Editoras (
    id_editora INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome_editora VARCHAR(100) NOT NULL,
    telefone_editora VARCHAR(14),
    contato_editora VARCHAR(14),
    cidade VARCHAR(100),
    cnpj VARCHAR(18),
    endereco VARCHAR(100)
);

CREATE TABLE Livros (
    id_livro INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_autor INT NOT NULL,
    id_editora INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    genero VARCHAR(100),
    quantidade INT,
    preco DECIMAL(6,2),
    FOREIGN KEY(id_autor) REFERENCES Autores(id_autor),
    FOREIGN KEY(id_editora) REFERENCES Editoras(id_editora)
);

CREATE TABLE Clientes (
    id_cliente INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(100) NOT NULL,
    telefone_cliente VARCHAR(14),
    nascimento_cliente DATE,
    email VARCHAR(100),
    cpf VARCHAR(14)
);

CREATE TABLE Vendas (
    id_venda INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_livro INT NOT NULL,
    data_venda DATE,
    quantidade INT,
    valor_total DECIMAL(6,2),
    FOREIGN KEY(id_cliente) REFERENCES Clientes(id_cliente),
    FOREIGN KEY(id_livro) REFERENCES Livros(id_livro)
);

CREATE TABLE Escrever (
    id_autor INT NOT NULL,
    id_livro INT NOT NULL,
    PRIMARY KEY(id_autor, id_livro),
    FOREIGN KEY(id_autor) REFERENCES Autores(id_autor),
    FOREIGN KEY(id_livro) REFERENCES Livros(id_livro)
);

CREATE TABLE Publicam (
    id_editora INT NOT NULL,
    id_livro INT NOT NULL,
    PRIMARY KEY(id_editora, id_livro),
    FOREIGN KEY(id_editora) REFERENCES Editoras(id_editora),
    FOREIGN KEY(id_livro) REFERENCES Livros(id_livro)
);

INSERT INTO Autores (nome_autor, nacionalidade, nascimento_autor)
VALUES 
('Carlos Ruiz', 'Espanha', '1964-03-05'),
('J.K. Rowling', 'Reino Unido', '1965-07-31');

ALTER TABLE Editoras
MODIFY contato_editora VARCHAR(50);

INSERT INTO Editoras (nome_editora, telefone_editora, contato_editora, cidade, cnpj, endereco)
VALUES 
('Editora Alfa', '11987654321', 'contato@alfa.com', 'São Paulo', '12.345.678/0001-90', 'Rua A, 123'),
('Editora Beta', '11912345678', 'contato@beta.com', 'Rio de Janeiro', '98.765.432/0001-10', 'Rua B, 456');

INSERT INTO Livros (id_autor, id_editora, titulo, genero, quantidade, preco)
VALUES 
(1, 1, 'A Sombra do Vento', 'Romance', 10, 49.90),
(2, 2, 'Harry Potter e a Pedra Filosofal', 'Fantasia', 15, 59.90);

INSERT INTO Clientes (nome_cliente, telefone_cliente, nascimento_cliente, email, cpf)
VALUES 
('João Silva', '11999887766', '1990-05-20', 'joao@gmail.com', '123.456.789-00'),
('Maria Santos', '11988776655', '1985-12-10', 'maria@gmail.com', '987.654.321-00');

INSERT INTO Vendas (id_cliente, id_livro, data_venda, quantidade, valor_total)
VALUES
(1, 1, '2025-09-12', 1, 49.90),
(2, 2, '2025-09-12', 2, 119.80);

INSERT INTO Escrever (id_autor, id_livro)
VALUES
(1, 1),
(2, 2);

INSERT INTO Publicam (id_editora, id_livro)
VALUES
(1, 1),
(2, 2);

SELECT * FROM autores;
SELECT * FROM clientes;
SELECT * FROM editoras;
SELECT * FROM escrever;
SELECT * FROM livros;
SELECT * FROM publicam;
SELECT * FROM vendas;