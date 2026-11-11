<?php
require __DIR__ . '/_comum.php';
require __DIR__ . '/_cotacoes.php';

usuarioLogado();   // só entra quem está logado

// A lista do radar: 25 ações populares de cada mercado, fixas. Para mudar o radar, é só mexer aqui.
$acoesRadar = [
    'B3' => ['PETR4', 'PETR3', 'VALE3', 'ITUB4', 'BBDC4', 'BBAS3', 'ITSA4', 'ABEV3', 'WEGE3', 'B3SA3',
             'BPAC11', 'PRIO3', 'SUZB3', 'RENT3', 'RADL3', 'EQTL3', 'GGBR4', 'LREN3', 'VIVT3', 'CMIG4',
             'SBSP3', 'TOTS3', 'RAIL3', 'MGLU3', 'HAPV3'],
    'US' => ['AAPL', 'MSFT', 'NVDA', 'AMZN', 'GOOGL', 'META', 'TSLA', 'KO', 'JPM', 'V',
             'MA', 'WMT', 'JNJ', 'PG', 'XOM', 'UNH', 'HD', 'DIS', 'NFLX', 'AMD',
             'INTC', 'PEP', 'MCD', 'NKE', 'BAC'],
];

// o radar só tem consulta (GET): ninguém adiciona nem apaga ações dele
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    erro('Método não permitido.', 405);
}

// transforma a lista em [ ['ticker' => 'PETR4', 'mercado' => 'B3'], ... ]
$lista = [];
foreach ($acoesRadar as $mercado => $tickers) {
    foreach ($tickers as $ticker) {
        $lista[] = ['ticker' => $ticker, 'mercado' => $mercado];
    }
}

// junta as cotações (do cache, ou das APIs todas de uma vez quando estão velhas)
$acoes = buscarVariasCotacoes($pdo, $lista);

// separa por mercado: { "B3": [ {ticker, mercado, preco, ...}, ... ], "US": [ ... ] }
$resposta = ['B3' => [], 'US' => []];
foreach ($acoes as $a) {
    $resposta[$a['mercado']][] = $a;
}

responder($resposta);
