-- Criar o banco de dados
CREATE DATABASE FornecimentoDB;
USE FornecimentoDB;

-- Tabela Fornecedor
CREATE TABLE Fornecedor (
    Fcodigo INT PRIMARY KEY,
    Fnome VARCHAR(100) NOT NULL,
    Status VARCHAR(20) DEFAULT 'Ativo',
    Cidade VARCHAR(50)
);

-- Tabela Peca
CREATE TABLE Peca (
    Pcodigo INT PRIMARY KEY,
    Pnome VARCHAR(100) NOT NULL,
    Cor VARCHAR(30) NOT NULL,
    Peso DECIMAL(10,2) NOT NULL,
    Cidade VARCHAR(50) NOT NULL
);

-- Tabela Instituicao
CREATE TABLE Instituicao (
    Icodigo INT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- Tabela Projeto
CREATE TABLE Projeto (
    PRcod INT PRIMARY KEY,
    PRnome VARCHAR(100) NOT NULL,
    Cidade VARCHAR(50),
    Icodigo INT,
    FOREIGN KEY (Icodigo) REFERENCES Instituicao(Icodigo)
);

-- Tabela Fornecimento
CREATE TABLE Fornecimento (
    Fcodigo INT,
    Pcodigo INT,
    PRcod INT,
    Quantidade INT NOT NULL,
    PRIMARY KEY (Fcodigo, Pcodigo, PRcod),
    FOREIGN KEY (Fcodigo) REFERENCES Fornecedor(Fcodigo),
    FOREIGN KEY (Pcodigo) REFERENCES Peca(Pcodigo),
    FOREIGN KEY (PRcod) REFERENCES Projeto(PRcod)
);

-- Remover tabela Instituicao (se existir)
DROP TABLE IF EXISTS Instituicao;

-- Criar tabela Cidade
CREATE TABLE Cidade (
    Ccod INT PRIMARY KEY,
    Cnome VARCHAR(50) NOT NULL,
    uf CHAR(2) NOT NULL
);

-- Alterar tabela Fornecedor
ALTER TABLE Fornecedor 
ADD Fone VARCHAR(20),
ADD Ccod INT,
ADD FOREIGN KEY (Ccod) REFERENCES Cidade(Ccod);

-- Remover coluna Cidade da tabela Fornecedor
ALTER TABLE Fornecedor DROP COLUMN Cidade;

-- Alterar tabela Peca
ALTER TABLE Peca 
ADD Ccod INT,
ADD FOREIGN KEY (Ccod) REFERENCES Cidade(Ccod);

-- Remover coluna Cidade da tabela Peca
ALTER TABLE Peca DROP COLUMN Cidade;

-- Alterar tabela Projeto
ALTER TABLE Projeto 
ADD Ccod INT,
ADD FOREIGN KEY (Ccod) REFERENCES Cidade(Ccod);

-- Remover colunas Cidade e Icodigo da tabela Projeto
ALTER TABLE Projeto DROP COLUMN Cidade;
ALTER TABLE Projeto DROP COLUMN Icodigo;

-- Renomear colunas para padrão novo
ALTER TABLE Fornecedor RENAME COLUMN Fcodigo TO Fcod;
ALTER TABLE Peca RENAME COLUMN Pcodigo TO Pcod;
ALTER TABLE Projeto RENAME COLUMN PRcod TO PRcod;

-- Índices para tabela Fornecedor
CREATE INDEX idx_fornecedor_nome ON Fornecedor(Fnome);
CREATE INDEX idx_fornecedor_status ON Fornecedor(Status);
CREATE INDEX idx_fornecedor_cidade ON Fornecedor(Ccod);

-- Índices para tabela Peca
CREATE INDEX idx_peca_nome ON Peca(Pnome);
CREATE INDEX idx_peca_cor ON Peca(Cor);
CREATE INDEX idx_peca_peso ON Peca(Peso);
CREATE INDEX idx_peca_cidade ON Peca(Ccod);

-- Índices para tabela Cidade
CREATE INDEX idx_cidade_nome ON Cidade(Cnome);
CREATE INDEX idx_cidade_uf ON Cidade(uf);

-- Índices para tabela Projeto
CREATE INDEX idx_projeto_nome ON Projeto(PRnome);
CREATE INDEX idx_projeto_cidade ON Projeto(Ccod);

-- Índices para tabela Fornecimento (muito importante para consultas de junção)
CREATE INDEX idx_fornecimento_quantidade ON Fornecimento(Quantidade);
CREATE INDEX idx_fornecimento_fornecedor ON Fornecimento(Fcod);
CREATE INDEX idx_fornecimento_peca ON Fornecimento(Pcod);
CREATE INDEX idx_fornecimento_projeto ON Fornecimento(PRcod);

-- Índice composto para consultas frequentes
CREATE INDEX idx_fornecimento_completo ON Fornecimento(Fcod, Pcod, PRcod, Quantidade);

-- Estrutura final das tabelas após todas as alterações
SHOW TABLES;

-- Descrever estrutura de cada tabela
DESC Fornecedor;
DESC Cidade;
DESC Peca;
DESC Projeto;
DESC Fornecimento;

-- Mostrar índices criados
SHOW INDEX FROM Fornecedor;
SHOW INDEX FROM Peca;
SHOW INDEX FROM Cidade;
SHOW INDEX FROM Projeto;
SHOW INDEX FROM Fornecimento;