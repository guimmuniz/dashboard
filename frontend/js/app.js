// ==========================================================
// Comportamento da interface (só front, sem backend)
// Tudo marcado com "INTEGRAR" é onde entra o fetch para a API.
//
// Funções prontas para usar na integração:
//   notificar({ titulo, descricao, status })   aviso flutuante (sucesso | erro | info)
//   confirmar({ titulo, texto, acao })         Promise<boolean> com modal de confirmação
//   desenharGrafico(semanas)                   redesenha o gráfico do dashboard
//   mostrarMsg(el, texto, tipo)                mensagem dentro de um formulário
//   Contadores (#qtd-transacoes, #qtd-saidas, #qtd-entradas) se atualizam sozinhos.
// ==========================================================

// Caminho da API a partir deste arquivo (frontend/js/app.js -> dashboard/api/).
// Funciona igual em index.html e em logado/*.php. Uso: fetch(API + 'transacoes.php')
const API = new URL('../../api/', document.currentScript.src).href;

const MESES = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
  'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

const SEM_ANIMACAO = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ---------- mensagens ----------
function mostrarMsg(el, texto, tipo = 'erro') {
  if (!el) return;
  el.className = 'msg ' + tipo;
  el.textContent = texto;
}

// ---------- notificações (BoardUI Notification) ----------
// notificar({ titulo: 'Transação salva', descricao: 'Mercado do mês', status: 'sucesso' })
// status: 'sucesso' | 'erro' | 'info'. duracao em ms (0 = fica até fechar).
// A contagem pausa enquanto o mouse ou o foco estiver sobre o aviso.
const ICONES_AVISO = {
  sucesso: 'M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM11.0026 16L18.0737 8.92893L16.6595 7.51472L11.0026 13.1716L8.17421 10.3431L6.75999 11.7574L11.0026 16Z',
  erro: 'M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM11 15V17H13V15H11ZM11 7V13H13V7H11Z',
  info: 'M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM11 11V17H13V11H11ZM11 7V9H13V7H11Z',
};
const X_PEQUENO = '<svg width="10.8" height="10.8" viewBox="0 0 10.8 10.8" fill="none" aria-hidden="true"><path d="M2 2L8.8 8.8M8.8 2L2 8.8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
const X_MEDIO = '<svg width="16.2" height="16.2" viewBox="0 0 16.2 16.2" fill="none" aria-hidden="true"><path d="M2 2L14.2 14.2M14.2 2L2 14.2" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>';

// A região já existe vazia desde o carregamento: leitores de tela só anunciam
// o que entra numa região viva que já estava na página.
const PILHA_AVISOS = document.createElement('div');
PILHA_AVISOS.className = 'avisos';
PILHA_AVISOS.setAttribute('role', 'region');
PILHA_AVISOS.setAttribute('aria-label', 'Notificações');
PILHA_AVISOS.setAttribute('aria-live', 'polite');
document.body.append(PILHA_AVISOS);

function notificar({ titulo, descricao = '', status = 'sucesso', duracao = 5000 }) {

  const aviso = document.createElement('div');
  aviso.className = 'aviso ' + status;
  if (status === 'erro') aviso.setAttribute('role', 'alert');
  aviso.innerHTML =
    '<span class="aviso-icone"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="' + (ICONES_AVISO[status] || ICONES_AVISO.info) + '"/></svg></span>' +
    '<div class="aviso-texto"><p class="aviso-titulo"></p></div>' +
    '<button type="button" class="fechar" aria-label="Fechar notificação">' + X_PEQUENO + '</button>';
  aviso.querySelector('.aviso-titulo').textContent = titulo;
  if (descricao) {
    const p = document.createElement('p');
    p.className = 'aviso-descricao';
    p.textContent = descricao;
    aviso.querySelector('.aviso-texto').append(p);
  }

  function fechar() {
    if (aviso.classList.contains('saindo')) return;
    aviso.classList.add('saindo');
    setTimeout(() => aviso.remove(), SEM_ANIMACAO ? 0 : 180);
  }
  aviso.querySelector('.fechar').addEventListener('click', fechar);

  if (duracao > 0) {
    // A barra de contagem é a própria contagem: quando a animação termina, o aviso fecha.
    const barra = document.createElement('span');
    barra.className = 'aviso-tempo';
    barra.setAttribute('aria-hidden', 'true');
    aviso.append(barra);
    const contagem = barra.animate(
      [{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }],
      { duration: duracao, easing: 'linear', fill: 'forwards' });
    contagem.onfinish = fechar;
    let sobre = false, foco = false;
    const sincronizar = () => {
      if (sobre || foco) contagem.pause();
      else if (contagem.playState !== 'finished') contagem.play();
    };
    aviso.addEventListener('mouseenter', () => { sobre = true; sincronizar(); });
    aviso.addEventListener('mouseleave', () => { sobre = false; sincronizar(); });
    aviso.addEventListener('focusin', () => { foco = true; sincronizar(); });
    aviso.addEventListener('focusout', () => { foco = false; sincronizar(); });
  }

  PILHA_AVISOS.append(aviso);
  return aviso;
}

// ---------- confirmação (modal com botão de perigo) ----------
// const ok = await confirmar({ titulo: 'Excluir transação?', texto: '...', acao: 'Excluir' });
function confirmar({ titulo, texto = '', acao = 'Excluir' }) {
  let modal = document.getElementById('modal-confirmar');
  if (!modal) {
    modal = document.createElement('dialog');
    modal.id = 'modal-confirmar';
    modal.setAttribute('aria-labelledby', 'titulo-modal-confirmar');
    modal.setAttribute('aria-describedby', 'texto-modal-confirmar');
    modal.innerHTML =
      '<div class="modal-topo"><h2 id="titulo-modal-confirmar"></h2><button type="button" class="fechar fechar-m" value="nao" aria-label="Fechar">' + X_MEDIO + '</button></div>' +
      '<p class="modal-texto" id="texto-modal-confirmar"></p>' +
      '<div class="modal-botoes"><button type="button" class="btn-borda" value="nao">Cancelar</button><button type="button" class="btn-perigo" value="sim"></button></div>';
    modal.addEventListener('click', e => {
      const botao = e.target.closest('button[value]');
      if (botao) modal.close(botao.value);
    });
    document.body.append(modal);
  }
  modal.querySelector('h2').textContent = titulo;
  modal.querySelector('.modal-texto').textContent = texto;
  modal.querySelector('.btn-perigo').textContent = acao;
  modal.returnValue = 'nao';
  modal.showModal();
  modal.querySelector('.btn-borda').focus(); // o foco começa no Cancelar, não na ação destrutiva

  return new Promise(resolve => {
    modal.addEventListener('close', () => resolve(modal.returnValue === 'sim'), { once: true });
  });
}

// ---------- usuário (avatar e trilha) ----------
// Basta colocar o nome em #nome-usuario: a inicial do avatar e o primeiro nome
// na trilha se atualizam sozinhos.
const nomeUsuario = document.getElementById('nome-usuario');
function atualizarUsuario() {
  const nome = nomeUsuario.textContent.trim();
  document.querySelectorAll('[data-avatar-usuario]').forEach(el => { el.textContent = (Array.from(nome)[0] || '?').toUpperCase(); });
  document.querySelectorAll('[data-primeiro-nome]').forEach(el => { el.textContent = nome.split(/\s+/)[0] || 'Você'; });
}
if (nomeUsuario) {
  atualizarUsuario();
  new MutationObserver(atualizarUsuario).observe(nomeUsuario, { childList: true, characterData: true, subtree: true });
}

// ---------- seletor de mês ----------
let mesAtual = new Date();   // começa no mês de hoje
mesAtual.setDate(1);         // sempre no dia 1, para somar meses sem erro

// mês no formato que a API espera, ex.: "2026-11"
function mesParaApi() {
  return mesAtual.getFullYear() + '-' + String(mesAtual.getMonth() + 1).padStart(2, '0');
}

function atualizarMes() {
  const span = document.getElementById('mes-atual');
  const nome = document.getElementById('nome-mes');
  const abrev = MESES[mesAtual.getMonth()].slice(0, 3);
  if (span) span.textContent = abrev[0].toUpperCase() + abrev.slice(1) + ' ' + mesAtual.getFullYear();
  if (nome) nome.textContent = MESES[mesAtual.getMonth()];
  carregarTransacoes();   // recarrega a lista do mês escolhido (página de transações)
  carregarDashboard();    // recarrega o resumo do mês (página do dashboard)
}

document.querySelectorAll('[data-mes]').forEach(btn => {
  btn.addEventListener('click', () => {
    // sempre no dia 1: somar meses a partir do dia 31 pularia um mês
    mesAtual = new Date(mesAtual.getFullYear(), mesAtual.getMonth() + Number(btn.dataset.mes), 1);
    atualizarMes();
  });
});

// ---------- modais (<dialog>) ----------
document.querySelectorAll('[data-abrir]').forEach(btn => {
  btn.addEventListener('click', () => abrirModal(btn.dataset.abrir));
});

function abrirModal(id, titulo) {
  const modal = document.getElementById(id);
  if (!modal) return;
  const form = modal.querySelector('form');
  form.reset();
  form.querySelector('[name="id"]').value = '';
  modal.querySelector('h2').textContent = titulo || (id === 'modal-categoria' ? 'Nova categoria' : 'Nova transação');
  mostrarMsg(modal.querySelector('.msg'), '');

  const data = form.querySelector('[name="data_transacao"]');
  if (data) {
    // data de hoje no fuso local (toISOString usaria UTC e viraria "amanhã" depois das 21h no Brasil)
    const hoje = new Date();
    data.value = hoje.getFullYear() + '-' + String(hoje.getMonth() + 1).padStart(2, '0') + '-' + String(hoje.getDate()).padStart(2, '0');
  }

  atualizarTipo(form);
  modal.showModal();
}

document.querySelectorAll('[data-fechar]').forEach(btn => {
  btn.addEventListener('click', () => btn.closest('dialog').close());
});

// ---------- tipo entrada/saída dentro dos modais ----------
// Transação: mostra só as categorias do tipo escolhido.
// Categoria: esconde o limite mensal quando o tipo é entrada.
function atualizarTipo(form) {
  const marcado = form.querySelector('[name="tipo"]:checked');
  if (!marcado) return;
  const tipo = marcado.value;

  const select = form.querySelector('[name="id_categoria"]');
  if (select) {
    select.querySelectorAll('option[data-tipo]').forEach(op => { op.hidden = op.dataset.tipo !== tipo; });
    if (select.selectedOptions[0]?.hidden) select.value = '';
  }

  const limite = document.getElementById('campo-limite');
  if (limite && form.getAttribute('id') === 'form-categoria') {
    limite.hidden = tipo === 'entrada';
    if (tipo === 'entrada') form.querySelector('[name="limite_mensal"]').value = '';
  }
}

document.querySelectorAll('dialog form').forEach(form => {
  form.querySelectorAll('[name="tipo"]').forEach(r => r.addEventListener('change', () => atualizarTipo(form)));
});

// ---------- editar / excluir nas listas ----------
document.addEventListener('click', async e => {
  const linha = e.target.closest('[data-id]');
  if (!linha) return;
  const ehCategoria = linha.classList.contains('categoria-linha');

  if (e.target.matches('[data-editar]')) {
    abrirModal(ehCategoria ? 'modal-categoria' : 'modal-transacao',
      ehCategoria ? 'Editar categoria' : 'Editar transação');
    document.querySelector('dialog[open] [name="id"]').value = linha.dataset.id;
    if (ehCategoria) {
      // os dados já foram carregados na lista (variável categorias): é só preencher o formulário
      const c = categorias.find(c => c.id == linha.dataset.id);
      const form = document.getElementById('form-categoria');
      form.nome.value = c.nome;
      form.tipo.value = c.tipo;
      form.limite_mensal.value = c.limite_mensal || '';
      atualizarTipo(form);
    } else {
      const t = transacoes.find(t => t.id == linha.dataset.id);
      const form = document.getElementById('form-transacao');
      form.tipo.value = t.tipo;
      atualizarTipo(form);               // primeiro o tipo: ele filtra as categorias do select
      form.id_categoria.value = t.id_categoria;
      form.descricao.value = t.descricao || '';
      form.valor.value = t.valor;
      form.data_transacao.value = t.data_transacao;
    }
  }

  if (e.target.matches('[data-excluir]')) {
    const nome = linha.querySelector('.descricao, .nome')?.textContent.trim() || '';
    const ok = await confirmar({
      titulo: ehCategoria ? 'Excluir categoria?' : 'Excluir transação?',
      texto: (nome ? '"' + nome + '" será excluída. ' : '') + 'Essa ação não pode ser desfeita.',
      acao: 'Excluir',
    });
    if (!ok) return;
    const arquivoApi = ehCategoria ? 'categorias.php' : 'transacoes.php';
    const resposta = await fetch(API + arquivoApi + '?id=' + linha.dataset.id, { method: 'DELETE' });
    if (!resposta.ok) {
      // ex.: categoria que ainda tem transações
      const resultado = await resposta.json();
      return notificar({ titulo: 'Não foi possível excluir', descricao: resultado.erro, status: 'erro' });
    }
    if (!ehCategoria) {
      transacoes = transacoes.filter(t => t.id != linha.dataset.id);   // tira da lista e refaz os totais
      mostrarTotais();
      carregarDashboard();
    }
    // o foco vai para a linha vizinha (ou para o botão "Nova…"), senão voltaria ao topo da página
    const vizinha = [linha.nextElementSibling, linha.previousElementSibling].find(v => v?.matches('[data-id]'));
    const alvo = vizinha?.querySelector('[data-excluir]') || document.querySelector('[data-abrir]');
    linha.remove();
    alvo?.focus();
    notificar({ titulo: ehCategoria ? 'Categoria excluída' : 'Transação excluída', descricao: nome });
  }
});

// ---------- contadores das listas ----------
// Contam sozinhos as linhas [data-id] de cada lista: quem preenche ou apaga linhas
// não precisa atualizar o número.
function ligarContador(idContador, idLista, texto) {
  const contador = document.getElementById(idContador);
  const lista = document.getElementById(idLista);
  if (!contador || !lista) return;
  const contar = () => {
    const n = lista.hidden ? 0 : lista.querySelectorAll('[data-id]:not([hidden])').length;
    const novo = texto(n);
    if (contador.textContent !== novo) contador.textContent = novo;
  };
  contar();
  new MutationObserver(contar).observe(lista, { childList: true, subtree: true, attributes: true, attributeFilter: ['hidden'] });
}
ligarContador('qtd-transacoes', 'lista-transacoes', n => n === 0 ? 'Nenhuma transação' : n === 1 ? '1 transação' : n + ' transações');
ligarContador('qtd-saidas', 'lista-saidas', String);
ligarContador('qtd-entradas', 'lista-entradas', String);

// ---------- envio dos formulários dos modais ----------
// (o formulário da carteira tem o próprio envio, lá embaixo na parte do Radar e carteira)
document.querySelectorAll('dialog form:not(#form-carteira)').forEach(form => {
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = form.querySelector('.msg');
    const dados = Object.fromEntries(new FormData(form));

    if (form.getAttribute('id') === 'form-transacao') {
      if (!dados.id_categoria) return mostrarMsg(msg, 'Escolha uma categoria.');
      if (!(Number(dados.valor) > 0)) return mostrarMsg(msg, 'Informe um valor maior que zero.');
      if (!dados.data_transacao) return mostrarMsg(msg, 'Informe a data.');
    }
    if (form.getAttribute('id') === 'form-categoria') {
      if (!dados.nome.trim()) return mostrarMsg(msg, 'Dê um nome para a categoria.');
    }

    // com id é edição (PUT); sem id é registro novo (POST)
    const ehCategoria = form.getAttribute('id') === 'form-categoria';
    const resposta = await fetch(API + (ehCategoria ? 'categorias.php' : 'transacoes.php'), {
      method: dados.id ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados),
    });
    const resultado = await resposta.json();
    if (!resposta.ok) return mostrarMsg(msg, resultado.erro);   // erro: o modal continua aberto
    // recarrega o que está na tela
    if (ehCategoria) {
      carregarCategorias();
    } else {
      carregarTransacoes();
      carregarDashboard();
    }
    form.closest('dialog').close();
    const oQue = form.getAttribute('id') === 'form-categoria' ? 'Categoria' : 'Transação';
    notificar({ titulo: oQue + (dados.id ? ' atualizada' : ' criada'), descricao: dados.nome || dados.descricao || '' });
  });
});

// ---------- abas de tipo (transacoes.php) ----------
document.querySelectorAll('.abas button').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.parentElement.querySelectorAll('button').forEach(b => {
      b.classList.toggle('ativo', b === btn);
      b.setAttribute('aria-pressed', String(b === btn));
    });
    carregarTransacoes();   // recarrega a lista (ela lê qual aba está ativa)
  });
});

// ---------- mostrar senha (login e cadastro) ----------
document.querySelectorAll('[data-mostrar-senha]').forEach(caixa => {
  const sincronizar = () => {
    caixa.closest('form').querySelectorAll('[name="senha"], [name="confirmar"]').forEach(campo => {
      campo.type = caixa.checked ? 'text' : 'password';
    });
  };
  caixa.addEventListener('change', sincronizar);
  window.addEventListener('pageshow', sincronizar); // o navegador pode restaurar a caixa marcada ao voltar
});

// ---------- login ----------
const formLogin = document.getElementById('form-login');
if (formLogin) {
  formLogin.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('msg-login');
    const dados = Object.fromEntries(new FormData(formLogin));
    if (!dados.email || !dados.senha) return mostrarMsg(msg, 'Preencha e-mail e senha.');
    if (!formLogin.querySelector('[name="email"]').checkValidity()) return mostrarMsg(msg, 'Informe um e-mail válido.');
    // manda e-mail e senha para o PHP; se der certo, vai para o dashboard
    const resposta = await fetch(API + 'login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados),
    });
    const resultado = await resposta.json();
    if (!resposta.ok) return mostrarMsg(msg, resultado.erro);
    window.location = 'logado/dashboard.php';
  });
}

// ---------- cadastro ----------
const formCadastro = document.getElementById('form-cadastro');
if (formCadastro) {
  formCadastro.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = document.getElementById('msg-cadastro');
    const dados = Object.fromEntries(new FormData(formCadastro));
    if (!dados.nome.trim() || !dados.email || !dados.senha) return mostrarMsg(msg, 'Preencha todos os campos.');
    if (!formCadastro.querySelector('[name="email"]').checkValidity()) return mostrarMsg(msg, 'Informe um e-mail válido.');
    if (dados.senha.length < 6) return mostrarMsg(msg, 'A senha precisa ter pelo menos 6 caracteres.');
    if (dados.senha !== dados.confirmar) return mostrarMsg(msg, 'As senhas não conferem.');
    delete dados.confirmar;
    // manda os dados para o PHP e espera a resposta
    const resposta = await fetch(API + 'cadastro.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados),
    });
    const resultado = await resposta.json();
    if (!resposta.ok) return mostrarMsg(msg, resultado.erro);
    mostrarMsg(msg, 'Conta criada. Agora é só entrar.', 'ok');
    setTimeout(() => { window.location = 'index.html'; }, 1200);
  });
}

// ==========================================================
// Gráfico de entradas e saídas (dashboard)
// Versão em SVG + JS puro do "analytics chart" do Cult UI, com duas séries.
//
// INTEGRAR: depois de buscar os totais do mês, chamar
//   desenharGrafico([{ rotulo: 'Sem 1', entradas: 2500, saidas: 1200 }, ...])
// Um item por semana (4 ou 5), valores em reais (número, não texto).
// Enquanto não há API, os dados vêm do atributo data-semanas de #grafico.
// ==========================================================
const SERIES = [
  { chave: 'entradas', nome: 'Entradas', classe: 'serie-entrada', cor: 'var(--serie-entrada)' },
  { chave: 'saidas', nome: 'Saídas', classe: 'serie-saida', cor: 'var(--serie-saida)' },
];
const REAIS = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
const COMPACTO = new Intl.NumberFormat('pt-BR', { notation: 'compact', maximumFractionDigits: 1 });
const SVG_NS = 'http://www.w3.org/2000/svg';

function svg(nome, atributos) {
  const el = document.createElementNS(SVG_NS, nome);
  for (const chave in atributos) el.setAttribute(chave, atributos[chave]);
  return el;
}

// Escolhe um passo "redondo" (1, 2, 2,5 ou 5 × 10ⁿ) para as linhas de grade.
function passoRedondo(maximo, divisoes) {
  const bruto = maximo / divisoes;
  const potencia = Math.pow(10, Math.floor(Math.log10(bruto)));
  return [1, 2, 2.5, 5, 10].map(m => m * potencia).find(p => p >= bruto);
}

function desenharGrafico(semanas, animar = true) {
  const el = document.getElementById('grafico');
  if (!el) return;
  semanas = (semanas || []).map(s => ({ rotulo: s.rotulo, entradas: Number(s.entradas) || 0, saidas: Number(s.saidas) || 0 }));
  el._semanas = semanas;
  el._largura = el.clientWidth;
  el.classList.remove('ativo');
  el.replaceChildren();
  // limpa o que o desenho anterior deixou (importa quando o mês novo não tem dados)
  el.onpointermove = el.onpointerdown = el.onpointerleave = el.onfocus = el.onblur = el.onkeydown = null;
  el._esconder = null;
  el.removeAttribute('tabindex');
  el.removeAttribute('role');
  el.removeAttribute('aria-label');

  const maximo = Math.max(0, ...semanas.flatMap(s => [s.entradas, s.saidas]));
  if (!maximo) {
    const vazio = document.createElement('div');
    vazio.className = 'vazio';
    vazio.textContent = 'Nenhuma movimentação neste mês.';
    el.append(vazio);
    return;
  }

  const L = el.clientWidth || 600, A = el.clientHeight || 260;
  el._largura = L;
  const borda = { topo: 12, dir: 16, base: 28, esq: 46 };
  const areaL = L - borda.esq - borda.dir, areaA = A - borda.topo - borda.base;
  const passo = passoRedondo(maximo, 4);
  const linhas = Math.ceil(maximo / passo);
  const teto = passo * linhas;
  const x = i => borda.esq + (semanas.length > 1 ? i / (semanas.length - 1) : 0.5) * areaL;
  const y = v => borda.topo + areaA - (v / teto) * areaA;

  const raiz = svg('svg', { viewBox: `0 0 ${L} ${A}`, 'aria-hidden': 'true' });

  // grade + rótulos do eixo
  for (let i = 0; i <= linhas; i++) {
    const yy = y(i * passo);
    raiz.append(svg('line', { class: i ? 'grade' : 'grade base', x1: borda.esq, x2: L - borda.dir, y1: yy, y2: yy }));
    const t = svg('text', { x: borda.esq - 10, y: yy + 4, 'text-anchor': 'end' });
    t.textContent = COMPACTO.format(i * passo);
    raiz.append(t);
  }
  semanas.forEach((s, i) => {
    const ancora = i === 0 ? 'start' : i === semanas.length - 1 ? 'end' : 'middle';
    const t = svg('text', { x: x(i), y: A - 6, 'text-anchor': semanas.length > 1 ? ancora : 'middle' });
    t.textContent = s.rotulo;
    raiz.append(t);
  });

  const guia = svg('line', { class: 'guia', y1: borda.topo, y2: borda.topo + areaA });
  raiz.append(guia);

  // linhas e pontos
  const linhasSvg = [], pontos = [];
  SERIES.forEach(serie => {
    const d = semanas.map((s, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${y(s[serie.chave]).toFixed(1)}`).join(' ');
    const caminho = svg('path', { class: 'linha ' + serie.classe, d });
    raiz.append(caminho);
    linhasSvg.push(caminho);
  });
  SERIES.forEach(serie => {
    semanas.forEach((s, i) => {
      const ponto = svg('circle', { class: 'ponto ' + serie.classe, cx: x(i), cy: y(s[serie.chave]), r: 4 });
      ponto.dataset.indice = i;
      raiz.append(ponto);
      pontos.push(ponto);
    });
  });
  el.append(raiz);

  // camada do tooltip: um marcador por série + um cartão
  const marcas = SERIES.map(serie => {
    const m = document.createElement('div');
    m.className = 'grafico-marca';
    m.style.setProperty('--cor', serie.cor);
    el.append(m);
    return m;
  });
  const cartao = document.createElement('div');
  cartao.className = 'grafico-cartao';
  el.append(cartao);

  // tabela equivalente para leitores de tela
  const tabela = document.createElement('table');
  tabela.className = 'so-leitor';
  tabela.innerHTML = '<caption>Entradas e saídas por semana</caption><thead><tr><th>Semana</th><th>Entradas</th><th>Saídas</th></tr></thead>';
  const corpo = tabela.createTBody();
  semanas.forEach(s => {
    const tr = corpo.insertRow();
    [s.rotulo, REAIS.format(s.entradas), REAIS.format(s.saidas)].forEach(txt => { tr.insertCell().textContent = txt; });
  });
  el.append(tabela);

  let ativo = -1;
  function mostrar(i) {
    if (i === ativo) return;
    ativo = i;
    pontos.forEach(p => p.classList.toggle('oculto', Number(p.dataset.indice) === i));
    if (i < 0) { el.classList.remove('ativo'); return; }

    const s = semanas[i], px = x(i);
    const ys = SERIES.map(serie => y(s[serie.chave]));
    guia.setAttribute('x1', px);
    guia.setAttribute('x2', px);
    marcas.forEach((m, k) => { m.style.left = px + 'px'; m.style.top = ys[k] + 'px'; });

    cartao.replaceChildren();
    const quando = document.createElement('div');
    quando.className = 'quando';
    quando.textContent = s.rotulo;
    cartao.append(quando);
    SERIES.forEach(serie => {
      const item = document.createElement('div');
      item.className = 'item';
      item.style.setProperty('--cor', serie.cor);
      const chip = document.createElement('span');
      chip.className = 'chip';
      chip.textContent = serie.nome;
      const quanto = document.createElement('span');
      quanto.className = 'quanto';
      quanto.textContent = REAIS.format(s[serie.chave]);
      item.append(chip, quanto);
      cartao.append(item);
    });

    // cartão acima do ponto mais alto; se não couber, ao lado da guia, sem sair da área do gráfico
    const cl = cartao.offsetWidth, ca = cartao.offsetHeight;
    const acima = Math.min(...ys) - 15 - ca;
    const cabe = acima >= -8;
    const topo = cabe ? acima : Math.max(0, Math.min(Math.max(...ys) + 15, A - borda.base - ca));
    const esquerda = cabe ? (px < L / 2 ? px - 13 : px + 13 - cl) : (px < L / 2 ? px + 16 : px - 16 - cl);
    cartao.style.top = topo + 'px';
    cartao.style.left = Math.min(Math.max(esquerda, 0), L - cl) + 'px';
    el.classList.add('ativo');
  }

  function maisProximo(clientX) {
    const px = clientX - el.getBoundingClientRect().left;
    let indice = 0, menor = Infinity;
    semanas.forEach((_, i) => {
      const dist = Math.abs(x(i) - px);
      if (dist < menor) { menor = dist; indice = i; }
    });
    return indice;
  }

  el.onpointermove = e => mostrar(maisProximo(e.clientX));
  el.onpointerdown = e => mostrar(maisProximo(e.clientX));
  // no toque o navegador dispara pointerleave ao levantar o dedo: aí o cartão fica até tocar fora
  el.onpointerleave = e => { if (e.pointerType !== 'touch') mostrar(-1); };
  el._esconder = () => mostrar(-1);
  // teclado: Tab foca o gráfico, setas mudam de semana
  el.tabIndex = 0;
  el.setAttribute('role', 'group');
  el.setAttribute('aria-label', 'Gráfico de entradas e saídas por semana. Use as setas para percorrer as semanas.');
  el.onfocus = () => { if (el.matches(':focus-visible')) mostrar(Math.max(ativo, 0)); };
  el.onblur = () => mostrar(-1);
  el.onkeydown = e => {
    if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
    e.preventDefault();
    const proximo = (ativo < 0 ? 0 : ativo + (e.key === 'ArrowRight' ? 1 : -1));
    mostrar(Math.min(Math.max(proximo, 0), semanas.length - 1));
  };

  // entrada: a grade aparece, as linhas se desenham e os pontos surgem em sequência
  if (animar && !SEM_ANIMACAO) {
    el.classList.add('entrando');
    linhasSvg.forEach(caminho => {
      const comprimento = Math.ceil(caminho.getTotalLength());
      caminho.style.strokeDasharray = comprimento;
      caminho.style.strokeDashoffset = comprimento;
    });
    pontos.forEach(p => { p.style.transitionDelay = (700 + Number(p.dataset.indice) * 80) + 'ms'; });
    requestAnimationFrame(() => requestAnimationFrame(() => {
      el.classList.remove('entrando');
      linhasSvg.forEach(caminho => {
        caminho.style.transition = 'stroke-dashoffset 1000ms cubic-bezier(.23, 1, .32, 1)';
        caminho.style.strokeDashoffset = 0;
      });
      setTimeout(() => {
        pontos.forEach(p => { p.style.transitionDelay = ''; });
        linhasSvg.forEach(caminho => { caminho.style.transition = ''; caminho.style.strokeDasharray = ''; caminho.style.strokeDashoffset = ''; });
      }, 1400);
    }));
  }
}

const grafico = document.getElementById('grafico');
if (grafico) {
  let semanasIniciais = [];
  try { semanasIniciais = JSON.parse(grafico.dataset.semanas || '[]'); } catch (erro) { console.error('data-semanas inválido em #grafico', erro); }
  desenharGrafico(semanasIniciais);
  document.addEventListener('pointerdown', e => { if (!grafico.contains(e.target)) grafico._esconder?.(); });
  // redesenha (sem animação) quando a largura muda
  new ResizeObserver(() => {
    if (grafico.clientWidth !== grafico._largura) desenharGrafico(grafico._semanas, false);
  }).observe(grafico);
}

// ==========================================================
// Categorias (lista em categorias.php e opções dos selects de transação)
// ==========================================================
let categorias = [];   // guarda a lista para preencher o modal na hora de editar

function criarLinhaCategoria(c) {
  const linha = document.createElement('div');
  linha.className = 'categoria-linha';
  linha.dataset.id = c.id;
  linha.innerHTML =
    '<span class="nome"></span>' +
    (c.tipo === 'saida' ? '<span class="num mudo"></span>' : '') +
    '<span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>';
  // textContent (e não innerHTML) para o nome nunca ser lido como HTML
  linha.querySelector('.nome').textContent = c.nome;
  if (c.tipo === 'saida') {
    linha.querySelector('.num').textContent = c.limite_mensal ? REAIS.format(Number(c.limite_mensal)) : 'Sem limite';
  }
  return linha;
}

async function carregarCategorias() {
  const listaSaidas = document.getElementById('lista-saidas');
  const listaEntradas = document.getElementById('lista-entradas');
  const selectModal = document.getElementById('t-categoria');
  const filtro = document.getElementById('filtro-categoria');
  if (!listaSaidas && !selectModal && !filtro) return;   // página que não usa categorias (login, cadastro)

  const resposta = await fetch(API + 'categorias.php');
  categorias = await resposta.json();

  // página de categorias: monta as duas listas
  if (listaSaidas && listaEntradas) {
    listaSaidas.replaceChildren();
    listaEntradas.replaceChildren();
    categorias.forEach(c => {
      (c.tipo === 'saida' ? listaSaidas : listaEntradas).append(criarLinhaCategoria(c));
    });
  }

  // formulário de transação: opções do select (o data-tipo serve para filtrar entrada/saída)
  if (selectModal) {
    selectModal.replaceChildren(new Option('Selecione...', ''));
    categorias.forEach(c => {
      const opcao = new Option(c.nome, c.id);
      opcao.dataset.tipo = c.tipo;
      selectModal.append(opcao);
    });
  }

  // filtro da página de transações
  if (filtro) {
    filtro.replaceChildren(new Option('Todas as categorias', ''));
    categorias.forEach(c => filtro.append(new Option(c.nome, c.id)));
  }
}
carregarCategorias();

// ==========================================================
// Transações (lista em transacoes.php)
// ==========================================================
let transacoes = [];   // lista que está na tela: usada ao editar e para somar os totais

function criarLinhaTransacao(t) {
  const [ano, mes, dia] = t.data_transacao.split('-');
  const ehEntrada = t.tipo === 'entrada';

  const linha = document.createElement('div');
  linha.className = 'lista-linha';
  linha.dataset.id = t.id;
  linha.innerHTML =
    '<span class="rotulo num"></span><span class="descricao"></span><span class="cat mudo"></span><span class="valor num"></span>' +
    '<span class="acoes"><button class="btn-texto" data-editar>Editar</button><button class="btn-texto perigo" data-excluir>Excluir</button></span>';
  // textContent (e não innerHTML) para o texto nunca ser lido como HTML
  linha.querySelector('.rotulo').textContent = dia + ' ' + MESES[Number(mes) - 1].slice(0, 3);
  linha.querySelector('.descricao').textContent = t.descricao || '';
  linha.querySelector('.cat').textContent = t.categoria;
  const valor = linha.querySelector('.valor');
  valor.classList.add(ehEntrada ? 'entrada' : 'saida');
  valor.textContent = (ehEntrada ? '+ ' : '− ') + REAIS.format(Number(t.valor));
  return linha;
}

// soma as transações que estão na tela e mostra Entrou / Saiu / Saldo
function mostrarTotais() {
  const lista = document.getElementById('lista-transacoes');
  if (!lista) return;   // só existe na página de transações

  let entradas = 0, saidas = 0;
  transacoes.forEach(t => {
    if (t.tipo === 'entrada') entradas += Number(t.valor);
    else saidas += Number(t.valor);
  });
  document.getElementById('total-entradas').textContent = REAIS.format(entradas);
  document.getElementById('total-saidas').textContent = REAIS.format(saidas);
  document.getElementById('saldo').textContent = REAIS.format(entradas - saidas);

  // sem transações: esconde a lista e mostra a mensagem "Nenhuma transação"
  lista.hidden = transacoes.length === 0;
  document.getElementById('sem-transacoes').hidden = transacoes.length > 0;
}

async function carregarTransacoes() {
  const lista = document.getElementById('lista-transacoes');
  if (!lista) return;   // não é a página de transações

  // lê os filtros da tela: mês, aba (todas/entradas/saídas) e categoria
  const tipo = document.querySelector('.abas .ativo')?.dataset.tipo || '';
  const categoria = document.getElementById('filtro-categoria').value;
  const url = API + 'transacoes.php?mes=' + mesParaApi() + '&tipo=' + tipo + '&categoria=' + categoria;

  const resposta = await fetch(url);
  transacoes = await resposta.json();

  lista.replaceChildren(...transacoes.map(criarLinhaTransacao));
  mostrarTotais();
}
document.getElementById('filtro-categoria')?.addEventListener('change', carregarTransacoes);

// ==========================================================
// Dashboard (só roda em dashboard.php): resumo do mês vindo de api/dashboard.php
// ==========================================================
const REAIS_INTEIRO = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 0, maximumFractionDigits: 0 });
const NUMERO_INTEIRO = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 });

function porcentagem(parte, total) {
  return total > 0 ? Math.round(parte / total * 100) : 0;
}

// uma barra da lista "Limites por categoria"
function criarBarraLimite(c) {
  const gasto = Number(c.gasto);
  const limite = Number(c.limite_mensal);
  const usado = limite > 0 ? gasto / limite : (gasto > 0 ? 1 : 0);   // 1 = 100% do limite
  const estourou = gasto > limite;

  const barra = document.createElement('div');
  barra.className = 'orcamento' + (estourou ? ' estourou' : usado >= 0.8 ? ' atencao' : '');
  barra.setAttribute('role', 'meter');
  barra.setAttribute('aria-label', c.nome);
  barra.setAttribute('aria-valuemin', 0);
  barra.setAttribute('aria-valuemax', limite);
  barra.setAttribute('aria-valuenow', Math.min(gasto, limite));
  barra.setAttribute('aria-valuetext', REAIS_INTEIRO.format(gasto) + ' de ' + REAIS_INTEIRO.format(limite) + (estourou ? ', acima do limite' : ''));
  barra.innerHTML = '<div class="orcamento-topo"><span></span><span class="num"></span></div><div class="trilho"><div></div></div>';
  barra.querySelector('.orcamento-topo span').textContent = c.nome;
  barra.querySelector('.num').textContent = NUMERO_INTEIRO.format(gasto) + ' / ' + NUMERO_INTEIRO.format(limite);
  barra.querySelector('.trilho div').style.setProperty('--p', Math.min(usado, 1) * 100 + '%');
  if (estourou) {
    const nota = document.createElement('span');
    nota.className = 'orcamento-nota';
    nota.textContent = REAIS.format(gasto - limite) + ' acima do limite';
    barra.append(nota);
  }
  return barra;
}

// mensagem de "lista vazia"
function criarVazio(texto) {
  const vazio = document.createElement('div');
  vazio.className = 'vazio';
  vazio.textContent = texto;
  return vazio;
}

async function carregarDashboard() {
  if (!document.getElementById('lista-gastos')) return;   // não é a página do dashboard

  const resposta = await fetch(API + 'dashboard.php?mes=' + mesParaApi());
  const d = await resposta.json();
  const entradas = Number(d.entradas);
  const saidas = Number(d.saidas);
  const saldo = entradas - saidas;

  // saldo grande, com os centavos em tamanho menor
  const [inteiro, centavos] = REAIS.format(saldo).split(',');
  const elSaldo = document.getElementById('saldo');
  elSaldo.textContent = inteiro;
  const spanCentavos = document.createElement('span');
  spanCentavos.className = 'centavos';
  spanCentavos.textContent = ',' + centavos;
  elSaldo.append(spanCentavos);

  // frase embaixo do saldo
  let frase = 'Nenhuma movimentação neste mês.';
  if (entradas === 0 && saidas > 0) frase = 'Neste mês só houve saídas.';
  else if (saldo >= 0 && entradas > 0) frase = 'Sobrou ' + porcentagem(saldo, entradas) + '% do que entrou neste mês.';
  else if (saldo < 0) frase = 'Gastou ' + porcentagem(-saldo, entradas) + '% a mais do que entrou neste mês.';
  document.getElementById('saldo-frase').textContent = frase;

  document.getElementById('total-entradas').textContent = REAIS.format(entradas);
  document.getElementById('total-saidas').textContent = REAIS.format(saidas);

  // categorias de saída que tiveram gasto (a API já manda da maior para a menor)
  const gastos = d.categorias.filter(c => Number(c.gasto) > 0);

  // maior gasto
  document.getElementById('maior-categoria').textContent = gastos.length ? gastos[0].nome : '—';
  document.getElementById('maior-categoria-parte').textContent = gastos.length ? porcentagem(Number(gastos[0].gasto), saidas) + '% das saídas' : '';

  // gráfico de entradas e saídas por semana
  desenharGrafico(d.semanas);

  // gastos por categoria
  const listaGastos = document.getElementById('lista-gastos');
  listaGastos.replaceChildren(...gastos.map(c => {
    const parte = porcentagem(Number(c.gasto), saidas);
    const linha = document.createElement('div');
    linha.className = 'barra-linha';
    linha.setAttribute('role', 'listitem');
    linha.style.setProperty('--p', parte + '%');
    linha.innerHTML = '<span class="nome"></span><span class="num valor-gasto"></span><span class="num parte"></span>';
    linha.querySelector('.nome').textContent = c.nome;
    linha.querySelector('.valor-gasto').textContent = REAIS.format(Number(c.gasto));
    linha.querySelector('.parte').textContent = parte + '%';
    return linha;
  }));
  listaGastos.hidden = gastos.length === 0;
  document.getElementById('sem-gastos').hidden = gastos.length > 0;

  // limites: só as categorias de saída que têm limite
  const comLimite = d.categorias.filter(c => c.limite_mensal !== null);
  const listaLimites = document.getElementById('lista-limites');
  listaLimites.replaceChildren(...comLimite.map(criarBarraLimite));
  if (comLimite.length === 0) listaLimites.append(criarVazio('Nenhuma categoria de saída com limite.'));

  // últimas transações (o botão Editar procura nesta lista)
  transacoes = d.ultimas;
  const listaUltimas = document.getElementById('ultimas-transacoes');
  listaUltimas.replaceChildren(...d.ultimas.map(criarLinhaTransacao));
  if (d.ultimas.length === 0) listaUltimas.append(criarVazio('Nenhuma transação neste mês.'));
}

// ==========================================================
// Radar e carteira (só roda em radar.php)
// Radar: lista fixa de ações vinda de api/radar.php (só consulta).
// Carteira: ações do usuário, com CRUD em api/carteira.php.
// Quem fala com a brapi e a Finnhub é o PHP; o navegador só conversa com a nossa API
// (assim as chaves das APIs nunca aparecem aqui).
// ==========================================================

// moeda de cada mercado: B3 em reais, EUA em dólares
const MOEDA_DO_MERCADO = { B3: 'BRL', US: 'USD' };

// 1234.5 + 'B3' -> "R$ 1.234,50"   |   1234.5 + 'US' -> "US$ 1.234,50"
function formatarDinheiro(valor, mercado) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: MOEDA_DO_MERCADO[mercado] }).format(valor);
}

// -1.96 -> "1,96%" (sem o sinal: a seta e o + / − são colocados por mostrarAltaBaixa)
function formatarPorcento(numero) {
  return Math.abs(numero).toFixed(2).replace('.', ',') + '%';
}

// escreve o texto com seta e cor: ▲ verde quando subiu, ▼ vermelho quando caiu
function mostrarAltaBaixa(el, numero, texto) {
  el.classList.remove('alta', 'baixa');
  if (numero > 0) {
    el.textContent = '▲ +' + texto;
    el.classList.add('alta');
  } else if (numero < 0) {
    el.textContent = '▼ −' + texto;
    el.classList.add('baixa');
  } else {
    el.textContent = texto;
  }
}

// "2026-11-11 16:00:00" -> "Atualizado às 16:00" (hoje) ou "Atualizado às 16:00 de 10/11" (outro dia)
function formatarAtualizacao(dataHora) {
  const [data, hora] = dataHora.split(' ');
  const [ano, mes, dia] = data.split('-');
  const hoje = new Date();
  const ehHoje = Number(ano) === hoje.getFullYear() && Number(mes) === hoje.getMonth() + 1 && Number(dia) === hoje.getDate();
  return 'Atualizado às ' + hora.slice(0, 5) + (ehHoje ? '' : ' de ' + dia + '/' + mes);
}

// ---------- radar ----------
function criarLinhaRadar(a) {
  const linha = document.createElement('div');
  linha.className = 'radar-linha';
  linha.dataset.ticker = a.ticker;   // usado pela busca
  linha.innerHTML =
    '<span class="nome"></span><span class="preco num"></span><span class="variacao num"></span><span class="quando mudo"></span>' +
    '<span class="acoes"><a class="btn-texto">Adicionar à carteira</a></span>';
  // textContent (e não innerHTML) para o texto nunca ser lido como HTML
  linha.querySelector('.nome').textContent = a.ticker;

  // o link leva para a carteira, que já abre o modal com a ação e o mercado preenchidos
  linha.querySelector('a').href = 'carteira.php?ticker=' + encodeURIComponent(a.ticker) + '&mercado=' + a.mercado;

  // ação sem nenhuma cotação guardada (a API não respondeu nenhuma vez)
  const preco = linha.querySelector('.preco');
  if (a.preco === null) {
    preco.textContent = 'Sem cotação';
    preco.classList.add('mudo');
    return linha;
  }

  preco.textContent = formatarDinheiro(Number(a.preco), a.mercado);
  const variacao = Number(a.variacao_percent);
  mostrarAltaBaixa(linha.querySelector('.variacao'), variacao, formatarPorcento(variacao));

  // quando foi atualizada. Se a API falhou, o PHP manda o último valor guardado
  // com desatualizada = true, e a linha ganha um selo avisando.
  const quando = linha.querySelector('.quando');
  quando.textContent = formatarAtualizacao(a.atualizado_em);
  if (a.desatualizada) {
    const selo = document.createElement('span');
    selo.className = 'selo';
    selo.title = 'A API de cotações não respondeu. Este é o último valor guardado.';
    selo.textContent = 'Desatualizada';
    quando.append(' ', selo);
  }
  return linha;
}

async function carregarRadar() {
  const listaB3 = document.getElementById('radar-b3');
  if (!listaB3) return;   // não é a página do radar

  // o PHP pode precisar consultar as APIs: leva alguns segundos
  const resposta = await fetch(API + 'radar.php');
  const radar = await resposta.json();
  document.getElementById('carregando-radar').hidden = true;
  if (!resposta.ok) {
    return notificar({ titulo: 'Não foi possível carregar o radar', descricao: radar.erro, status: 'erro' });
  }

  listaB3.replaceChildren(...radar.B3.map(criarLinhaRadar));
  document.getElementById('radar-us').replaceChildren(...radar.US.map(criarLinhaRadar));
  filtrarRadar();   // se a pessoa já digitou algo na busca enquanto carregava

  // um aviso só, se alguma cotação não pôde ser atualizada
  const todas = radar.B3.concat(radar.US);
  if (todas.some(a => a.desatualizada || a.preco === null)) {
    notificar({ titulo: 'Algumas cotações não foram atualizadas', descricao: 'A API de cotações não respondeu agora. Mostramos o último valor guardado.', status: 'info' });
  }
}

// busca do radar: esconde as linhas cujo código não tem o texto digitado (só no navegador)
function filtrarRadar() {
  const busca = document.getElementById('busca-radar');
  if (!busca) return;
  const texto = busca.value.trim().toUpperCase();

  let achou = 0;
  ['b3', 'us'].forEach(mercado => {
    let achouNoMercado = 0;
    document.querySelectorAll('#radar-' + mercado + ' .radar-linha').forEach(linha => {
      linha.hidden = !linha.dataset.ticker.includes(texto);
      if (!linha.hidden) achouNoMercado++;
    });
    // mercado sem nenhuma ação encontrada: esconde o bloco inteiro (título e cabeçalho)
    document.getElementById('bloco-' + mercado).hidden = achouNoMercado === 0;
    achou += achouNoMercado;
  });

  // nenhuma ação nos dois mercados: mostra a mensagem
  const semResultado = document.getElementById('radar-sem-resultado');
  semResultado.hidden = achou > 0;
  semResultado.textContent = 'Nenhuma ação encontrada para "' + busca.value.trim() + '".';
}
document.getElementById('busca-radar')?.addEventListener('input', filtrarRadar);

// ---------- carteira ----------
let carteira = [];   // posições que estão na tela: usadas ao editar e para somar os totais

function criarLinhaCarteira(p) {
  const linha = document.createElement('div');
  linha.className = 'carteira-linha';
  linha.dataset.id = p.id;
  // data-rotulo: no celular o cabeçalho some e cada valor mostra o próprio nome (ver style.css)
  linha.innerHTML =
    '<span class="nome"><span class="ticker"></span> <span class="cat"></span></span>' +
    '<span class="num" data-rotulo="Quantidade"></span><span class="num" data-rotulo="Preço médio"></span>' +
    '<span class="num" data-rotulo="Preço atual"></span><span class="num" data-rotulo="Valor atual"></span>' +
    '<span class="num resultado" data-rotulo="Resultado"></span>' +
    '<span class="acoes"><button class="btn-texto" data-editar-posicao>Editar</button><button class="btn-texto perigo" data-excluir-posicao>Excluir</button></span>';
  linha.querySelector('.ticker').textContent = p.ticker;
  linha.querySelector('.cat').textContent = p.mercado === 'B3' ? 'B3' : 'EUA';

  const celulas = linha.querySelectorAll('[data-rotulo]');
  celulas[0].textContent = NUMERO_INTEIRO.format(p.quantidade);
  celulas[1].textContent = formatarDinheiro(p.preco_medio, p.mercado);

  // sem cotação: não dá para calcular o valor atual nem o resultado
  if (p.preco === null) {
    celulas[2].textContent = 'Sem cotação';
    celulas[3].textContent = '—';
    celulas[4].textContent = '—';
    return linha;
  }

  // valor atual = quantidade × preço de hoje; investido = quantidade × preço médio pago
  const valorAtual = p.quantidade * p.preco;
  const investido = p.quantidade * p.preco_medio;
  const ganho = valorAtual - investido;
  const ganhoPorcento = ganho / investido * 100;

  celulas[2].textContent = formatarDinheiro(p.preco, p.mercado);
  celulas[3].textContent = formatarDinheiro(valorAtual, p.mercado);
  mostrarAltaBaixa(celulas[4], ganho, formatarDinheiro(Math.abs(ganho), p.mercado) + ' (' + formatarPorcento(ganhoPorcento) + ')');
  return linha;
}

// totais de cada moeda. R$ e US$ ficam em caixas separadas: nunca são somados.
function mostrarTotaisCarteira() {
  ['B3', 'US'].forEach(mercado => {
    const caixa = document.getElementById(mercado === 'B3' ? 'totais-b3' : 'totais-us');
    const posicoes = carteira.filter(p => p.mercado === mercado);
    caixa.hidden = posicoes.length === 0;   // sem ações desse mercado: esconde a caixa
    if (posicoes.length === 0) return;

    let investido = 0, valorAtual = 0, faltaCotacao = false;
    posicoes.forEach(p => {
      investido += p.quantidade * p.preco_medio;
      if (p.preco === null) faltaCotacao = true;
      else valorAtual += p.quantidade * p.preco;
    });
    caixa.querySelector('.investido').textContent = formatarDinheiro(investido, mercado);

    // se alguma ação está sem cotação, o total ficaria errado: mostra "—"
    const elAtual = caixa.querySelector('.atual');
    const elResultado = caixa.querySelector('.resultado');
    if (faltaCotacao) {
      elAtual.textContent = '—';
      elResultado.classList.remove('alta', 'baixa');
      elResultado.textContent = '—';
      return;
    }
    const ganho = valorAtual - investido;
    elAtual.textContent = formatarDinheiro(valorAtual, mercado);
    mostrarAltaBaixa(elResultado, ganho, formatarDinheiro(Math.abs(ganho), mercado) + ' (' + formatarPorcento(ganho / investido * 100) + ')');
  });

  // carteira vazia: esconde a lista e mostra a mensagem
  document.getElementById('lista-carteira').hidden = carteira.length === 0;
  document.getElementById('sem-carteira').hidden = carteira.length > 0;
}

async function carregarCarteira() {
  const lista = document.getElementById('lista-carteira');
  if (!lista) return;   // não é a página do radar

  const resposta = await fetch(API + 'carteira.php');
  const posicoes = await resposta.json();
  if (!resposta.ok) {
    return notificar({ titulo: 'Não foi possível carregar a carteira', descricao: posicoes.erro, status: 'erro' });
  }

  // o PHP manda os números como texto ("38.50"): converte uma vez aqui para fazer as contas
  carteira = posicoes.map(p => ({
    ...p,
    quantidade: Number(p.quantidade),
    preco_medio: Number(p.preco_medio),
    preco: p.preco === null ? null : Number(p.preco),
  }));

  lista.replaceChildren(...carteira.map(criarLinhaCarteira));
  mostrarTotaisCarteira();
}
ligarContador('qtd-carteira', 'lista-carteira', String);

// cada página carrega só o que é dela (as duas funções não fazem nada na página errada)
carregarRadar();
carregarCarteira().then(abrirCarteiraPelaUrl);

// carteira.php?ticker=PETR4&mercado=B3 (vindo do botão do radar): já abre o modal com a ação.
// Se a pessoa já tem essa ação, abre o Editar dela (em vez de dar o erro "você já tem").
function abrirCarteiraPelaUrl() {
  if (!formCarteira) return;   // não é a página da carteira
  const parametros = new URLSearchParams(location.search);
  const ticker = (parametros.get('ticker') || '').trim().toUpperCase();
  const mercado = parametros.get('mercado');
  if (!ticker || (mercado !== 'B3' && mercado !== 'US')) return;   // sem parâmetros: só mostra a carteira

  // tira os parâmetros do endereço: recarregar a página não abre o modal de novo
  history.replaceState(null, '', location.pathname);

  const jaTem = carteira.find(p => p.ticker === ticker);   // a tabela só deixa um ticker por usuário
  abrirCarteira(jaTem || { ticker, mercado });
}

// ---------- modal da carteira ----------
const modalCarteira = document.getElementById('modal-carteira');
const formCarteira = document.getElementById('form-carteira');

// abre o modal. dados pode ser:
//   {}                  -> botão "Adicionar à carteira" da seção (tudo em branco)
//   { ticker, mercado } -> botão de uma linha do radar (ação e mercado já preenchidos)
//   uma posição inteira -> botão Editar (com id: só muda quantidade e preço médio)
function abrirCarteira(dados = {}) {
  formCarteira.reset();
  mostrarMsg(document.getElementById('msg-carteira'), '');

  formCarteira.querySelector('[name="id"]').value = dados.id || '';
  formCarteira.ticker.value = dados.ticker || '';
  formCarteira.mercado.value = dados.mercado || 'B3';
  formCarteira.quantidade.value = dados.quantidade || '';
  formCarteira.preco_medio.value = dados.preco_medio || '';

  // ação já escolhida (radar ou edição): trava a ação e o mercado
  const acaoEscolhida = Boolean(dados.ticker);
  formCarteira.ticker.readOnly = acaoEscolhida;
  formCarteira.mercado.disabled = acaoEscolhida;

  document.getElementById('titulo-modal-carteira').textContent = dados.id ? 'Editar ' + dados.ticker : 'Adicionar à carteira';
  modalCarteira.showModal();
  (acaoEscolhida ? formCarteira.quantidade : formCarteira.ticker).focus();
}

document.getElementById('btn-nova-posicao')?.addEventListener('click', () => abrirCarteira());

// enviar o modal: POST (novo) ou PUT (editar) para api/carteira.php
formCarteira?.addEventListener('submit', async e => {
  e.preventDefault();
  const msg = document.getElementById('msg-carteira');
  // lê campo por campo (o select travado não entraria num FormData)
  const dados = {
    id: formCarteira.querySelector('[name="id"]').value,
    ticker: formCarteira.ticker.value.trim().toUpperCase(),
    mercado: formCarteira.mercado.value,
    quantidade: formCarteira.quantidade.value,
    preco_medio: formCarteira.preco_medio.value,
  };

  if (!dados.ticker) return mostrarMsg(msg, 'Informe o código da ação.');
  const quantidade = Number(dados.quantidade);
  if (!Number.isInteger(quantidade) || quantidade <= 0) return mostrarMsg(msg, 'Informe a quantidade: um número inteiro maior que zero.');
  if (!(Number(dados.preco_medio) > 0)) return mostrarMsg(msg, 'Informe o preço médio pago (um valor maior que zero).');

  // ação nova: o PHP pode precisar conferir na API se ela existe. Trava o botão enquanto espera.
  const botao = formCarteira.querySelector('[type="submit"]');
  botao.disabled = true;
  botao.textContent = 'Salvando...';
  const resposta = await fetch(API + 'carteira.php', {
    method: dados.id ? 'PUT' : 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados),
  });
  const resultado = await resposta.json();
  botao.disabled = false;
  botao.textContent = 'Salvar';

  if (!resposta.ok) return mostrarMsg(msg, resultado.erro);   // erro: o modal continua aberto
  modalCarteira.close();
  carregarCarteira();
  notificar({ titulo: dados.id ? 'Posição atualizada' : 'Ação adicionada à carteira', descricao: dados.ticker });
});

// botões Editar e Excluir de cada linha da carteira
document.getElementById('lista-carteira')?.addEventListener('click', async e => {
  const linha = e.target.closest('[data-id]');
  if (!linha) return;
  const posicao = carteira.find(p => p.id == linha.dataset.id);

  if (e.target.matches('[data-editar-posicao]')) {
    abrirCarteira(posicao);
  }

  if (e.target.matches('[data-excluir-posicao]')) {
    const ok = await confirmar({
      titulo: 'Excluir ' + posicao.ticker + ' da carteira?',
      texto: 'A quantidade e o preço médio dessa ação serão apagados. Essa ação não pode ser desfeita.',
      acao: 'Excluir',
    });
    if (!ok) return;

    const resposta = await fetch(API + 'carteira.php?id=' + posicao.id, { method: 'DELETE' });
    if (!resposta.ok) {
      const resultado = await resposta.json();
      return notificar({ titulo: 'Não foi possível excluir', descricao: resultado.erro, status: 'erro' });
    }
    // tira da lista e refaz os totais
    carteira = carteira.filter(p => p.id != posicao.id);
    linha.remove();
    mostrarTotaisCarteira();
    document.getElementById('btn-nova-posicao').focus();
    notificar({ titulo: 'Ação excluída da carteira', descricao: posicao.ticker });
  }
});

// começa tudo: escreve o mês na tela e carrega os dados dele
atualizarMes();
