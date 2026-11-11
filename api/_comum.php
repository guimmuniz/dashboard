<?php
// Funções usadas por todos os arquivos da pasta api/.
// Cada arquivo da API começa com:  require __DIR__ . '/_comum.php';
// (o "_" no começo do nome é só para lembrar que ninguém chama este arquivo direto)

// Tudo que a API responde é JSON (texto organizado que o JavaScript entende).
header('Content-Type: application/json; charset=utf-8');

// A sessão é como o site "lembra" quem está logado entre uma página e outra.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Abre a conexão com o banco. Depois desta linha existe a variável $pdo.
require __DIR__ . '/../config/db.php';

// Envia a resposta em JSON e encerra o arquivo.
// Exemplo: responder(['ok' => true]);
function responder($dados, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

// Atalho para erros: responde { "erro": "mensagem" } e encerra.
// O front-end mostra essa mensagem na tela.
// Exemplo: erro('E-mail já cadastrado.', 409);
function erro($mensagem, $codigo = 400) {
    responder(['erro' => $mensagem], $codigo);
}

// Lê os dados que o JavaScript enviou (fetch com body em JSON)
// e devolve como array. Se não veio nada, devolve array vazio.
function lerJson() {
    $dados = json_decode(file_get_contents('php://input'), true);
    return is_array($dados) ? $dados : [];
}

// Confere se tem alguém logado. Se não tiver, responde erro 401 e encerra.
// Se tiver, devolve o id do usuário (guardado na sessão no login).
function usuarioLogado() {
    if (!isset($_SESSION['id_usuario'])) {
        erro('Você precisa entrar na sua conta.', 401);
    }
    return $_SESSION['id_usuario'];
}
