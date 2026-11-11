<?php
// MODELO da conexão com o banco. Este arquivo VAI para o GitHub.
//
// Como usar: copie este arquivo, dê o nome db.php (na mesma pasta config/)
// e ajuste os dados abaixo se o seu XAMPP for diferente.
// O db.php fica de fora do GitHub (.gitignore) para não expor senha.

$host    = '127.0.0.1';
$porta   = 3306;        // se você mudou a porta do MySQL no XAMPP, troque aqui
$banco   = 'dashboard';
$usuario = 'root';
$senha   = '';          // no XAMPP, o usuário root vem sem senha

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => 'Não foi possível conectar ao banco de dados.']);
    exit;
}
