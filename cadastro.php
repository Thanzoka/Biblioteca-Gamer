<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha) VALUES (?, ?)");
        $stmt->execute([$usuario, $senha]);
        $_SESSION['success_message'] = "Cadastro realizado com sucesso!";
        redirect('login.php');
    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar: " . (strpos($e->getMessage(), 'Duplicate entry') !== false ? "Usuário já existe" : "Erro no servidor");
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/cadastro.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | Sistema de Jogos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="signup-container">
        <div class="signup-header">
            <h2>Crie sua conta</h2>
            <p>Registre-se para começar a usar o sistema</p>
        </div>

        <div class="signup-form">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success_message'] ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($erro)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="usuario">Usuário</label>
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="usuario" name="usuario" class="form-control" placeholder="Digite um nome de usuário" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Crie uma senha segura" required>
                    <div class="password-requirements">
                        Use pelo menos 6 caracteres
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Cadastrar
                </button>

                <a href="login.php" class="btn-link">
                    <i class="fas fa-sign-in-alt"></i> Já tem uma conta? Faça login
                </a>
            </form>

            <div class="footer-text">
                <p>Sistema de Gerenciamento de Jogos © <?= date('Y') ?></p>
            </div>
        </div>
    </div>
</body>

</html>