<?php
// Funções para buscar cotações nas APIs externas e guardar no banco (cache).
// Usado por api/radar.php e api/carteira.php.
//
// B3:  brapi.dev  (plano grátis: 15 mil pedidos por mês, 1 ação por pedido)
// EUA: Finnhub    (plano grátis: 60 pedidos por minuto)
//
// As cotações são atualizadas duas vezes por dia: 11:00 e 16:00 (horário de Brasília).
// Não tem tarefa agendada: quando alguém abre a página, o PHP vê se a cotação guardada
// é mais velha que o último horário de atualização. Se for, pergunta de novo para a API.

// Todas as datas e horas destes arquivos ficam no horário de Brasília.
date_default_timezone_set('America/Sao_Paulo');

// As chaves das APIs ficam em config/apis.php (fora do GitHub).
// Depois deste require existem as variáveis $chaveBrapi e $chaveFinnhub.
if (!file_exists(__DIR__ . '/../config/apis.php')) {
    erro('Copie config/apis.exemplo.php para config/apis.php.', 500);
}
require __DIR__ . '/../config/apis.php';

// Devolve o último horário de atualização que já passou, ex.: "2026-11-11 11:00:00".
//   dia útil, depois das 16:00       -> hoje às 16:00
//   dia útil, entre 11:00 e 16:00    -> hoje às 11:00
//   antes das 11:00 ou fim de semana -> o último dia útil anterior, às 16:00
// O parâmetro $agora só serve para testar com outros horários; normalmente fica 'now'.
function horarioDaUltimaAtualizacao($agora = 'now') {
    $dia = new DateTime($agora);
    $hora = $dia->format('H:i');                // ex.: "14:35"
    $diaUtil = $dia->format('N') <= 5;          // N: 1 = segunda ... 6 = sábado, 7 = domingo

    if ($diaUtil && $hora >= '16:00') {
        return $dia->format('Y-m-d') . ' 16:00:00';
    }
    if ($diaUtil && $hora >= '11:00') {
        return $dia->format('Y-m-d') . ' 11:00:00';
    }

    // volta um dia de cada vez até cair num dia útil
    do {
        $dia->modify('-1 day');
    } while ($dia->format('N') > 5);
    return $dia->format('Y-m-d') . ' 16:00:00';
}

// ==========================================================
// Pedidos para as APIs
// Cada API tem duas funções:
//   pedidoX($ticker)                 -> monta o endereço e os cabeçalhos do pedido
//   lerRespostaX($codigo, $dados)    -> entende a resposta. Devolve uma de três coisas:
//       array com preco, variacao_percent e moeda  -> deu certo
//       false                                      -> a API disse que essa ação não existe
//       null                                       -> a API falhou (sem internet, chave errada, limite de uso...)
// Separadas assim, as mesmas regras servem para o pedido de uma ação só (consultarApi)
// e para vários pedidos ao mesmo tempo (consultarVariasApis).
// ==========================================================

// B3: https://brapi.dev/api/quote/PETR4  (o token vai no cabeçalho Authorization)
function pedidoBrapi($ticker) {
    global $chaveBrapi;

    // sem token a brapi ainda responde PETR4, MGLU3, VALE3 e ITUB4
    $cabecalhos = [];
    if ($chaveBrapi != '') {
        $cabecalhos[] = 'Authorization: Bearer ' . $chaveBrapi;
    }
    return ['https://brapi.dev/api/quote/' . urlencode($ticker), $cabecalhos];
}

function lerRespostaBrapi($codigo, $dados) {
    // 404 ou 400 = ação que a brapi não conhece
    if ($codigo == 404 || $codigo == 400) {
        return false;
    }
    // qualquer outro código diferente de 200 é falha (401 chave errada, 429 limite, 500...)
    if ($codigo != 200) {
        return null;
    }
    // veio 200, mas sem preço: também é ação desconhecida
    $acao = $dados['results'][0] ?? null;
    if (!isset($acao['regularMarketPrice'])) {
        return false;
    }

    return [
        'preco'            => $acao['regularMarketPrice'],
        'variacao_percent' => $acao['regularMarketChangePercent'] ?? 0,
        'moeda'            => $acao['currency'] ?? 'BRL',
    ];
}

// EUA: https://finnhub.io/api/v1/quote?symbol=AAPL  (a chave vai no cabeçalho X-Finnhub-Token)
// Sem chave devolve null: nem adianta mandar o pedido.
function pedidoFinnhub($ticker) {
    global $chaveFinnhub;

    if ($chaveFinnhub == '') {
        return null;
    }
    return ['https://finnhub.io/api/v1/quote?symbol=' . urlencode($ticker), ['X-Finnhub-Token: ' . $chaveFinnhub]];
}

// Resposta da Finnhub: c = preço atual, dp = variação do dia em %
function lerRespostaFinnhub($codigo, $dados) {
    if ($codigo != 200) {
        return null;   // 401 chave errada, 429 limite, 0 sem conexão...
    }
    // para ação que não existe a Finnhub responde 200 com o preço zerado
    if (empty($dados['c'])) {
        return false;
    }

    return [
        'preco'            => $dados['c'],
        'variacao_percent' => $dados['dp'] ?? 0,
        'moeda'            => 'USD',   // a Finnhub só tem ações dos EUA
    ];
}

// Cria o pedido (curl) com as opções que todos os pedidos usam.
function criarCurl($url, $cabecalhos) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);   // devolve a resposta em vez de imprimir
    curl_setopt($curl, CURLOPT_HTTPHEADER, $cabecalhos);
    curl_setopt($curl, CURLOPT_TIMEOUT, 4);             // espera no máximo 4 segundos (a fila do curl_multi muda para 15)
    return $curl;
}

// ==========================================================
// UMA ação: usado ao adicionar uma ação nova na carteira (confere se ela existe)
// ==========================================================
function consultarApi($ticker, $mercado) {
    $pedido = $mercado == 'B3' ? pedidoBrapi($ticker) : pedidoFinnhub($ticker);
    if ($pedido === null) {
        return null;
    }
    [$url, $cabecalhos] = $pedido;

    $curl = criarCurl($url, $cabecalhos);
    $resposta = curl_exec($curl);                           // manda e espera a resposta
    $codigo = curl_getinfo($curl, CURLINFO_HTTP_CODE);      // 0 quando nem conseguiu conectar
    curl_close($curl);
    $dados = json_decode((string) $resposta, true);

    return $mercado == 'B3' ? lerRespostaBrapi($codigo, $dados) : lerRespostaFinnhub($codigo, $dados);
}

// ==========================================================
// VÁRIAS ações de uma vez (curl_multi)
// Com curl_exec, um pedido só começa quando o anterior termina e abre uma conexão nova
// cada vez: 50 ações levam uns 27 segundos.
// Com o curl_multi, os pedidos ficam num grupo e o PHP cuida de todos juntos:
//   - a brapi (B3) e a Finnhub (EUA) são atendidas AO MESMO TEMPO;
//   - dentro de cada API os pedidos andam em fila, um de cada vez, reaproveitando a
//     mesma conexão (bem mais rápido que abrir uma conexão por pedido).
// Por que fila? A brapi do plano grátis recusa pedidos simultâneos (responde 429).
// $lista: [ ['ticker' => 'PETR4', 'mercado' => 'B3'], ... ]
// Devolve: [ 'B3:PETR4' => array | false | null, ... ]  (mesmas regras de lerRespostaX)
// ==========================================================
function consultarVariasApis($lista) {
    $resultados = [];
    $multi = curl_multi_init();   // o "multi" é um grupo de pedidos que o PHP cuida junto
    $pedidos = [];                // 'B3:PETR4' => ['curl' => o pedido, 'mercado' => 'B3']

    // fila por servidor: no máximo 1 conexão para cada API...
    curl_multi_setopt($multi, CURLMOPT_MAX_HOST_CONNECTIONS, 1);
    // ...e 1 pedido por vez nessa conexão (sem isso o HTTP/2 mandaria todos juntos pela mesma conexão)
    curl_multi_setopt($multi, CURLMOPT_PIPELINING, CURLPIPE_NOTHING);

    // 1) cria um pedido para cada ação e coloca no grupo
    foreach ($lista as $acao) {
        $chave = $acao['mercado'] . ':' . $acao['ticker'];
        $pedido = $acao['mercado'] == 'B3' ? pedidoBrapi($acao['ticker']) : pedidoFinnhub($acao['ticker']);
        if ($pedido === null) {
            $resultados[$chave] = null;   // sem chave: nem manda, já conta como falha
            continue;
        }
        [$url, $cabecalhos] = $pedido;
        $curl = criarCurl($url, $cabecalhos);
        // na fila, o relógio de cada pedido já corre enquanto ele espera a vez dele.
        // Com 4 s, os últimos da fila desistiriam antes de sair; com 15 s a fila inteira termina.
        // Se a API estiver fora do ar, todos desistem juntos em 15 s (e não 15 s para cada um).
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_multi_add_handle($multi, $curl);   // põe o pedido no grupo
        $pedidos[$chave] = ['curl' => $curl, 'mercado' => $acao['mercado']];
    }

    // 2) roda o grupo até todos os pedidos terminarem.
    //    curl_multi_exec faz os pedidos andarem e diz em $rodando quantos ainda não terminaram.
    //    curl_multi_select dorme até chegar alguma resposta (assim o PHP não fica girando à toa).
    do {
        curl_multi_exec($multi, $rodando);
        curl_multi_select($multi);
    } while ($rodando > 0);

    // 3) lê cada resposta com as mesmas regras do pedido de uma ação só
    foreach ($pedidos as $chave => $p) {
        $codigo = curl_getinfo($p['curl'], CURLINFO_HTTP_CODE);              // 0 quando falhou ou passou dos 15 s
        $dados = json_decode((string) curl_multi_getcontent($p['curl']), true);
        if ($p['mercado'] == 'B3') {
            $resultados[$chave] = lerRespostaBrapi($codigo, $dados);
        } else {
            $resultados[$chave] = lerRespostaFinnhub($codigo, $dados);
        }
        curl_multi_remove_handle($multi, $p['curl']);   // tira do grupo e libera
        curl_close($p['curl']);
    }
    curl_multi_close($multi);

    return $resultados;
}

// ==========================================================
// Banco (cache)
// ==========================================================

// Grava a cotação no cache. Se a ação já tem linha na tabela, só atualiza.
// Em atualizado_em vai o horário de atualização (11:00 ou 16:00), e não a hora exata do pedido:
// é ele que aparece na tela ("Atualizado às 11:00").
function salvarCotacao($pdo, $ticker, $mercado, $cotacao) {
    $grava = $pdo->prepare('INSERT INTO cotacao (ticker, mercado, preco, variacao_percent, moeda, atualizado_em)
                            VALUES (?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE preco = VALUES(preco), variacao_percent = VALUES(variacao_percent),
                                                    moeda = VALUES(moeda), atualizado_em = VALUES(atualizado_em)');
    $grava->execute([$ticker, $mercado, $cotacao['preco'], $cotacao['variacao_percent'], $cotacao['moeda'], horarioDaUltimaAtualizacao()]);
}

// Lê todas as cotações guardadas, numa consulta só.
// Devolve [ 'B3:PETR4' => [preco, variacao_percent, moeda, atualizado_em], ... ]
function lerCotacoesGuardadas($pdo) {
    $guardadas = [];
    foreach ($pdo->query('SELECT ticker, mercado, preco, variacao_percent, moeda, atualizado_em FROM cotacao') as $c) {
        $guardadas[$c['mercado'] . ':' . $c['ticker']] = $c;
    }
    return $guardadas;
}

// Junta a cotação em cada ação da lista, usando o cache sempre que der:
//   cotação guardada depois do último horário de atualização -> usa a guardada
//   senão                                                    -> pergunta para a API (todas juntas) e guarda
// Cada item da lista ganha: preco, variacao_percent, moeda, atualizado_em e desatualizada.
//   desatualizada = true -> a API falhou e o valor é o último guardado
//   preco = null         -> a ação nunca teve cotação
// $lista: [ ['ticker' => 'PETR4', 'mercado' => 'B3', ...outros campos...], ... ]
function buscarVariasCotacoes($pdo, $lista) {
    $ultimaAtualizacao = horarioDaUltimaAtualizacao();
    $guardadas = lerCotacoesGuardadas($pdo);

    // 1) separa as ações que precisam de cotação nova: nunca guardadas ou mais velhas que o último horário.
    //    As datas estão no formato "2026-11-11 11:00:00", então dá para comparar como texto.
    $velhas = [];
    foreach ($lista as $acao) {
        $guardada = $guardadas[$acao['mercado'] . ':' . $acao['ticker']] ?? null;
        if (!$guardada || $guardada['atualizado_em'] < $ultimaAtualizacao) {
            $velhas[] = $acao;
        }
    }

    // 2) pergunta todas as velhas para as APIs ao mesmo tempo e guarda as que deram certo
    if (count($velhas) > 0) {
        $novas = consultarVariasApis($velhas);
        foreach ($velhas as $acao) {
            $cotacao = $novas[$acao['mercado'] . ':' . $acao['ticker']];
            if (is_array($cotacao)) {
                salvarCotacao($pdo, $acao['ticker'], $acao['mercado'], $cotacao);
            }
        }
        $guardadas = lerCotacoesGuardadas($pdo);   // lê de novo, já com as novas
    }

    // 3) junta a cotação em cada ação da lista
    $resultado = [];
    foreach ($lista as $acao) {
        $guardada = $guardadas[$acao['mercado'] . ':' . $acao['ticker']] ?? null;
        if ($guardada) {
            $acao['preco']            = $guardada['preco'];
            $acao['variacao_percent'] = $guardada['variacao_percent'];
            $acao['moeda']            = $guardada['moeda'];
            $acao['atualizado_em']    = $guardada['atualizado_em'];
            // continua mais velha que o último horário? então a API falhou
            $acao['desatualizada']    = $guardada['atualizado_em'] < $ultimaAtualizacao;
        } else {
            $acao['preco']            = null;
            $acao['variacao_percent'] = null;
            $acao['moeda']            = null;
            $acao['atualizado_em']    = null;
            $acao['desatualizada']    = false;
        }
        $resultado[] = $acao;
    }
    return $resultado;
}
