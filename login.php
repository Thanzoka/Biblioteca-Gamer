<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario'] = $user['usuario'];
        redirect('index.php');
    } else {
        $erro = "Usuário ou senha incorretos";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/login.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistema de Jogos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Bem-vindo de volta</h2>
            <p>Faça login para acessar sua conta</p>
        </div>

        <div class="login-form">
            <?php if (isset($erro)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="usuario">Usuário</label>
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="usuario" name="usuario" class="form-control" placeholder="Digite seu usuário" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>

                <a href="cadastro.php" class="btn btn-link">
                    Não tem uma conta? Cadastre-se
                </a>
            </form>

            <div class="footer-text">
                <p>Sistema de Gerenciamento de Jogos © <?= date('Y') ?></p>
            </div>
        </div>
    </div>
</body>

</html>