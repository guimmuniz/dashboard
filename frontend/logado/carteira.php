<?php require __DIR__ . '/../../config/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Carteira · Nome do projeto</title>
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
        <a href="radar.php"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.75 13l4.25-4.25 3 3 5.25-5.25M11.5 6.5h3.75v3.75"/></svg>Radar</a>
        <a class="ativo" href="carteira.php" aria-current="page"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.75" y="4.25" width="12.5" height="10" rx="2"/><path d="M2.75 7.5h12.5M11.25 11h1.5"/></svg>Carteira</a>
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
        <li aria-current="page">Carteira</li>
      </ol>
    </nav>

    <div class="topo">
      <h1>Carteira <span class="contador" id="qtd-carteira">0</span></h1>
      <button class="btn" id="btn-nova-posicao"><svg class="ico" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>Adicionar à carteira</button>
    </div>
    <p class="mudo" style="font-size:14px; margin:12px 0 0; max-width:560px; line-height:1.55">
      As ações que você tem, com o preço médio que pagou. O preço atual é atualizado às 11:00 e às 16:00
      (horário de Brasília). Os totais em reais e em dólares ficam separados.
    </p>

    <!-- GET ../../api/carteira.php -> lista das posições do usuário, cada uma com a cotação atual.
         Os totais ficam separados por moeda: R$ (B3) e US$ (EUA) nunca são somados.
         Aberta como carteira.php?ticker=PETR4&mercado=B3 (botão do radar), já abre o modal com a ação. -->
    <section class="secao" style="margin-top:40px">

      <!-- Totais de cada moeda: o JS preenche e mostra só os que têm ações -->
      <div class="totais carteira-totais" id="totais-b3" hidden>
        <span class="carteira-totais-titulo">Ações da B3 (R$)</span>
        <div><span class="rotulo">Investido</span><strong class="num investido"></strong></div>
        <div><span class="rotulo">Valor atual</span><strong class="num atual"></strong></div>
        <div><span class="rotulo">Resultado</span><strong class="num resultado"></strong></div>
      </div>
      <div class="totais carteira-totais" id="totais-us" hidden>
        <span class="carteira-totais-titulo">Ações dos EUA (US$)</span>
        <div><span class="rotulo">Investido</span><strong class="num investido"></strong></div>
        <div><span class="rotulo">Valor atual</span><strong class="num atual"></strong></div>
        <div><span class="rotulo">Resultado</span><strong class="num resultado"></strong></div>
      </div>

      <div class="carteira-cabecalho rotulo">
        <span>Ação</span><span>Qtd.</span><span>Preço médio</span><span>Preço atual</span><span>Valor atual</span><span>Resultado</span><span></span>
      </div>
      <div id="lista-carteira">
      </div>

      <!-- Aparece quando a carteira está vazia (e #lista-carteira fica escondida) -->
      <div class="vazio" id="sem-carteira" hidden>Sua carteira está vazia. Use "Adicionar à carteira" aqui ou no Radar.</div>
    </section>

  </main>
</div>

<!-- ===== Modal: adicionar / editar ação da carteira =====
     Campos com os nomes das colunas da tabela carteira (id_usuario vem da sessão no PHP).
     POST (novo) ou PUT (editar, com id) para ../../api/carteira.php.
     Ao editar (ou vindo do radar), a ação e o mercado ficam travados. -->
<dialog id="modal-carteira" aria-labelledby="titulo-modal-carteira">
  <form id="form-carteira" method="dialog" novalidate>
    <div class="modal-topo">
      <h2 id="titulo-modal-carteira">Adicionar à carteira</h2>
      <button type="button" class="fechar fechar-m" data-fechar aria-label="Fechar"><svg width="16.2" height="16.2" viewBox="0 0 16.2 16.2" fill="none" aria-hidden="true"><path d="M2 2L14.2 14.2M14.2 2L2 14.2" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></button>
    </div>
    <div class="msg erro" id="msg-carteira" role="alert"></div>
    <input type="hidden" name="id">

    <div class="campo">
      <label for="k-ticker">Código da ação</label>
      <input type="text" id="k-ticker" name="ticker" maxlength="10" placeholder="Ex.: PETR4" autocomplete="off" style="text-transform:uppercase" required>
      <span class="dica">Ex.: PETR4 ou VALE3 na B3, AAPL ou MSFT nos EUA.</span>
    </div>

    <div class="campo">
      <label for="k-mercado">Mercado</label>
      <select id="k-mercado" name="mercado">
        <option value="B3">B3 (Brasil)</option>
        <option value="US">EUA</option>
      </select>
    </div>

    <div class="campo">
      <label for="k-quantidade">Quantidade</label>
      <input type="number" id="k-quantidade" name="quantidade" min="1" step="1" placeholder="Ex.: 100" required>
    </div>

    <div class="campo">
      <label for="k-preco">Preço médio pago</label>
      <input type="number" id="k-preco" name="preco_medio" min="0.01" step="0.01" placeholder="Ex.: 38,50" required>
      <span class="dica">Por ação. Em reais para ações da B3 e em dólares para ações dos EUA.</span>
    </div>

    <div class="modal-botoes">
      <button type="button" class="btn-borda" data-fechar>Cancelar</button>
      <button type="submit" class="btn">Salvar</button>
    </div>
  </form>
</dialog>

<script src="../js/app.js"></script>
</body>
</html>
