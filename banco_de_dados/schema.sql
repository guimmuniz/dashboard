DROP DATABASE IF EXISTS dashboard;

CREATE DATABASE dashboard
	CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE dashboard;

CREATE TABLE usuario (
id INT PRIMARY KEY AUTO_INCREMENT, 
nome VARCHAR(100) NOT NULL, 
email VARCHAR(50) UNIQUE NOT NULL,  
senha VARCHAR(255) NOT NULL, 
data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE categoria (
id INT PRIMARY KEY AUTO_INCREMENT, 
id_usuario INT NOT NULL, 
nome VARCHAR(20) NOT NULL, 
tipo ENUM('entrada', 'saida') NOT NULL, 
limite_mensal DECIMAL(10,2),
FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE RESTRICT,
UNIQUE (id_usuario, nome)
);

CREATE TABLE transacao (
id INT PRIMARY KEY AUTO_INCREMENT,  
id_categoria INT NOT NULL, 
descricao VARCHAR(100), 
valor DECIMAL(10,2) NOT NULL, 
data_transacao DATE NOT NULL, 
FOREIGN KEY (id_categoria) REFERENCES categoria(id) ON DELETE RESTRICT
);

CREATE TABLE simulacao (
id INT PRIMARY KEY AUTO_INCREMENT, 
id_usuario INT NOT NULL, 
perfil ENUM('conservador', 'moderado', 'arrojado') NOT NULL, 
aporte_mensal DECIMAL(10,2) NOT NULL, 
prazo_meses INT NOT NULL, 
resultado DECIMAL(10,2) NOT NULL, 
data_simulacao DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE RESTRICT
);

-- Carteira: ações que cada usuário tem, com quantidade e preço médio pago
CREATE TABLE carteira (
id INT PRIMARY KEY AUTO_INCREMENT,
id_usuario INT NOT NULL,
ticker VARCHAR(10) NOT NULL,
mercado ENUM('B3', 'US') NOT NULL,
quantidade INT NOT NULL,
preco_medio DECIMAL(12,2) NOT NULL,
FOREIGN KEY (id_usuario) REFERENCES usuario(id) ON DELETE RESTRICT,
UNIQUE (id_usuario, ticker)
);

-- Última cotação de cada ação, compartilhada entre os usuários (radar e carteira).
-- Atualizada duas vezes por dia: atualizado_em guarda o horário da atualização (11:00 ou 16:00).
CREATE TABLE cotacao (
ticker VARCHAR(10) NOT NULL,
mercado ENUM('B3', 'US') NOT NULL,
preco DECIMAL(12,2) NOT NULL,
variacao_percent DECIMAL(8,2) NOT NULL,
moeda CHAR(3) NOT NULL,
atualizado_em DATETIME NOT NULL,
PRIMARY KEY (ticker, mercado)
);

