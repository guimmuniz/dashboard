// ==========================================================
// Fluxo — comportamento da interface (só front, sem backend)
// Tudo marcado com "INTEGRAR" é onde entra o fetch para a API.
// ==========================================================

// Caminho da API a partir deste arquivo (front-end/js/app.js -> dashboard/api/).
// Funciona igual em index.html e em logado/*.php. Uso: fetch(API + 'transacoes.php')
const API = new URL('../../api/', document.currentScript.src).href;

const MESES = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
  'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

// ---------- mensagens ----------
function mostrarMsg(el, texto, tipo = 'erro') {
  if (!el) return;
  el.className = 'msg ' + tipo;
  el.textContent = texto;
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
document.addEventListener('click', e => {
  const linha = e.target.closest('[data-id]');
  if (!linha) return;

  if (e.target.matches('[data-editar]')) {
    const ehCategoria = linha.classList.contains('categoria-linha');
    abrirModal(ehCategoria ? 'modal-categoria' : 'modal-transacao',
      ehCategoria ? 'Editar categoria' : 'Editar transação');
    document.querySelector('dialog[open] [name="id"]').value = linha.dataset.id;
    // INTEGRAR: buscar o registro pelo id (GET ...?id=) e preencher os campos do form
  }

  if (e.target.matches('[data-excluir]')) {
    if (!confirm('Excluir este registro?')) return;
    // INTEGRAR: fetch DELETE para a API com o id. Só remover a linha se a resposta for ok.
    linha.remove();
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
    console.log('enviar para a API:', dados);
    form.closest('dialog').close();
  });
});

// ---------- abas de tipo (transacoes.html) ----------
document.querySelectorAll('.abas button').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('ativo'));
    btn.classList.add('ativo');
    // INTEGRAR: recarregar a lista com ?tipo= btn.dataset.tipo
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

atualizarMes();
