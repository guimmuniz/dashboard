<?php
require __DIR__ . '/_comum.php';

// pega o e-mail e a senha digitados
$dados = lerJson();
$email = trim($dados['email'] ?? '');
$senha = $dados['senha'] ?? '';

// procura o usuário pelo e-mail
$busca = $pdo->prepare('SELECT id, nome, senha FROM usuario WHERE email = ?');
$busca->execute([$email]);
$usuario = $busca->fetch();

// password_verify compara a senha digitada com o hash guardado no banco
if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    erro('E-mail ou senha incorretos.', 401);
}

// guarda na sessão quem entrou (é isso que "mantém o login")
$_SESSION['id_usuario'] = $usuario['id'];
$_SESSION['nome'] = $usuario['nome'];

responder(['ok' => true]);
