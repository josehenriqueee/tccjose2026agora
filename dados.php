<?php
session_start();

$resultado = null;
$dado_escolhido = null;

// Verifica se o usuário clicou em algum botão de rolar dado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dado'])) {
    $dado_escolhido = (int)$_POST['dado'];
    
    // Lista de dados permitidos por segurança
    $dados_validos = [4, 6, 8, 10, 12, 20, 100];
    
    if (in_array($dado_escolhido, $dados_validos)) {
        // random_int é a função mais segura do PHP para gerar números aleatórios
        $resultado = random_int(1, $dado_escolhido);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rolador de Dados - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">

    <style>
        .page-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 20px;
        }

        .dice-roller-container {
            background-color: transparent;
            max-width: 500px;
            width: 100%;
            text-align: center;
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .dice-roller-container h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            color: #111;
        }

        .dice-roller-container p {
            color: #555;
            margin-bottom: 30px;
            font-family: sans-serif;
        }

        /* Formulário que contém os botões */
        .dice-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        /* Estilo dos botões vermelhos idênticos aos do login */
        .dice-buttons button {
            background-color: #cc0000;
            color: #ffffff;
            border: none;
            border-radius: 30px; /* Estilo pílula */
            padding: 12px 25px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .dice-buttons button:hover {
            background-color: #a80000;
        }

        /* Caixa onde o resultado aparece */
        .dice-result {
            background: transparent;
            border: 2px solid #ccc;
            border-radius: 20px;
            padding: 30px;
            font-size: 1.5em;
            color: #333;
        }

        .dice-result span {
            display: block;
            font-size: 3.5em;
            font-weight: bold;
            color: #cc0000; /* Número em destaque vermelho */
            margin-top: 15px;
        }
        
        .no-result {
            color: #999;
            font-size: 1.2em;
            font-style: italic;
        }
    </style>
</head>
<body>

    <header class="site-header">
        <a href="Index.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav" aria-label="Navegação principal">
            <a href="fichas.php">Fichas</a>
            <a href="campanhas.php">Campanhas</a>
            <a href="regras.php">Regras</a>
        </nav>
        <div class="header-right">
            <?php if(isset($_SESSION['id_usuario'])): ?>
                <span style="font-weight:bold; color:#111; margin-right: 15px;">Olá, <?php echo $_SESSION['nome']; ?></span>
                <a href="logout.php" class="btn-entrar" style="background: transparent; color: #cc0000; border: none; font-weight: bold;">Sair</a>
            <?php else: ?>
                <a href="login.php" class="btn-entrar" style="background: transparent; color: #cc0000; border: none; font-weight: bold;">Iniciar Sessão</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="page-wrap">
        <div class="dice-roller-container">
            <h1>Mesa de Rolagens</h1>
            <p>Selecione o dado que deseja rolar (seguro contra fraudes).</p>
            
            <!-- O formulário envia a requisição POST para a própria página -->
            <form method="POST" action="">
                <div class="dice-buttons">
                    <button type="submit" name="dado" value="4">D4</button>
                    <button type="submit" name="dado" value="6">D6</button>
                    <button type="submit" name="dado" value="8">D8</button>
                    <button type="submit" name="dado" value="10">D10</button>
                    <button type="submit" name="dado" value="12">D12</button>
                    <button type="submit" name="dado" value="20">D20</button>
                    <button type="submit" name="dado" value="100">D100</button>
                </div>
            </form>

            <div class="dice-result">
                <?php if ($resultado !== null): ?>
                    Resultado do D<?php echo $dado_escolhido; ?>:
                    <span><?php echo $resultado; ?></span>
                <?php else: ?>
                    <div class="no-result">Nenhum dado rolado ainda.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>

<script src="assets/site.min.js" defer></script>
</body>
</html>