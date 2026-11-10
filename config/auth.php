<?php
// Incluído no topo de toda página de front-end/logado/.
// Sem usuário na sessão -> volta para o login.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.html');
    exit;
}
