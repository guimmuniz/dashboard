<?php
require __DIR__ . '/_comum.php';

$idUsuario = usuarioLogado();

// mês pedido (?mes=2026-11): do dia 1 até o dia 1 do mês seguinte
$mes = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    erro('Mês inválido.');
}
$inicio = $mes . '-01';
$fim = date('Y-m-d', strtotime($inicio . ' +1 month'));

// 1) todas as transações do mês, das mais recentes para as mais antigas
$busca = $pdo->prepare('SELECT t.id, t.id_categoria, t.descricao, t.valor, t.data_transacao, c.nome AS categoria, c.tipo
                        FROM transacao t JOIN categoria c ON c.id = t.id_categoria
                        WHERE c.id_usuario = ? AND t.data_transacao >= ? AND t.data_transacao < ?
                        ORDER BY t.data_transacao DESC, t.id DESC');
$busca->execute([$idUsuario, $inicio, $fim]);
$transacoes = $busca->fetchAll();

// 2) o gráfico tem uma linha por semana: dias 1-7 = Sem 1, 8-14 = Sem 2, e assim por diante
$qtdSemanas = date('t', strtotime($inicio)) > 28 ? 5 : 4;   // o 't' devolve quantos dias o mês tem
$semanas = [];
for ($i = 1; $i <= $qtdSemanas; $i++) {
    $semanas[] = ['rotulo' => 'Sem ' . $i, 'entradas' => 0, 'saidas' => 0];
}

// percorre as transações somando o total do mês e o total de cada semana
$entradas = 0;
$saidas = 0;
foreach ($transacoes as $t) {
    $valor = (float) $t['valor'];
    $dia = (int) substr($t['data_transacao'], 8, 2);
    $semana = intdiv($dia - 1, 7);   // dia 1 a 7 = posição 0, dia 8 a 14 = posição 1...
    if ($t['tipo'] == 'entrada') {
        $entradas += $valor;
        $semanas[$semana]['entradas'] += $valor;
    } else {
        $saidas += $valor;
        $semanas[$semana]['saidas'] += $valor;
    }
}
foreach ($semanas as $i => $s) {
    $semanas[$i]['entradas'] = round($s['entradas'], 2);
    $semanas[$i]['saidas'] = round($s['saidas'], 2);
}

// 3) quanto cada categoria de SAÍDA gastou no mês (GROUP BY soma as transações de cada categoria).
// O LEFT JOIN mantém também as categorias sem nenhum gasto: elas servem para a lista de limites.
$busca = $pdo->prepare("SELECT c.nome, c.limite_mensal, COALESCE(SUM(t.valor), 0) AS gasto
                        FROM categoria c
                        LEFT JOIN transacao t ON t.id_categoria = c.id AND t.data_transacao >= ? AND t.data_transacao < ?
                        WHERE c.id_usuario = ? AND c.tipo = 'saida'
                        GROUP BY c.id, c.nome, c.limite_mensal
                        ORDER BY gasto DESC, c.nome");
$busca->execute([$inicio, $fim, $idUsuario]);
$categorias = $busca->fetchAll();

responder([
    'entradas'   => round($entradas, 2),
    'saidas'     => round($saidas, 2),
    'semanas'    => $semanas,
    'categorias' => $categorias,
    'ultimas'    => array_slice($transacoes, 0, 5),   // as 5 mais recentes
]);
