<?php require __DIR__ . '/../../config/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard · Fluxo</title>
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
        <a class="ativo" href="dashboard.php">Dashboard</a>
        <a href="transacoes.php">Transações</a>
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
      <div class="seletor-mes">
        <button class="btn-mes" data-mes="-1" aria-label="Mês anterior">‹</button>
        <span id="mes-atual">Nov 2026</span>
        <button class="btn-mes" data-mes="1" aria-label="Próximo mês">›</button>
      </div>
      <button class="btn" data-abrir="modal-transacao">Nova transação</button>
    </div>

    <!-- SALDO DO MÊS = total de entradas - total de saídas -->
    <section class="saldo">
      <div class="rotulo">Saldo de <span id="nome-mes">novembro</span></div>
      <div class="saldo-valor num" id="saldo">R$ 1.384<span class="centavos">,60</span></div>
      <p id="saldo-frase">Sobrou 41% do que entrou neste mês.</p>
    </section>

    <section class="resumo">
      <div>
        <div class="rotulo">Entradas</div>
        <div class="valor num" id="total-entradas">R$ 3.350,00</div>
      </div>
      <div>
        <div class="rotulo">Saídas</div>
        <div class="valor num" id="total-saidas">R$ 1.965,40</div>
      </div>
      <div>
        <div class="rotulo">Maior gasto</div>
        <div class="valor" id="maior-categoria">Aluguel</div>
      </div>
    </section>

    <section class="secao duas-colunas">

      <div>
        <div class="secao-topo">
          <h2>Entradas e saídas</h2>
          <div class="legenda rotulo">
            <span><i class="cor-entrada"></i>Entradas</span>
            <span><i class="cor-saida"></i>Saídas</span>
          </div>
        </div>
        <!-- Cada .barra recebe a altura em % do maior valor do mês: style="--h: 60%".
             O JS só precisa calcular esse número por semana. -->
        <div class="grafico" id="grafico">
          <div class="semana"><div class="barra cor-entrada" style="--h: 100%" title="Entradas: R$ 2.500,00"></div><div class="barra cor-saida" style="--h: 48%" title="Saídas: R$ 1.200,00"></div></div>
          <div class="semana"><div class="barra cor-entrada" style="--h: 0%"></div><div class="barra cor-saida" style="--h: 13%" title="Saídas: R$ 318,90"></div></div>
          <div class="semana"><div class="barra cor-entrada" style="--h: 34%" title="Entradas: R$ 850,00"></div><div class="barra cor-saida" style="--h: 11%" title="Saídas: R$ 266,50"></div></div>
          <div class="semana"><div class="barra cor-entrada" style="--h: 0%"></div><div class="barra cor-saida" style="--h: 7%" title="Saídas: R$ 180,00"></div></div>
        </div>
        <div class="eixo rotulo"><span>Sem 1</span><span>Sem 2</span><span>Sem 3</span><span>Sem 4</span></div>
      </div>

      <div>
        <div class="secao-topo">
          <h2>Limites por categoria</h2>
          <a class="link" href="categorias.php">Editar</a>
        </div>
        <!-- Só categorias de saída com limite_mensal preenchido.
             --p = gasto / limite (máx. 100%). Passou do limite -> classe "estourou". -->
        <div id="lista-limites">
          <div class="orcamento">
            <div class="orcamento-topo"><span>Aluguel</span><span class="num mudo">1.200 / 1.200</span></div>
            <div class="trilho"><div style="--p: 100%"></div></div>
          </div>
          <div class="orcamento">
            <div class="orcamento-topo"><span>Alimentação</span><span class="num mudo">412 / 600</span></div>
            <div class="trilho"><div style="--p: 69%"></div></div>
          </div>
          <div class="orcamento">
            <div class="orcamento-topo"><span>Transporte</span><span class="num mudo">173 / 250</span></div>
            <div class="trilho"><div style="--p: 69%"></div></div>
          </div>
          <div class="orcamento estourou">
            <div class="orcamento-topo"><span>Lazer</span><span class="num">180 / 150 · acima do limite</span></div>
            <div class="trilho"><div style="--p: 100%"></div></div>
          </div>
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
        <div class="lista-linha" data-id="1">
          <span class="rotulo num">24 nov</span>
          <span class="descricao">Show no fim de semana</span>
          <span class="cat mudo">Lazer</span>
          <span class="valor saida num">− R$ 180,00</span>
          <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
        </div>
        <div class="lista-linha" data-id="2">
          <span class="rotulo num">20 nov</span>
          <span class="descricao">Bolsa estágio</span>
          <span class="cat mudo">Bolsa estágio</span>
          <span class="valor entrada num">+ R$ 850,00</span>
          <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
        </div>
        <div class="lista-linha" data-id="3">
          <span class="rotulo num">15 nov</span>
          <span class="descricao">Mercado do mês</span>
          <span class="cat mudo">Alimentação</span>
          <span class="valor saida num">− R$ 266,50</span>
          <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
        </div>
        <div class="lista-linha" data-id="4">
          <span class="rotulo num">05 nov</span>
          <span class="descricao">Salário</span>
          <span class="cat mudo">Salário</span>
          <span class="valor entrada num">+ R$ 2.500,00</span>
          <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
        </div>
        <div class="lista-linha" data-id="5">
          <span class="rotulo num">05 nov</span>
          <span class="descricao">Aluguel</span>
          <span class="cat mudo">Aluguel</span>
          <span class="valor saida num">− R$ 1.200,00</span>
          <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
        </div>
      </div>
    </section>

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
