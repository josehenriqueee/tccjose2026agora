<?php
session_start();
require_once 'conecta.php';

// 1. Verifica se alguém está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_logado = $_SESSION['id_usuario'];

// 2. TRAVA DE SEGURANÇA: Apenas ADMINs podem aceder a esta página
$sql_verifica_admin = "SELECT tipo_usuario FROM usuario WHERE id_usuario = '$id_usuario_logado'";
$res_verifica = mysqli_query($conexao, $sql_verifica_admin);
$dados_user = mysqli_fetch_assoc($res_verifica);

if ($dados_user['tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$mensagem = "";

// 3. Processa o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Escapar dados para evitar SQL Injection
    $nome = mysqli_real_escape_string($conexao, trim($_POST['nome_usuario']));
    $email = mysqli_real_escape_string($conexao, trim($_POST['email']));
    $senha = trim($_POST['senha']);

    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = "<div class='msg erro'>Preencha todos os campos!</div>";
    } else {
        // Verifica se o e-mail ou o nome já estão registados
        $sql_checa = "SELECT id_usuario FROM usuario WHERE email = '$email' OR nome_usuario = '$nome'";
        $res_checa = mysqli_query($conexao, $sql_checa);

        if (mysqli_num_rows($res_checa) > 0) {
            $mensagem = "<div class='msg erro'>O nome de utilizador ou e-mail já existe no sistema.</div>";
        } else {
            // Encriptar a password de forma segura
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Inserir na base de dados com tipo_usuario = 'admin'
            $sql_insere = "INSERT INTO usuario (nome_usuario, email, senha, tipo_usuario) 
                           VALUES ('$nome', '$email', '$senha_hash', 'admin')";

            if (mysqli_query($conexao, $sql_insere)) {
                $mensagem = "<div class='msg sucesso'>Administrador cadastrado com sucesso!</div>";
            } else {
                $mensagem = "<div class='msg erro'>Erro ao cadastrar: " . mysqli_error($conexao) . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Admin - D20REALITAS</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #ececec; 
            color: #111; 
            padding: 40px; 
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 { color: #000; margin-top: 0; }
        
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #222;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-block;
        }
        .btn:hover { background-color: #000; }
        
        .btn-voltar {
            background-color: #777;
            margin-right: 10px;
        }
        .btn-voltar:hover { background-color: #555; }

        .msg {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: bold;
            text-align: center;
        }
        .msg.erro { background-color: #ffcccc; color: #cc0000; border: 1px solid #cc0000; }
        .msg.sucesso { background-color: #ccffcc; color: #006600; border: 1px solid #006600; }
    </style>
</head>
<body>

<div class="container">
    <h2>Cadastrar Novo Administrador</h2>
    
    <?php echo $mensagem; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="nome_usuario">Nome do Administrador</label>
            <input type="text" name="nome_usuario" id="nome_usuario" required placeholder="Digite o nome de utilizador">
        </div>
        
        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" required placeholder="admin@d20realitas.com">
        </div>

        <div class="form-group">
            <label for="senha">Palavra-passe (Senha)</label>
            <input type="password" name="senha" id="senha" required placeholder="Crie uma palavra-passe segura">
        </div>

        <div style="margin-top: 25px;">
            <a href="admin.php" class="btn btn-voltar">← Voltar ao Painel</a>
            <button type="submit" class="btn">Concluir Cadastro</button>
        </div>
    </form>
</div>

</body>
</html>