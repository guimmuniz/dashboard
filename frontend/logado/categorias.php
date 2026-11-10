<?php require __DIR__ . '/../../../config/auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Categorias · Nome do projeto</title>
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
        <a class="ativo" href="categorias.php" aria-current="page"><svg class="menu-ico" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.6 2.75H4.25a1.5 1.5 0 0 0-1.5 1.5V9.6a1.5 1.5 0 0 0 .44 1.06l4.9 4.9a1.5 1.5 0 0 0 2.12 0l5.35-5.35a1.5 1.5 0 0 0 0-2.12l-4.9-4.9A1.5 1.5 0 0 0 9.6 2.75Z"/><circle cx="6.25" cy="6.25" r="1" fill="currentColor" stroke="none"/></svg>Categorias</a>
      </nav>
    </div>
    <!-- O avatar e a trilha leem o nome de #nome-usuario sozinhos (ver app.js) -->
    <div class="usuario">
      <span class="avatar" data-avatar-usuario aria-hidden="true"></span>
      <div class="usuario-texto">
        <strong id="nome-usuario">Nome do usuário</strong>
        <a href="../../../api/logout.php">Sair</a>
      </div>
    </div>
  </aside>

  <main class="conteudo">

    <nav class="trilha" aria-label="Você está em">
      <ol>
        <li><a href="dashboard.php"><span class="avatar avatar-pp" data-avatar-usuario aria-hidden="true"></span><span data-primeiro-nome></span></a></li>
        <li aria-hidden="true"><svg class="separador" viewBox="0 0 12 12" fill="none"><path d="M4.5 2.5L8 6l-3.5 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></li>
        <li aria-current="page">Categorias</li>
      </ol>
    </nav>

    <div class="topo">
      <h1>Categorias</h1>
      <button class="btn" data-abrir="modal-categoria"><svg class="ico" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 4.5v11M4.5 10h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>Nova categoria</button>
    </div>
    <p class="mudo" style="font-size:14px; margin:12px 0 0; max-width:520px; line-height:1.55">
      Os tipos de entrada e saída que você usa nas transações. Em saídas, o limite mensal é opcional e aparece no dashboard.
    </p>

    <!-- Mensagem geral da página (ex.: "Categoria excluída." ou erro de FK) -->
    <div class="msg" id="msg-categorias" role="status" style="margin-top:24px"></div>

    <!-- INTEGRAR: GET ../../api/categorias.php -> separar por tipo e preencher as duas listas -->
    <!-- Linha de saída: .nome, .num (limite; vazio = "Sem limite"), .acoes. Linha de entrada: .nome, .acoes.
         Os contadores #qtd-saidas e #qtd-entradas se atualizam sozinhos (app.js). -->
    <section class="secao duas-colunas" style="margin-top:48px">

      <div>
        <div class="secao-topo">
          <h2><i class="marca-cor cor-saida" aria-hidden="true"></i>Saídas <span class="contador" id="qtd-saidas">4</span></h2>
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
          <h2><i class="marca-cor cor-entrada" aria-hidden="true"></i>Entradas <span class="contador" id="qtd-entradas">2</span></h2>
        </div>
        <div id="lista-entradas">
          <div class="categoria-linha" data-id="1">
            <span class="nome">Salário</span>
            <span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>
          </div>
          <div class="categoria-linha" data-id="2">
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
    <div class="modal-topo">
      <h2>Nova categoria</h2>
      <button type="button" class="fechar fechar-m" data-fechar aria-label="Fechar"><svg width="16.2" height="16.2" viewBox="0 0 16.2 16.2" fill="none" aria-hidden="true"><path d="M2 2L14.2 14.2M14.2 2L2 14.2" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></button>
    </div>
    <div class="msg erro" id="msg-categoria" role="alert"></div>
    <input type="hidden" name="id">

    <div class="campo">
      <label for="c-nome">Nome</label>
      <input type="text" id="c-nome" name="nome" maxlength="20" placeholder="Ex.: Bolsa estágio" required autofocus>
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
