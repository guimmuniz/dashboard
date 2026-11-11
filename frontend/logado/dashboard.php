<?php require __DIR__ . '/../../config/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard · Nome do projeto</title>
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
        <a class="ativo" href="dashboard.php" aria-current="page"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.75" y="2.75" width="5" height="5" rx="1.5"/><rect x="10.25" y="2.75" width="5" height="5" rx="1.5"/><rect x="2.75" y="10.25" width="5" height="5" rx="1.5"/><rect x="10.25" y="10.25" width="5" height="5" rx="1.5"/></svg>Dashboard</a>
        <a href="transacoes.php"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 6h11l-3-3M14.5 12h-11l3 3"/></svg>Transações</a>
        <a href="categorias.php"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.6 2.75H4.25a1.5 1.5 0 0 0-1.5 1.5V9.6a1.5 1.5 0 0 0 .44 1.06l4.9 4.9a1.5 1.5 0 0 0 2.12 0l5.35-5.35a1.5 1.5 0 0 0 0-2.12l-4.9-4.9A1.5 1.5 0 0 0 9.6 2.75Z"/><circle cx="6.25" cy="6.25" r="1" fill="currentColor" stroke="none"/></svg>Categorias</a>
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
        <li aria-current="page">Dashboard</li>
      </ol>
    </nav>

    <h1 class="so-leitor">Dashboard</h1>

    <div class="topo">
      <div class="seletor-mes">
        <button class="btn-mes" data-mes="-1" aria-label="Mês anterior"><svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M11 4.5L6.5 9l4.5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <span id="mes-atual" aria-live="polite">Nov 2026</span>
        <button class="btn-mes" data-mes="1" aria-label="Próximo mês"><svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M7 4.5L11.5 9 7 13.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      </div>
      <button class="btn" data-abrir="modal-transacao"><svg class="ico" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>Nova transação</button>
    </div>

    <!-- SALDO DO MÊS = total de entradas - total de saídas -->
    <section class="saldo">
      <div class="rotulo">Saldo de <span id="nome-mes">novembro</span></div>
      <div class="saldo-valor num" id="saldo">R$ 0<span class="centavos">,00</span></div>
      <p id="saldo-frase"></p>
    </section>

    <section class="resumo">
      <div>
        <span class="resumo-icone entrada" aria-hidden="true"><svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.5 5.5l-7 7M5.5 7v5.5H11"/></svg></span>
        <div class="rotulo">Entradas</div>
        <div class="valor num" id="total-entradas">R$ 0,00</div>
      </div>
      <div>
        <span class="resumo-icone saida" aria-hidden="true"><svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5.5 12.5l7-7M7 5.5h5.5V11"/></svg></span>
        <div class="rotulo">Saídas</div>
        <div class="valor num" id="total-saidas">R$ 0,00</div>
      </div>
      <div>
        <span class="resumo-icone" aria-hidden="true"><svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.6 2.75H4.25a1.5 1.5 0 0 0-1.5 1.5V9.6a1.5 1.5 0 0 0 .44 1.06l4.9 4.9a1.5 1.5 0 0 0 2.12 0l5.35-5.35a1.5 1.5 0 0 0 0-2.12l-4.9-4.9A1.5 1.5 0 0 0 9.6 2.75Z"/><circle cx="6.25" cy="6.25" r="1" fill="currentColor" stroke="none"/></svg></span>
        <div class="rotulo">Maior gasto</div>
        <div class="resumo-linha">
          <div class="valor" id="maior-categoria">—</div>
          <!-- INTEGRAR (opcional): parte do maior gasto no total de saídas do mês, ex.: "61% das saídas".
               Se não for calcular, deixe o span vazio (sem espaço nem quebra de linha dentro) que ele some. -->
          <span class="selo" id="maior-categoria-parte"></span>
        </div>
      </div>
    </section>

    <section class="secao">

      <div>
        <div class="secao-topo">
          <h2>Entradas e saídas</h2>
          <div class="legenda rotulo">
            <span><i class="cor-entrada"></i>Entradas</span>
            <span><i class="cor-saida"></i>Saídas</span>
          </div>
        </div>
        <!-- Gráfico de linhas desenhado pelo JS dentro de #grafico (desenharGrafico em app.js).
             INTEGRAR: com os totais do mês em mãos, chamar
               desenharGrafico([{ rotulo: 'Sem 1', entradas: 2500, saidas: 1200 }, ...])
             Um item por semana, valores em reais. O data-semanas abaixo é só o exemplo inicial. -->
        <div class="grafico" id="grafico"
             data-semanas='[]'></div>
      </div>

    </section>

    <section class="secao duas-colunas">

      <div>
        <div class="secao-topo">
          <h2>Gastos por categoria</h2>
          <span class="rotulo">% das saídas</span>
        </div>
        <!-- INTEGRAR: uma linha por categoria de saída com gasto no mês, da maior para a menor.
             --p = gasto da categoria / total de saídas do mês (o mesmo número mostrado em .parte).
             Sem saídas no mês: esconder #lista-gastos e mostrar #sem-gastos. -->
        <div id="lista-gastos" role="list">
        </div>
        <div class="vazio" id="sem-gastos" hidden>Nenhuma saída neste mês.</div>
      </div>

      <div>
        <div class="secao-topo">
          <h2>Limites por categoria</h2>
          <a class="link" href="categorias.php">Editar</a>
        </div>
        <!-- Só categorias de saída com limite_mensal preenchido.
             --p = gasto / limite (máx. 100%).
             Gasto em 80% do limite ou mais -> classe "atencao" (barra amarela).
             Passou do limite -> classe "estourou" (barra vermelha) + a linha .orcamento-nota.
             role="meter" e os aria-* são para leitores de tela: aria-valuenow = gasto (no máximo o limite). -->
        <div id="lista-limites">
        </div>
      </div>

    </section>

    <section class="secao">
      <div class="secao-topo">
        <h2>Últimas transações</h2>
        <a class="link" href="transacoes.php">Ver todas →</a>
      </div>
      <!-- 5 mais recentes do mês. Mesma estrutura de linha usada em transacoes.html -->
      <div id="ultimas-transacoes">
      </div>
    </section>

  </main>
</div>

<!-- ===== Modal: nova / editar transação (igual ao de transacoes.html) =====
     Campos com os mesmos nomes das colunas da tabela transacao.
     INTEGRAR: POST (novo) ou PUT (editar, com id) para ../../api/transacoes.php -->
<dialog id="modal-transacao" aria-labelledby="titulo-modal-transacao">
  <form id="form-transacao" method="dialog" novalidate>
    <div class="modal-topo">
      <h2 id="titulo-modal-transacao">Nova transação</h2>
      <button type="button" class="fechar fechar-m" data-fechar aria-label="Fechar"><svg width="16.2" height="16.2" viewBox="0 0 16.2 16.2" fill="none" aria-hidden="true"><path d="M2 2L14.2 14.2M14.2 2L2 14.2" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></button>
    </div>
    <div class="msg erro" id="msg-transacao" role="alert"></div>
    <input type="hidden" name="id">

    <div class="campo">
      <label id="t-tipo-rotulo">Tipo</label>
      <div class="grupo-radio" role="radiogroup" aria-labelledby="t-tipo-rotulo" aria-describedby="t-tipo-dica">
        <label><input type="radio" name="tipo" value="saida" checked autofocus> Saída</label>
        <label><input type="radio" name="tipo" value="entrada"> Entrada</label>
      </div>
      <span class="dica" id="t-tipo-dica">Filtra as categorias abaixo.</span>
    </div>

    <div class="campo">
      <label for="t-categoria">Categoria</label>
      <!-- INTEGRAR: preencher com GET ../../api/categorias.php (value = id da categoria) -->
      <select id="t-categoria" name="id_categoria" required>
        <option value="">Selecione...</option>
      </select>
    </div>

    <div class="campo">
      <label for="t-descricao">Descrição</label>
      <input type="text" id="t-descricao" name="descricao" maxlength="100" placeholder="Ex.: mercado do mês">
    </div>

    <div class="campo">
      <label for="t-valor">Valor (R$)</label>
      <input type="number" id="t-valor" name="valor" min="0.01" step="0.01" required>
    </div>

    <div class="campo">
      <label for="t-data">Data</label>
      <input type="date" id="t-data" name="data_transacao" required>
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
