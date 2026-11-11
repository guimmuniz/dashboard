<?php
require __DIR__ . '/_comum.php';

// pega os dados que o formulário enviou
$dados = lerJson();
$nome  = trim($dados['nome'] ?? '');
$email = trim($dados['email'] ?? '');
$senha = $dados['senha'] ?? '';

if ($nome == '' || $email == '' || strlen($senha) < 6) {
    erro('Preencha o nome, o e-mail e uma senha de pelo menos 6 caracteres.');
}

// já existe alguém com esse e-mail?
$busca = $pdo->prepare('SELECT id FROM usuario WHERE email = ?');
$busca->execute([$email]);
if ($busca->fetch()) {
    erro('Este e-mail já está cadastrado.');
}

// guarda a senha embaralhada (hash), nunca a senha de verdade
$hash = password_hash($senha, PASSWORD_DEFAULT);
$grava = $pdo->prepare('INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)');
$grava->execute([$nome, $email, $hash]);

responder(['ok' => true]);
