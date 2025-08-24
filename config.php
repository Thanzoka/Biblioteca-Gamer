<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'sistema_jogos';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

function redirect($url)
{
    header("Location: $url");
    exit();
}

function verificarLogin()
{
    if (!isset($_SESSION['usuario_id'])) {
        redirect('login.php');
    }
}

// Verificar se a página atual não é login ou cadastro
$pagina_atual = basename($_SERVER['PHP_SELF']);
if (!in_array($pagina_atual, ['login.php', 'cadastro.php']) && !isset($_SESSION['usuario_id'])) {
    redirect('login.php');
}
