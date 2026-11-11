<?php
require __DIR__ . '/_comum.php';

$idUsuario = usuarioLogado();
$metodo = $_SERVER['REQUEST_METHOD'];    // GET, POST, PUT ou DELETE

// LISTAR: sempre de um mês (?mes=2026-11). Opcional: ?tipo=saida e ?categoria=4
if ($metodo == 'GET') {
    $mes = $_GET['mes'] ?? date('Y-m');
    if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
        erro('Mês inválido.');
    }
    $inicio = $mes . '-01';
    $fim = date('Y-m-d', strtotime($inicio . ' +1 month'));   // primeiro dia do mês seguinte

    // o JOIN junta cada transação com a sua categoria (para pegar o nome e o tipo)
    $sql = 'SELECT t.id, t.id_categoria, t.descricao, t.valor, t.data_transacao, c.nome AS categoria, c.tipo
            FROM transacao t JOIN categoria c ON c.id = t.id_categoria
            WHERE c.id_usuario = ? AND t.data_transacao >= ? AND t.data_transacao < ?';
    $valores = [$idUsuario, $inicio, $fim];

    // filtros opcionais: cada um acrescenta um pedaço no SQL
    $tipo = $_GET['tipo'] ?? '';
    if ($tipo == 'entrada' || $tipo == 'saida') {
        $sql .= ' AND c.tipo = ?';
        $valores[] = $tipo;
    }
    $categoria = (int) ($_GET['categoria'] ?? 0);
    if ($categoria > 0) {
        $sql .= ' AND t.id_categoria = ?';
        $valores[] = $categoria;
    }
    $sql .= ' ORDER BY t.data_transacao DESC, t.id DESC';   // mais recentes primeiro

    $busca = $pdo->prepare($sql);
    $busca->execute($valores);
    responder($busca->fetchAll());
}

if ($metodo != 'POST' && $metodo != 'PUT' && $metodo != 'DELETE') {
    erro('Método não permitido.', 405);
}

$dados = lerJson();
$id = (int) ($_GET['id'] ?? $dados['id'] ?? 0);   // DELETE manda o id na URL; PUT manda no corpo

// EDITAR ou EXCLUIR: a transação precisa ser de uma categoria sua
if ($metodo == 'PUT' || $metodo == 'DELETE') {
    $busca = $pdo->prepare('SELECT t.id FROM transacao t JOIN categoria c ON c.id = t.id_categoria WHERE t.id = ? AND c.id_usuario = ?');
    $busca->execute([$id, $idUsuario]);
    if (!$busca->fetch()) {
        erro('Transação não encontrada.', 404);
    }
}

if ($metodo == 'DELETE') {
    $apaga = $pdo->prepare('DELETE FROM transacao WHERE id = ?');
    $apaga->execute([$id]);
    responder(['ok' => true]);
}

// CRIAR (POST) e EDITAR (PUT) usam os mesmos dados
$idCategoria = (int) ($dados['id_categoria'] ?? 0);
$descricao   = trim($dados['descricao'] ?? '');
$valor       = $dados['valor'] ?? '';
$data        = $dados['data_transacao'] ?? '';

// a categoria escolhida precisa ser sua
$busca = $pdo->prepare('SELECT id FROM categoria WHERE id = ? AND id_usuario = ?');
$busca->execute([$idCategoria, $idUsuario]);
if (!$busca->fetch()) {
    erro('Escolha uma categoria.');
}

if (!is_numeric($valor) || $valor <= 0 || $valor >= 100000000) {
    erro('Informe um valor maior que zero.');
}

// data no formato 2026-11-15; o checkdate recusa datas que não existem (ex.: 2026-02-31)
$partes = explode('-', $data);
if (count($partes) != 3 || !checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0])) {
    erro('Informe uma data válida.');
}

if (mb_strlen($descricao) > 100) {
    erro('A descrição pode ter no máximo 100 caracteres.');
}

if ($metodo == 'POST') {
    $grava = $pdo->prepare('INSERT INTO transacao (id_categoria, descricao, valor, data_transacao) VALUES (?, ?, ?, ?)');
    $grava->execute([$idCategoria, $descricao, $valor, $data]);
} else {
    $grava = $pdo->prepare('UPDATE transacao SET id_categoria = ?, descricao = ?, valor = ?, data_transacao = ? WHERE id = ?');
    $grava->execute([$idCategoria, $descricao, $valor, $data, $id]);
}

responder(['ok' => true]);
