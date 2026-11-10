<?php require __DIR__ . '/../../config/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Transações · Fluxo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Inter+Tight:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>


<div class="app">

  <aside class="lateral">
    <div>
      <a class="marca" href="dashboard.php">Fluxo</a>
      <nav class="menu" aria-label="Principal">
        <a href="dashboard.php">Dashboard</a>
        <a class="ativo" href="transacoes.php">Transações</a>
        <a href="categorias.php">Categorias</a>
      </nav>
    </div>
    <div class="usuario">
      <strong id="nome-usuario">Nome do usuário</strong>
      <a href="../../api/logout.php">Sair</a>
    </div>
  </aside>

  <main class="conteudo">

    <div class="topo">
      <h1>Transações</h1>
      <button class="btn" data-abrir="modal-transacao">Nova transação</button>
    </div>

    <!-- INTEGRAR: mudar mês / tipo / categoria -> GET ../../api/transacoes.php?mes=2026-11&tipo=saida&categoria=4 -->
    <div class="filtros">
      <div class="seletor-mes">
        <button class="btn-mes" data-mes="-1" aria-label="Mês anterior">‹</button>
        <span id="mes-atual">Nov 2026</span>
        <button class="btn-mes" data-mes="1" aria-label="Próximo mês">›</button>
      </div>
      <div class="abas" role="group" aria-label="Filtrar por tipo">
        <button class="ativo" data-tipo="">Todas</button>
        <button data-tipo="entrada">Entradas</button>
        <button data-tipo="saida">Saídas</button>
      </div>
      <select id="filtro-categoria" aria-label="Filtrar por categoria">
        <option value="">Todas as categorias</option>
        <option value="1">Salário</option>
        <option value="2">Bolsa estágio</option>
        <option value="3">Aluguel</option>
        <option value="4">Alimentação</option>
        <option value="5">Transporte</option>
        <option value="6">Lazer</option>
      </select>
    </div>

    <div class="totais">
      <div><span class="rotulo">Entrou</span><strong class="num" id="total-entradas">R$ 3.350,00</strong></div>
      <div><span class="rotulo">Saiu</span><strong class="num" id="total-saidas">R$ 1.965,40</strong></div>
      <div><span class="rotulo">Saldo</span><strong class="num" id="saldo">R$ 1.384,60</strong></div>
    </div>

    <div class="lista-cabecalho rotulo">
      <span>Data</span><span>Descrição</span><span>Categoria</span><span>Valor</span><span></span>
    </div>

    <!-- Linhas geradas pelo JS a partir do JSON. Mantenha essa estrutura (o CSS depende dela).
         Entrada -> classe "entrada" e "+"; saída -> classe "saida" e "−". -->
    <div id="lista-transacoes">
      <div class="lista-linha" data-id="1">
        <span class="rotulo num">24 nov</span><span class="descricao">Show no fim de semana</span><span class="cat mudo">Lazer</span>
        <span class="valor saida num">− R$ 180,00</span>
        <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
      </div>
      <div class="lista-linha" data-id="2">
        <span class="rotulo num">20 nov</span><span class="descricao">Bolsa estágio</span><span class="cat mudo">Bolsa estágio</span>
        <span class="valor entrada num">+ R$ 850,00</span>
        <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
      </div>
      <div class="lista-linha" data-id="3">
        <span class="rotulo num">15 nov</span><span class="descricao">Mercado do mês</span><span class="cat mudo">Alimentação</span>
        <span class="valor saida num">− R$ 266,50</span>
        <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
      </div>
      <div class="lista-linha" data-id="6">
        <span class="rotulo num">12 nov</span><span class="descricao">Recarga do cartão de ônibus</span><span class="cat mudo">Transporte</span>
        <span class="valor saida num">− R$ 120,00</span>
        <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
      </div>
      <div class="lista-linha" data-id="7">
        <span class="rotulo num">10 nov</span><span class="descricao">Feira</span><span class="cat mudo">Alimentação</span>
        <span class="valor saida num">− R$ 145,90</span>
        <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
      </div>
      <div class="lista-linha" data-id="8">
        <span class="rotulo num">08 nov</span><span class="descricao">Corrida de app</span><span class="cat mudo">Transporte</span>
        <span class="valor saida num">− R$ 53,00</span>
        <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
      </div>
      <div class="lista-linha" data-id="4">
        <span class="rotulo num">05 nov</span><span class="descricao">Salário</span><span class="cat mudo">Salário</span>
        <span class="valor entrada num">+ R$ 2.500,00</span>
        <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
      </div>
      <div class="lista-linha" data-id="5">
        <span class="rotulo num">05 nov</span><span class="descricao">Aluguel</span><span class="cat mudo">Aluguel</span>
        <span class="valor saida num">− R$ 1.200,00</span>
        <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
      </div>
    </div>

    <!-- Mostrar quando o JSON vier vazio (e esconder #lista-transacoes) -->
    <div class="vazio" id="sem-transacoes" hidden>Nenhuma transação neste mês.</div>

  </main>
</div>

<!-- ===== Modal: nova / editar transação (igual ao de transacoes.html) =====
     Campos com os mesmos nomes das colunas da tabela transacao.
     INTEGRAR: POST (novo) ou PUT (editar, com id) para ../../api/transacoes.php -->
<dialog id="modal-transacao">
  <form id="form-transacao" method="dialog" novalidate>
    <h2>Nova transação</h2>
    <div class="msg erro" id="msg-transacao" role="alert"></div>
    <input type="hidden" name="id">

    <div class="campo">
      <label>Tipo</label>
      <div class="grupo-radio">
        <label><input type="radio" name="tipo" value="saida" checked> Saída</label>
        <label><input type="radio" name="tipo" value="entrada"> Entrada</label>
      </div>
      <span class="dica">Filtra as categorias abaixo.</span>
    </div>

    <div class="campo">
      <label for="t-categoria">Categoria</label>
      <!-- INTEGRAR: preencher com GET ../../api/categorias.php (value = id da categoria) -->
      <select id="t-categoria" name="id_categoria" required>
        <option value="">Selecione...</option>
        <option value="3" data-tipo="saida">Aluguel</option>
        <option value="4" data-tipo="saida">Alimentação</option>
        <option value="5" data-tipo="saida">Transporte</option>
        <option value="6" data-tipo="saida">Lazer</option>
        <option value="1" data-tipo="entrada">Salário</option>
        <option value="2" data-tipo="entrada">Bolsa estágio</option>
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
