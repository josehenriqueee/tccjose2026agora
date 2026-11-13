<?php
// Autentica o usuário e inicia uma sessão de acesso ao sistema.
session_start();
$msg = '';

if($_POST){
    $con = mysqli_connect("localhost", "root", "", "d20realitas");
    
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $sql = "SELECT * FROM Usuario WHERE email = '$email'";
    $resultado = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($resultado) > 0){
        $usuario = mysqli_fetch_assoc($resultado);
        
        if(password_verify($senha, $usuario['senha'])){
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome_usuario'];

            if ($usuario['tipo_usuario'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: home.php");
            }
            exit; 
            
        } else {
            $msg = "<h3 style='color:#cc0000;'>Palavra-passe incorreta. Tente novamente!</h3>";
        }
    } else {
        $msg = "<h3 style='color:#cc0000;'>E-mail não encontrado.</h3>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - D20REALITAS</title>
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
            gap: 100px; /* Reduzi levemente o gap para o conjunto ficar mais harmônico no centro */
            width: 100%;
        }

        /* ESTILOS DA IMAGEM TRANSPARENTE E ESTÁTICA */
        .auth-image {
            /* Removido o flex: 1 para não forçar larguras estranhas */
            display: flex;
            justify-content: center;
        }

        .auth-image img {
            max-width: 500px; 
            width: 100%;
            height: auto;
        }

        /* ESTILOS DO CARTÃO DE LOGIN */
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
            color: #ffffff;
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
            <a href="cadastrar.php" class="btn-entrar" style="background: transparent; color: #cc0000; border: none; font-weight: bold; font-size: 16px;">Cadastrar</a>
        </div>
    </header>

    <main class="auth-wrap">
    <div class="auth-container">

        <!-- IMAGEM NA ESQUERDA -->
        <div class="auth-image">
            <img src="loginfoto.webp" alt="Personagens D20 Realitas">
        </div>

        <!-- FORMULÁRIO NA DIREITA -->
        <div class="login-card">
            <h1>Faça o seu login.</h1>

            <?php echo $msg; ?>

            <form action="" method="post">
                <div class="form-group">
                    <input type="email" name="email" id="email" placeholder="E-mail:" required/>
                </div>

                <div class="form-group">
                    <input type="password" name="senha" id="senha" placeholder="Senha:" required/>
                </div>

                <button type="submit" class="btn-confirmar">Confirmar</button>
            </form>

            <div class="login-links">
                <p>Não tem uma conta? <a href="cadastrar.php">Cadastre-se</a></p>
                <p>Perdeu sua senha? <a href="#">Recuperar senha</a></p>
            </div>
        </div>

    </div>
    </main>

<script src="assets/site.min.js" defer></script>
</body>
</html>