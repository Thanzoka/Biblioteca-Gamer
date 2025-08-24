<?php

session_start();

// Se não houver sessão, manda pro login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: landing.php");
    exit();
}

require 'config.php';
verificarLogin();

// Adicionar novo jogo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_jogo'])) {
    $nome = $_POST['nome'];
    $data = !empty($_POST['data_comecado']) ? $_POST['data_comecado'] : null;

    $stmt = $pdo->prepare("INSERT INTO jogos (nome, dia_comecado, status, usuario_id) VALUES (?, ?, 'jogando', ?)");
    $stmt->execute([$nome, $data, $_SESSION['usuario_id']]);
}

// Processar finalização do jogo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_jogo'])) {
    $id = $_POST['id'];
    $dataTermino = null;

    if ($_POST['data_option'] === 'full' && !empty($_POST['data_termino'])) {
        $dataTermino = $_POST['data_termino'];
    } elseif ($_POST['data_option'] === 'year' && !empty($_POST['ano_termino'])) {
        $dataTermino = $_POST['ano_termino'];
    }

    $stmt = $pdo->prepare("UPDATE jogos SET status = 'finalizado', dia_zerado = ? WHERE id = ? AND usuario_id = ?");
    if ($stmt->execute([$dataTermino, $id, $_SESSION['usuario_id']])) {
        header("Location: index.php");
        exit();
    } else {
        die("Erro ao finalizar o jogo");
    }
}

// Atualizar status do jogo
if (isset($_GET['acao'])) {
    $id = $_GET['id'];

    switch ($_GET['acao']) {
        case 'pendente':
            $stmt = $pdo->prepare("UPDATE jogos SET status = 'pendente' WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $_SESSION['usuario_id']]);
            break;

        case 'retornar':
            $stmt = $pdo->prepare("UPDATE jogos SET status = 'jogando', dia_comecado = CURDATE() WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $_SESSION['usuario_id']]);
            break;

        case 'espera':
            $stmt = $pdo->prepare("UPDATE jogos SET status = 'espera' WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $_SESSION['usuario_id']]);
            break;
    }

    header("Location: index.php");
    exit();
}

// Obter jogos por status
function getJogosPorStatus($pdo, $status, $usuario_id)
{
    $stmt = $pdo->prepare("SELECT * FROM jogos WHERE status = ? AND usuario_id = ? ORDER BY nome ASC");
    $stmt->execute([$status, $usuario_id]);
    return $stmt->fetchAll();
}

$jogosJogando = getJogosPorStatus($pdo, 'jogando', $_SESSION['usuario_id']);
$jogosFinalizados = getJogosPorStatus($pdo, 'finalizado', $_SESSION['usuario_id']);
$jogosPendentes = getJogosPorStatus($pdo, 'pendente', $_SESSION['usuario_id']);
$jogosEspera = getJogosPorStatus($pdo, 'espera', $_SESSION['usuario_id']);

function formatarData($data)
{
    if (empty($data)) return 'N/A';

    if (preg_match('/^\d{4}$/', $data)) {
        return $data;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        return date('d/m/Y', strtotime($data));
    }

    return $data;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Jogos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <h1><i class="fas fa-gamepad"></i> Meus Jogos</h1>
            <div class="user-menu">
                <span class="user-greeting">Olá, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
                <a href="logout.php" class="btn btn-sair">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </header>

        <section class="form-novo">
            <h2><i class="fas fa-plus-circle"></i> Adicionar Novo Jogo</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="nome" placeholder="Nome do jogo" required>
                    <input type="date" name="data_comecado">
                    <button type="submit" name="adicionar_jogo" class="btn btn-primary">
                        <i class="fas fa-gamepad"></i> Adicionar
                    </button>
                </div>
            </form>
        </section>

        <!-- Popup para data de término -->
        <div id="dataPopup" class="data-popup">
            <h3>Quando terminou este jogo?</h3>
            <form method="POST" action="index.php">
                <input type="hidden" name="id" id="popupJogoId">
                <input type="hidden" name="finalizar_jogo" value="1">

                <div class="data-options">
                    <label>
                        <input type="radio" name="data_option" value="full" checked>
                        Data completa
                    </label>
                    <label>
                        <input type="radio" name="data_option" value="year">
                        Apenas o ano
                    </label>
                </div>

                <div id="fullDateField" class="data-field">
                    <input type="date" name="data_termino" value="<?= date('Y-m-d') ?>">
                </div>

                <div id="yearField" class="data-field" style="display:none;">
                    <input type="number" name="ano_termino" min="1990" max="<?= date('Y') ?>"
                        value="<?= date('Y') ?>" placeholder="Digite o ano">
                </div>

                <div class="popup-buttons">
                    <button type="button" onclick="hideDataPopup()">Cancelar</button>
                    <button type="submit" class="btn-primary">OK</button>
                </div>
            </form>
        </div>

        <script>
            function showDataPopup(jogoId) {
                document.getElementById('popupJogoId').value = jogoId;
                document.getElementById('dataPopup').style.display = 'block';
            }

            function hideDataPopup() {
                document.getElementById('dataPopup').style.display = 'none';
            }

            // Alternar entre data completa e apenas ano
            document.querySelectorAll('input[name="data_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('fullDateField').style.display =
                        this.value === 'full' ? 'block' : 'none';
                    document.getElementById('yearField').style.display =
                        this.value === 'year' ? 'block' : 'none';
                });
            });
        </script>

        <div class="lists-container">
            <!-- Lista Jogando -->
            <section class="jogo-list">
                <h2><i class="fas fa-play-circle"></i> Jogando</h2>
                <?php if (empty($jogosJogando)): ?>
                    <p>Nenhum jogo sendo jogado no momento.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Jogo</th>
                                <th>Iniciado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jogosJogando as $jogo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($jogo['nome']) ?></td>
                                    <td class="jogo-date"><?= formatarData($jogo['dia_comecado']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="#" onclick="showDataPopup(<?= $jogo['id'] ?>)" class="btn action-btn finished-btn">
                                                <i class="fas fa-flag-checkered"></i> Finalizar
                                            </a>
                                            <a href="index.php?acao=pendente&id=<?= $jogo['id'] ?>" class="btn action-btn unknown-btn">
                                                <i class="fas fa-question"></i> Pendente
                                            </a>
                                            <!-- <a href="index.php?acao=espera&id=<?= $jogo['id'] ?>" class="btn action-btn continue-btn">
                                                <i class="fas fa-clock"></i> Espera
                                            </a> -->
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <!-- Lista Na Espera -->
            <!-- <section class="jogo-list">
                <h2><i class="fas fa-clock"></i> Na Espera</h2>
                <?php if (empty($jogosEspera)): ?>
                    <p>Nenhum jogo na lista de espera.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Jogo</th>
                                <th>Iniciado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jogosEspera as $jogo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($jogo['nome']) ?></td>
                                    <td class="jogo-date"><?= formatarData($jogo['dia_comecado']) ?></td>
                                    <td>
                                        <a href="index.php?acao=retornar&id=<?= $jogo['id'] ?>" class="btn action-btn return-btn">
                                            <i class="fas fa-undo"></i> Retornar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section> -->

            <!-- Lista Pendentes -->
            <section class="jogo-list">
                <h2><i class="fas fa-question-circle"></i> Pendentes</h2>
                <?php if (empty($jogosPendentes)): ?>
                    <p>Nenhum jogo pendente.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Jogo</th>
                                <th>Adicionado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jogosPendentes as $jogo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($jogo['nome']) ?></td>
                                    <td class="jogo-date"><?= formatarData($jogo['dia_comecado']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="index.php?acao=retornar&id=<?= $jogo['id'] ?>" class="btn action-btn return-btn">
                                                <i class="fas fa-undo"></i> Jogar
                                            </a>
                                            <!-- <a href="#" onclick="showDataPopup(<?= $jogo['id'] ?>)" class="btn action-btn finished-btn">
                                                <i class="fas fa-flag-checkered"></i> Finalizar
                                            </a> -->
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <!-- Lista Terminados -->
            <section class="jogo-list">
                <h2><i class="fas fa-flag-checkered"></i> Terminados</h2>
                <?php if (empty($jogosFinalizados)): ?>
                    <p>Nenhum jogo finalizado.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Jogo</th>
                                <th>Iniciado em</th>
                                <th>Finalizado em</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jogosFinalizados as $jogo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($jogo['nome']) ?></td>
                                    <td class="jogo-date"><?= formatarData($jogo['dia_comecado']) ?></td>
                                    <td class="jogo-date"><?= formatarData($jogo['dia_zerado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>

</html>