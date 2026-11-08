# Dashboard

Aplicação web para controle das finanças do mês (entradas e saídas), com simulador de investimentos e cotações via API gratuita. Projeto do **TDE 2** da disciplina de Programação Web (PUCPR).

## Equipe

- Davi Berbek
- Guilherme Martins Muniz
- Rafael Henrique Requião Borges dos Reis

## Funcionalidades

- [ ] Cadastro de usuários e login com sessão PHP, incluindo logout
- [x] CRUD de categorias (com limite mensal opcional)
- [x] CRUD de transações (entradas e saídas)
- [ ] Dashboard do mês: total de entradas, total de saídas e gasto por categoria ao lado do limite
- [ ] Simulador de investimentos por perfil (conservador, moderado ou arrojado), com histórico salvo
- [ ] Cotações em tempo real por API gratuita, consultada pelo PHP

## Tecnologias

- HTML, CSS e JavaScript (comunicação com a API por `fetch` e JSON)
- PHP (API, sessões e acesso ao banco)
- MariaDB/MySQL (desenvolvido e testado com MariaDB 10.4.32, do XAMPP)
- Servidor local: XAMPP

## Estrutura de pastas

```
dashboard/
├── banco_de_dados/
│   ├── schema.sql         
├── config/                
├── api/                   
├── .gitignore
└── README.md
```

## Como instalar e executar

### 1. Pré-requisitos

- XAMPP instalado, com **Apache** e **MySQL** iniciados no painel.
- O projeto deve ficar em `C:\xampp\htdocs\dashboard`.

### 2. Criar o banco de dados

> **Atenção:** o `schema.sql` começa com `DROP DATABASE IF EXISTS dashboard;`. Se você já tiver um banco com esse nome, **ele será apagado**.

**Opção A: phpMyAdmin**

1. Abra `http://localhost/phpmyadmin`.
2. Sem selecionar nenhum banco, clique na aba **Importar**.
3. Escolha `banco_de_dados/schema.sql` e clique em **Importar**.

**Opção B: terminal (Shell do XAMPP)**

```
mysql -u root -h 127.0.0.1 -P 3306 < banco_de_dados\schema.sql
```

### 3. Configurar a conexão

Observação: a porta padrão do MySQL no XAMPP é **3306**. Se ela estiver ocupada por outro MySQL instalado na máquina, é possível mudar a porta no `my.ini` do XAMPP (seções `[client]` e `[mysqld]`) e, no phpMyAdmin, acrescentar `$cfg['Servers'][$i]['port']` em `C:\xampp\phpMyAdmin\config.inc.php`. Depois, a mesma porta deve ser informada no `db.php`.

### 4. Acessar a aplicação

### 5. Usuário para teste

## Modelo do banco de dados

| Tabela | Finalidade | Campos principais |
|---|---|---|
| `usuario` | Cadastro e autenticação | `id`, `nome`, `email` (único), `senha` (hash), `data_criacao` |
| `categoria` | Rótulos criados pelo usuário | `id`, `id_usuario`, `nome`, `tipo` (`entrada` ou `saida`), `limite_mensal` (opcional) |
| `transacao` | Cada movimentação financeira | `id`, `id_categoria`, `descricao`, `valor`, `data_transacao` |
| `simulacao` | Histórico do simulador | `id`, `id_usuario`, `perfil`, `aporte_mensal`, `prazo_meses`, `resultado`, `data_simulacao` |

## Andamento

- [x] Ambiente local (XAMPP, MariaDB, phpMyAdmin)
- [x] Modelagem e script do banco (`schema.sql`)
- [x] Dados de exemplo e consultas do dashboard validadas
- [x] Repositório no GitHub
- [ ] Conexão PHP com PDO
- [ ] Cadastro, login e sessão
- [ ] CRUD de categorias e transações
- [ ] Dashboard (front end)
- [ ] Simulador e API de cotações
- [ ] README final e teste de instalação do zero

