// ==========================================================
// Comportamento da interface (só front, sem backend)
// Tudo marcado com "INTEGRAR" é onde entra o fetch para a API.
//
// Funções prontas para usar na integração:
//   notificar({ titulo, descricao, status })   aviso flutuante (sucesso | erro | info)
//   confirmar({ titulo, texto, acao })         Promise<boolean> com modal de confirmação
//   desenharGrafico(semanas)                   redesenha o gráfico do dashboard
//   mostrarMsg(el, texto, tipo)                mensagem dentro de um formulário
// ==========================================================

// Caminho da API a partir deste arquivo (front-end/js/app.js -> dashboard/api/).
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

function notificar({ titulo, descricao = '', status = 'sucesso', duracao = 5000 }) {
  let pilha = document.querySelector('.avisos');
  if (!pilha) {
    pilha = document.createElement('div');
    pilha.className = 'avisos';
    pilha.setAttribute('aria-label', 'Notificações');
    document.body.append(pilha);
  }

  const aviso = document.createElement('div');
  aviso.className = 'aviso ' + status;
  aviso.setAttribute('role', status === 'erro' ? 'alert' : 'status');
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
    aviso.addEventListener('mouseenter', () => contagem.pause());
    aviso.addEventListener('mouseleave', () => contagem.play());
    aviso.addEventListener('focusin', () => contagem.pause());
    aviso.addEventListener('focusout', () => contagem.play());
  }

  pilha.append(aviso);
  return aviso;
}

// ---------- confirmação (modal com botão de perigo) ----------
// const ok = await confirmar({ titulo: 'Excluir transação?', texto: '...', acao: 'Excluir' });
function confirmar({ titulo, texto = '', acao = 'Excluir' }) {
  let modal = document.getElementById('modal-confirmar');
  if (!modal) {
    modal = document.createElement('dialog');
    modal.id = 'modal-confirmar';
    modal.innerHTML =
      '<div class="modal-topo"><h2></h2><button type="button" class="fechar fechar-m" value="nao" aria-label="Fechar">' + X_MEDIO + '</button></div>' +
      '<p class="modal-texto"></p>' +
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
  document.querySelectorAll('[data-avatar-usuario]').forEach(el => { el.textContent = (nome[0] || '?').toUpperCase(); });
  document.querySelectorAll('[data-primeiro-nome]').forEach(el => { el.textContent = nome.split(/\s+/)[0] || 'Você'; });
}
if (nomeUsuario) {
  atualizarUsuario();
  new MutationObserver(atualizarUsuario).observe(nomeUsuario, { childList: true, characterData: true, subtree: true });
}

// ---------- seletor de mês ----------
let mesAtual = new Date(2026, 10, 1); // nov/2026 (mês começa em 0). Trocar por new Date() depois.

function atualizarMes() {
  const span = document.getElementById('mes-atual');
  const nome = document.getElementById('nome-mes');
  const abrev = MESES[mesAtual.getMonth()].slice(0, 3);
  if (span) span.textContent = abrev[0].toUpperCase() + abrev.slice(1) + ' ' + mesAtual.getFullYear();
  if (nome) nome.textContent = MESES[mesAtual.getMonth()];
  // INTEGRAR: recarregar os dados do mês. Formato sugerido: "2026-11"
  // const mes = mesAtual.toISOString().slice(0, 7);
}

document.querySelectorAll('[data-mes]').forEach(btn => {
  btn.addEventListener('click', () => {
    mesAtual.setMonth(mesAtual.getMonth() + Number(btn.dataset.mes));
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
  if (data) data.value = new Date().toISOString().slice(0, 10);

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
  if (limite && form.id === 'form-categoria') {
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
    // INTEGRAR: buscar o registro pelo id (GET ...?id=) e preencher os campos do form
  }

  if (e.target.matches('[data-excluir]')) {
    const nome = linha.querySelector('.descricao, .nome')?.textContent.trim() || '';
    const ok = await confirmar({
      titulo: ehCategoria ? 'Excluir categoria?' : 'Excluir transação?',
      texto: (nome ? '"' + nome + '" será apagada. ' : '') + 'Essa ação não pode ser desfeita.',
      acao: 'Excluir',
    });
    if (!ok) return;
    // INTEGRAR: fetch DELETE para a API com o id. Só remover a linha se a resposta for ok.
    // Se der erro (ex.: categoria com transações): notificar({ titulo: 'Não foi possível excluir', descricao: mensagemDaApi, status: 'erro' })
    linha.remove();
    notificar({ titulo: ehCategoria ? 'Categoria excluída' : 'Transação excluída', descricao: nome });
  }
});

// ---------- envio dos formulários dos modais ----------
document.querySelectorAll('dialog form').forEach(form => {
  form.addEventListener('submit', e => {
    e.preventDefault();
    const msg = form.querySelector('.msg');
    const dados = Object.fromEntries(new FormData(form));

    if (form.id === 'form-transacao') {
      if (!dados.id_categoria) return mostrarMsg(msg, 'Escolha uma categoria.');
      if (!(Number(dados.valor) > 0)) return mostrarMsg(msg, 'Informe um valor maior que zero.');
      if (!dados.data_transacao) return mostrarMsg(msg, 'Informe a data.');
    }
    if (form.id === 'form-categoria') {
      if (!dados.nome.trim()) return mostrarMsg(msg, 'Dê um nome para a categoria.');
    }

    // INTEGRAR: fetch(url, { method: dados.id ? 'PUT' : 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(dados) })
    // Erro da API -> mostrarMsg(msg, textoDoErro) e manter o modal aberto. Sucesso -> fechar e notificar (abaixo).
    console.log('enviar para a API:', dados);
    form.closest('dialog').close();
    const oQue = form.id === 'form-categoria' ? 'Categoria' : 'Transação';
    notificar({ titulo: oQue + (dados.id ? ' atualizada' : ' criada'), descricao: dados.nome || dados.descricao || '' });
  });
});

// ---------- abas de tipo (transacoes.php) ----------
document.querySelectorAll('.abas button').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('ativo'));
    btn.classList.add('ativo');
    // INTEGRAR: recarregar a lista com ?tipo= btn.dataset.tipo
  });
});

// ---------- mostrar senha (login e cadastro) ----------
document.querySelectorAll('[data-mostrar-senha]').forEach(caixa => {
  caixa.addEventListener('change', () => {
    caixa.closest('form').querySelectorAll('[name="senha"], [name="confirmar"]').forEach(campo => {
      campo.type = caixa.checked ? 'text' : 'password';
    });
  });
});

// ---------- login ----------
const formLogin = document.getElementById('form-login');
if (formLogin) {
  formLogin.addEventListener('submit', e => {
    e.preventDefault();
    const msg = document.getElementById('msg-login');
    const dados = Object.fromEntries(new FormData(formLogin));
    if (!dados.email || !dados.senha) return mostrarMsg(msg, 'Preencha e-mail e senha.');
    // INTEGRAR: POST API + 'login.php' -> se ok: window.location = 'logado/dashboard.php'
    window.location = 'logado/dashboard.php';
  });
}

// ---------- cadastro ----------
const formCadastro = document.getElementById('form-cadastro');
if (formCadastro) {
  formCadastro.addEventListener('submit', e => {
    e.preventDefault();
    const msg = document.getElementById('msg-cadastro');
    const dados = Object.fromEntries(new FormData(formCadastro));
    if (!dados.nome.trim() || !dados.email || !dados.senha) return mostrarMsg(msg, 'Preencha todos os campos.');
    if (dados.senha.length < 6) return mostrarMsg(msg, 'A senha precisa ter pelo menos 6 caracteres.');
    if (dados.senha !== dados.confirmar) return mostrarMsg(msg, 'As senhas não conferem.');
    delete dados.confirmar;
    // INTEGRAR: POST API + 'cadastro.php' com { nome, email, senha }
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

    // cartão acima do ponto mais alto; se não couber, abaixo do mais baixo
    const cl = cartao.offsetWidth, ca = cartao.offsetHeight;
    const acima = Math.min(...ys) - 15 - ca;
    const topo = acima >= -8 ? acima : Math.max(...ys) + 15;
    const esquerda = px < L / 2 ? px - 13 : px + 13 - cl;
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
  el.onpointerleave = () => mostrar(-1);
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
  // redesenha (sem animação) quando a largura muda
  new ResizeObserver(() => {
    if (grafico.clientWidth !== grafico._largura) desenharGrafico(grafico._semanas, false);
  }).observe(grafico);
}

atualizarMes();
