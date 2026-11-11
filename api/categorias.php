<?php
require __DIR__ . '/_comum.php';

$idUsuario = usuarioLogado();            // só entra quem está logado
$metodo = $_SERVER['REQUEST_METHOD'];    // GET, POST, PUT ou DELETE

// LISTAR: devolve as categorias do usuário que está logado
if ($metodo == 'GET') {
    $busca = $pdo->prepare('SELECT id, nome, tipo, limite_mensal FROM categoria WHERE id_usuario = ? ORDER BY nome');
    $busca->execute([$idUsuario]);
    responder($busca->fetchAll());
}

// EXCLUIR: o id vem na URL (categorias.php?id=3)
if ($metodo == 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);

    // categoria com transações não pode ser apagada
    $conta = $pdo->prepare('SELECT COUNT(*) FROM transacao WHERE id_categoria = ?');
    $conta->execute([$id]);
    if ($conta->fetchColumn() > 0) {
        erro('Essa categoria tem transações. Apague ou mova as transações antes.', 409);
    }

    // o "AND id_usuario" garante que ninguém apaga categoria de outra pessoa
    $apaga = $pdo->prepare('DELETE FROM categoria WHERE id = ? AND id_usuario = ?');
    $apaga->execute([$id, $idUsuario]);
    responder(['ok' => true]);
}

// CRIAR (POST) e EDITAR (PUT) usam os mesmos dados
if ($metodo != 'POST' && $metodo != 'PUT') {
    erro('Método não permitido.', 405);
}

$dados  = lerJson();
$id     = (int) ($dados['id'] ?? 0);
$nome   = trim($dados['nome'] ?? '');
$tipo   = $dados['tipo'] ?? '';
$limite = $dados['limite_mensal'] ?? '';

if ($nome == '' || ($tipo != 'entrada' && $tipo != 'saida')) {
    erro('Informe o nome e o tipo da categoria.');
}
if (mb_strlen($nome) > 20) {
    erro('O nome pode ter no máximo 20 caracteres.');
}

// só categoria de saída tem limite; vazio = sem limite
if ($tipo == 'entrada' || $limite === '' || $limite === null) {
    $limite = null;
} elseif (!is_numeric($limite) || $limite < 0) {
    erro('O limite precisa ser um número maior ou igual a zero.');
}

// já existe OUTRA categoria sua com esse nome?
$busca = $pdo->prepare('SELECT id FROM categoria WHERE id_usuario = ? AND nome = ? AND id <> ?');
$busca->execute([$idUsuario, $nome, $id]);
if ($busca->fetch()) {
    erro('Você já tem uma categoria com esse nome.');
}

if ($metodo == 'POST') {
    $grava = $pdo->prepare('INSERT INTO categoria (id_usuario, nome, tipo, limite_mensal) VALUES (?, ?, ?, ?)');
    $grava->execute([$idUsuario, $nome, $tipo, $limite]);
} else {
    $grava = $pdo->prepare('UPDATE categoria SET nome = ?, tipo = ?, limite_mensal = ? WHERE id = ? AND id_usuario = ?');
    $grava->execute([$nome, $tipo, $limite, $id, $idUsuario]);
}

responder(['ok' => true]);
