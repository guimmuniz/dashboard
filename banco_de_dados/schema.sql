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

