<?php require __DIR__ . '/../../config/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Radar de ações · Nome do projeto</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://api.fontshare.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://api.fontshare.com/v2/css?f[]=clash-display@500,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>


<div class="app">

  <aside class="lateral">
    <div>
      <a class="marca" href="dashboard.php">Nome do projeto</a>
      <nav class="menu" aria-label="Principal">
        <a href="dashboard.php"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.75" y="2.75" width="5" height="5" rx="1.5"/><rect x="10.25" y="2.75" width="5" height="5" rx="1.5"/><rect x="2.75" y="10.25" width="5" height="5" rx="1.5"/><rect x="10.25" y="10.25" width="5" height="5" rx="1.5"/></svg>Dashboard</a>
        <a href="transacoes.php"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 6h11l-3-3M14.5 12h-11l3 3"/></svg>Transações</a>
        <a href="categorias.php"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.6 2.75H4.25a1.5 1.5 0 0 0-1.5 1.5V9.6a1.5 1.5 0 0 0 .44 1.06l4.9 4.9a1.5 1.5 0 0 0 2.12 0l5.35-5.35a1.5 1.5 0 0 0 0-2.12l-4.9-4.9A1.5 1.5 0 0 0 9.6 2.75Z"/><circle cx="6.25" cy="6.25" r="1" fill="currentColor" stroke="none"/></svg>Categorias</a>
        <a class="ativo" href="radar.php" aria-current="page"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.75 13l4.25-4.25 3 3 5.25-5.25M11.5 6.5h3.75v3.75"/></svg>Radar</a>
        <a href="carteira.php"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.75" y="4.25" width="12.5" height="10" rx="2"/><path d="M2.75 7.5h12.5M11.25 11h1.5"/></svg>Carteira</a>
      </nav>
    </div>
    <!-- O avatar e a trilha leem o nome de #nome-usuario sozinhos (ver app.js) -->
    <div class="usuario">
      <span class="avatar" data-avatar-usuario aria-hidden="true"></span>
      <div class="usuario-texto">
        <strong id="nome-usuario"><?= htmlspecialchars($_SESSION['nome']) ?></strong>
        <a href="../../api/logout.php">Sair</a>
      </div>
    </div>
  </aside>

  <main class="conteudo">

    <nav class="trilha" aria-label="Você está em">
      <ol>
        <li><a href="dashboard.php"><span class="avatar avatar-pp" data-avatar-usuario aria-hidden="true"></span><span data-primeiro-nome></span></a></li>
        <li aria-hidden="true"><svg class="separador" viewBox="0 0 12 12" fill="none"><path d="M4.5 2.5L8 6l-3.5 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></li>
        <li aria-current="page">Radar de ações</li>
      </ol>
    </nav>

    <div class="topo">
      <h1>Radar de ações</h1>
    </div>
    <p class="mudo" style="font-size:14px; margin:12px 0 0; max-width:560px; line-height:1.55">
      Cotações de 25 ações populares da B3 e 25 dos EUA. Os preços são atualizados duas vezes por dia,
      às 11:00 e às 16:00 (horário de Brasília): não são cotações em tempo real.
    </p>

    <!-- GET ../../api/radar.php -> { "B3": [...], "US": [...] }. Cada ação: ticker, mercado, preco,
         variacao_percent, moeda, atualizado_em, desatualizada. Linhas criadas por criarLinhaRadar() em app.js.
         O botão de cada linha leva para carteira.php?ticker=PETR4&mercado=B3. -->
    <section class="secao" style="margin-top:40px">

      <!-- Busca: filtra as linhas pelo código enquanto digita (só no navegador, sem chamar a API) -->
      <input type="search" id="busca-radar" class="radar-busca" placeholder="Buscar ação (ex.: PETR4)" aria-label="Buscar ação no radar" autocomplete="off">

      <!-- Aparece enquanto o PHP busca as cotações (pode levar alguns segundos) -->
      <div class="vazio" id="carregando-radar">Carregando cotações...</div>

      <!-- Cada mercado num bloco: o bloco some quando a busca não acha nada nele -->
      <div class="radar-bloco" id="bloco-b3">
        <h3 class="radar-mercado">B3 (Brasil)</h3>
        <div class="radar-cabecalho rotulo">
          <span>Ação</span><span>Preço</span><span>Variação no dia</span><span>Atualização</span><span></span>
        </div>
        <div id="radar-b3">
        </div>
      </div>

      <div class="radar-bloco" id="bloco-us">
        <h3 class="radar-mercado">EUA</h3>
        <div class="radar-cabecalho rotulo">
          <span>Ação</span><span>Preço</span><span>Variação no dia</span><span>Atualização</span><span></span>
        </div>
        <div id="radar-us">
        </div>
      </div>

      <!-- Aparece quando a busca não acha nenhuma ação -->
      <div class="vazio" id="radar-sem-resultado" hidden></div>
    </section>

  </main>
</div>

<script src="../js/app.js"></script>
</body>
</html>
