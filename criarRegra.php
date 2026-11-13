<?php
session_start();
require_once 'conecta.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_regras = mysqli_real_escape_string($conexao, $_POST['nome_regras']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);

    // INSERT adaptado às tuas colunas: nome_regras e descricao
    $sql = "INSERT INTO regras (nome_regras, descricao) VALUES ('$nome_regras', '$descricao')";

    if (mysqli_query($conexao, $sql)) {
        header("Location: regras.php");
        exit();
    } else {
        $erro = "Erro ao guardar: " . mysqli_error($conexao);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Regra - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>

    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav" aria-label="Navegação principal">
            <a href="fichas.php">Fichas</a>
            <a href="campanhas.php">Campanhas</a>
            <a href="regras.php" class="active">Regras</a>
        </nav>
        <div class="header-right"></div>
    </header>

    <div class="container container-estreito">
        
        <a href="regras.php" class="btn-voltar">&larr; Voltar</a>

        <?php if (isset($erro)) { echo "<div class='erro-msg'>$erro</div>"; } ?>

        <form action="" method="post">
            <div class="campos-principais">
                <div class="grupo-input">
                    <label for="nome">Nome da Regra</label>
                    <input type="text" id="nome" name="nome_regras" required>
                </div>

                <div class="grupo-input">
                    <label for="descricao">O que ela faz?</label>
                    <textarea id="descricao" name="descricao" required></textarea>
                </div>
            </div>

            <div class="botoes-acao">
                <button type="submit" class="btn-criar">Adicionar Regra</button>
            </div>
        </form>
    </div>

<script src="assets/site.min.js" defer></script>
</body>
</html>