<?php require __DIR__ . '/../../config/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Categorias · Fluxo</title>
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
        <a href="transacoes.php">Transações</a>
        <a class="ativo" href="categorias.php">Categorias</a>
      </nav>
    </div>
    <div class="usuario">
      <strong id="nome-usuario">Nome do usuário</strong>
      <a href="../../api/logout.php">Sair</a>
    </div>
  </aside>

  <main class="conteudo">

    <div class="topo">
      <h1>Categorias</h1>
      <button class="btn" data-abrir="modal-categoria">Nova categoria</button>
    </div>
    <p class="mudo" style="font-size:14px; margin:12px 0 0; max-width:520px; line-height:1.55">
      Os tipos de entrada e saída que você usa nas transações. Em saídas, o limite mensal é opcional e aparece no dashboard.
    </p>

    <!-- Mensagem geral da página (ex.: "Categoria excluída." ou erro de FK) -->
    <div class="msg" id="msg-categorias" role="status" style="margin-top:24px"></div>

    <!-- INTEGRAR: GET ../../api/categorias.php -> separar por tipo e preencher as duas listas -->
    <section class="secao duas-colunas" style="margin-top:48px">

      <div>
        <div class="secao-topo">
          <h2>Saídas <span class="contador" id="qtd-saidas">4</span></h2>
          <span class="rotulo cabecalho-limite">Limite mensal</span>
        </div>
        <div id="lista-saidas">
          <div class="categoria-linha" data-id="3">
            <span class="nome">Aluguel</span><span class="num mudo">R$ 1.200,00</span>
            <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
          </div>
          <div class="categoria-linha" data-id="4">
            <span class="nome">Alimentação</span><span class="num mudo">R$ 600,00</span>
            <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
          </div>
          <div class="categoria-linha" data-id="5">
            <span class="nome">Transporte</span><span class="num mudo">R$ 250,00</span>
            <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
          </div>
          <div class="categoria-linha" data-id="6">
            <span class="nome">Lazer</span><span class="num mudo">R$ 150,00</span>
            <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
          </div>
        </div>
      </div>

      <div>
        <div class="secao-topo">
          <h2>Entradas <span class="contador" id="qtd-entradas">2</span></h2>
        </div>
        <div id="lista-entradas">
          <div class="categoria-linha" data-id="1" style="grid-template-columns:1fr 110px">
            <span class="nome">Salário</span>
            <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
          </div>
          <div class="categoria-linha" data-id="2" style="grid-template-columns:1fr 110px">
            <span class="nome">Bolsa estágio</span>
            <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
          </div>
        </div>
      </div>

    </section>

  </main>
</div>

<!-- ===== Modal: nova / editar categoria =====
     Campos com os nomes das colunas da tabela categoria (id_usuario vem da sessão no PHP).
     INTEGRAR: POST (novo) ou PUT (editar, com id) para ../../api/categorias.php.
     Excluir: o banco usa ON DELETE RESTRICT, então categoria com transação vai dar erro ->
     mostrar "Essa categoria tem transações. Apague ou mova as transações antes." -->
<dialog id="modal-categoria">
  <form id="form-categoria" method="dialog" novalidate>
    <h2>Nova categoria</h2>
    <div class="msg erro" id="msg-categoria" role="alert"></div>
    <input type="hidden" name="id">

    <div class="campo">
      <label for="c-nome">Nome</label>
      <input type="text" id="c-nome" name="nome" maxlength="20" placeholder="Ex.: Bolsa estágio" required>
      <span class="dica">Até 20 caracteres. Não pode repetir.</span>
    </div>

    <div class="campo">
      <label>Tipo</label>
      <div class="grupo-radio">
        <label><input type="radio" name="tipo" value="saida" checked> Saída</label>
        <label><input type="radio" name="tipo" value="entrada"> Entrada</label>
      </div>
    </div>

    <div class="campo" id="campo-limite">
      <label for="c-limite">Limite mensal (R$)</label>
      <input type="number" id="c-limite" name="limite_mensal" min="0" step="0.01" placeholder="Opcional">
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
