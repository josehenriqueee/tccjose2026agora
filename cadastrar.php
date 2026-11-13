<?php
session_start();
require_once 'conecta.php';

$mensagem = "";
$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        
        $stmt_check = $conexao->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $erro = "Este e-mail já está registado!";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("INSERT INTO usuario (nome_usuario, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha_hash);

            if ($stmt->execute()) {
                $_SESSION['sucesso'] = "Registo efetuado com sucesso! Faça login para continuar.";
                header("Location: login.php");
                exit();
            } else {
                $erro = "Erro ao registar utilizador no sistema: " . $conexao->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">

    <style>
        /* ESTILOS DE LAYOUT LADO A LADO E AFASTAMENTO */
        .auth-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 85vh;
            padding: 40px 20px;
        }

        .auth-container {
            display: flex;
            flex-direction: row; 
            align-items: center;
            justify-content: center; /* CENTRALIZA O CONJUNTO TODO NO MEIO DA TELA */
            gap: 100px;
            width: 100%;
        }

        /* ESTILOS DA IMAGEM TRANSPARENTE E ESTÁTICA */
        .auth-image {
            display: flex;
            justify-content: center;
        }

        .auth-image img {
            max-width: 500px; 
            width: 100%;
            height: auto;
        }

        /* ESTILOS DO CARTÃO DE CADASTRO */
        .login-card {
            width: 100%;
            max-width: 400px;
            background: transparent;
            padding: 20px;
        }

        .login-card h1 {
            font-family: 'Georgia', 'Times New Roman', serif; 
            font-size: 2.5em;
            margin-bottom: 30px;
            color: #111;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            box-sizing: border-box;
            padding: 16px 20px;
            border: 1px solid #ccc;
            border-radius: 30px;
            font-size: 15px;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #cc0000;
        }

        /* BOTÃO VERMELHO E BRANCO */
        .btn-confirmar {
            width: 100%;
            padding: 15px;
            background-color: #cc0000; 
            color: #ffffff; 
            border: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-confirmar:hover {
            background-color: #a80000; 
        }

        .alerta-erro {
            color: #cc0000;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .login-links {
            margin-top: 25px;
            text-align: center;
            font-size: 0.95em;
            color: #555;
        }

        .login-links a {
            color: #cc0000; 
            text-decoration: none;
            font-weight: bold;
        }

        .login-links a:hover {
            text-decoration: underline;
        }

        /* RESPONSIVIDADE PARA CELULARES */
        @media (max-width: 900px) {
            .auth-container {
                flex-direction: column;
                gap: 40px;
            }
            .auth-image img {
                max-width: 380px; 
            }
            .login-card {
                text-align: center;
            }
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
            <a href="login.php" class="btn-entrar" style="background: transparent; color: #cc0000; border: none; font-weight: bold; font-size: 16px;">Iniciar Sessão</a>
        </div>
    </header>

    <main class="auth-wrap">
    <div class="auth-container">

        <!-- IMAGEM NA ESQUERDA (formato .png) -->
        <div class="auth-image">
            <img src="loginfoto.webp" alt="Personagens D20 Realitas">
        </div>

        <!-- FORMULÁRIO NA DIREITA -->
        <div class="login-card">
            <h1>Faça o seu cadastro.</h1>

            <?php if (!empty($erro)): ?>
                <div class="alerta-erro"><?php echo $erro; ?></div>
            <?php endif; ?>

            <form action="cadastrar.php" method="POST">
                <div class="form-group">
                    <input type="text" id="nome" name="nome" required placeholder="Nome do usuário:">
                </div>

                <div class="form-group">
                    <input type="email" id="email" name="email" required placeholder="E-mail:">
                </div>

                <div class="form-group">
                    <input type="password" id="senha" name="senha" required placeholder="Senha:">
                </div>

                <div class="form-group">
                    <input type="password" id="senha_confirma" name="senha_confirma" placeholder="Confirmar senha:">
                </div>

                <button type="submit" class="btn-confirmar">Confirmar</button>
            </form>

            <div class="login-links">
                <p>Já tem uma conta? <a href="login.php">Faça Login</a></p>
            </div>
        </div>

    </div>
    </main>

<script src="assets/site.min.js" defer></script>
</body>
</html>