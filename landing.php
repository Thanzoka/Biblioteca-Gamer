<?php
session_start();

// Se já estiver logado, vai direto para a tela de jogos
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Biblioteca Gamer</title>
    <link rel="stylesheet" href="css/inicial.css">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <h1>🎮 Biblioteca Gamer</h1>
        <p>Organize, acompanhe e finalize seus jogos</p>
        <a href="login.php" class="btn">
            Entrar <i class="fas fa-arrow-down"></i>
        </a>
    </div>
</body>

</html>