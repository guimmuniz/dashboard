<?php
require __DIR__ . '/_comum.php';
require __DIR__ . '/_cotacoes.php';

$idUsuario = usuarioLogado();            // só entra quem está logado
$metodo = $_SERVER['REQUEST_METHOD'];    // GET, POST, PUT ou DELETE

// LISTAR: as ações da carteira do usuário, cada uma com a cotação atual (do cache ou da API)
if ($metodo == 'GET') {
    $busca = $pdo->prepare('SELECT id, ticker, mercado, quantidade, preco_medio FROM carteira WHERE id_usuario = ? ORDER BY mercado, ticker');
    $busca->execute([$idUsuario]);

    // junta a cotação em cada posição (preco, variacao_percent, moeda, atualizado_em, desatualizada),
    // do cache ou das APIs todas de uma vez. As contas (valor atual, ganho ou perda) são feitas no JavaScript.
    $posicoes = buscarVariasCotacoes($pdo, $busca->fetchAll());
    responder($posicoes);
}

// EXCLUIR: o id vem na URL (carteira.php?id=3)
if ($metodo == 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);

    // o "AND id_usuario" garante que ninguém apaga a carteira de outra pessoa
    $apaga = $pdo->prepare('DELETE FROM carteira WHERE id = ? AND id_usuario = ?');
    $apaga->execute([$id, $idUsuario]);
    if ($apaga->rowCount() == 0) {
        erro('Ação não encontrada na sua carteira.', 404);
    }
    responder(['ok' => true]);
}

// CRIAR (POST) e EDITAR (PUT) usam a mesma quantidade e o mesmo preço médio
if ($metodo != 'POST' && $metodo != 'PUT') {
    erro('Método não permitido.', 405);
}

$dados      = lerJson();
$id         = (int) ($dados['id'] ?? 0);
$quantidade = (string) ($dados['quantidade'] ?? '');
$precoMedio = $dados['preco_medio'] ?? '';

// quantidade: número inteiro maior que zero (a carteira só tem ações inteiras)
// ctype_digit confere se o texto só tem dígitos (recusa "2.5", "-3" e vazio)
if (!ctype_digit($quantidade) || $quantidade == 0 || $quantidade > 100000000) {
    erro('Informe a quantidade: um número inteiro maior que zero.');
}
// preço médio: quanto o usuário pagou por ação, na moeda do mercado (R$ na B3, US$ nos EUA)
if (!is_numeric($precoMedio) || $precoMedio <= 0 || $precoMedio >= 1000000000) {
    erro('Informe o preço médio pago (um valor maior que zero).');
}

// EDITAR: só muda a quantidade e o preço médio (a ação e o mercado continuam os mesmos)
if ($metodo == 'PUT') {
    // a posição precisa ser sua
    $busca = $pdo->prepare('SELECT id FROM carteira WHERE id = ? AND id_usuario = ?');
    $busca->execute([$id, $idUsuario]);
    if (!$busca->fetch()) {
        erro('Ação não encontrada na sua carteira.', 404);
    }

    $grava = $pdo->prepare('UPDATE carteira SET quantidade = ?, preco_medio = ? WHERE id = ? AND id_usuario = ?');
    $grava->execute([$quantidade, $precoMedio, $id, $idUsuario]);
    responder(['ok' => true]);
}

// CRIAR: além da quantidade e do preço, vêm a ação e o mercado
$ticker  = strtoupper(trim($dados['ticker'] ?? ''));   // "petr4 " vira "PETR4"
$mercado = $dados['mercado'] ?? '';

if ($mercado != 'B3' && $mercado != 'US') {
    erro('Escolha o mercado: B3 ou EUA.');
}
// só letras, números e ponto (ex.: PETR4, AAPL, BRK.B), até 10 caracteres
if (!preg_match('/^[A-Z0-9.]{1,10}$/', $ticker)) {
    erro('Informe o código da ação (ex.: PETR4 ou AAPL), com até 10 letras e números.');
}

// a ação já está na sua carteira?
$busca = $pdo->prepare('SELECT id FROM carteira WHERE id_usuario = ? AND ticker = ?');
$busca->execute([$idUsuario, $ticker]);
if ($busca->fetch()) {
    erro('Você já tem ' . $ticker . ' na carteira. Use Editar para mudar a quantidade.', 409);
}

// a ação existe? Se ela já está no cache de cotações (ex.: as do radar), existe.
// Se não está, pergunta uma vez para a API e já guarda a cotação.
$busca = $pdo->prepare('SELECT ticker FROM cotacao WHERE ticker = ? AND mercado = ?');
$busca->execute([$ticker, $mercado]);
if (!$busca->fetch()) {
    $cotacao = consultarApi($ticker, $mercado);

    if ($cotacao === false) {
        $ondeFica = $mercado == 'B3' ? 'na B3' : 'nos EUA';
        erro('Não encontramos a ação ' . $ticker . ' ' . $ondeFica . '. Confira o código e o mercado.');
    }
    if ($cotacao === null) {
        erro('Não foi possível conferir a ação agora. Tente de novo em alguns minutos.', 503);
    }
    salvarCotacao($pdo, $ticker, $mercado, $cotacao);
}

$grava = $pdo->prepare('INSERT INTO carteira (id_usuario, ticker, mercado, quantidade, preco_medio) VALUES (?, ?, ?, ?, ?)');
$grava->execute([$idUsuario, $ticker, $mercado, $quantidade, $precoMedio]);

responder(['ok' => true]);
