<?php
session_start();
require_once 'conecta.php';

// 1. Verificação de login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_logado = (int) $_SESSION['id_usuario'];

// 2. Busca as fichas do utilizador logado
$sql = "SELECT * FROM ficha_personagem WHERE id_usuario = $id_usuario_logado ORDER BY id_ficha DESC";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Fichas - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">
    <style>
        body { background-color: #f0f0f0; margin: 0; font-family: sans-serif; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .cabecalho { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-criar { background-color: #c00000; color: white; padding: 10px 18px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .btn-criar:hover { background-color: #a00000; }
        
        .grid-fichas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .card-ficha {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 15px;
        }

        /* ESTILO DA FOTO DO PERSONAGEM */
        .img-ficha-card {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 12px;
            background-color: #ddd;
            display: block;
        }

        .capa-padrao {
            width: 100%;
            height: 140px;
            background-color: #333;
            color: #fff;
            border-radius: 6px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .card-titulo { margin: 0 0 5px 0; font-size: 18px; color: #111; }
        .card-sub { font-size: 13px; color: #666; margin-bottom: 8px; }
        
        /* BOTÕES DO CARD */
        .btn-abrir {
            display: block;
            text-align: center;
            background-color: #c00000;
            color: white;
            padding: 8px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn-abrir:hover { background-color: #a00000; }

        .btn-deletar {
            width: 100%;
            background-color: #721c24;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
        }
        .btn-deletar:hover { background-color: #501218; }
    </style>
</head>
<body>

    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav">
            <a href="fichas.php" class="active">Fichas</a>
            <a href="campanhas.php">Campanhas</a>
            <a href="regras.php">Regras</a>
        </nav>
    </header>

    <div class="container">
        <div class="cabecalho">
            <h2>Minhas Fichas</h2>
            <a href="criarFicha.php" class="btn-criar">+ Criar Nova Ficha</a>
        </div>

        <div class="grid-fichas">
            <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                <?php while ($f = mysqli_fetch_assoc($resultado)): ?>
                    <div class="card-ficha">
                        <div>
                            <!-- EXIBIÇÃO DA FOTO DO PERSONAGEM -->
                            <?php 
                            $caminho_foto = 'uploads/' . $f['foto_personagem'];
                            if (!empty($f['foto_personagem']) && file_exists($caminho_foto)): 
                            ?>
                                <img src="<?php echo htmlspecialchars($caminho_foto); ?>" alt="Foto de <?php echo htmlspecialchars($f['nome_personagem']); ?>" class="img-ficha-card">
                            <?php else: ?>
                                <div class="capa-padrao">SEM FOTO</div>
                            <?php endif; ?>

                            <h3 class="card-titulo"><?php echo htmlspecialchars($f['nome_personagem']); ?></h3>
                            <p class="card-sub">
                                <strong>Origem:</strong> <?php echo htmlspecialchars($f['origem'] ?? '—'); ?><br>
                                <strong>Classe:</strong> <?php echo htmlspecialchars($f['classe_ou_trilha'] ?? '—'); ?>
                            </p>
                            <p class="card-sub" style="color: #c00000; font-weight: bold;">
                                PV: <?php echo (int)$f['pv_atual'] . '/' . (int)$f['pv_maximo']; ?>
                            </p>
                        </div>

                        <div>
                            <!-- BOTÃO VER FICHA -->
                            <a href="ficha_criada.php?id=<?php echo (int) $f['id_ficha']; ?>" class="btn-abrir">Ver Ficha</a>

                            <!-- BOTÃO APAGAR FICHA -->
                            <form action="deletarFicha.php" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar esta ficha permanentemente?');">
                                <input type="hidden" name="id_ficha" value="<?php echo (int) $f['id_ficha']; ?>">
                                <button type="submit" class="btn-deletar">Apagar Ficha</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Você ainda não criou nenhuma ficha de personagem.</p>
            <?php endif; ?>
        </div>
    </div>

<script src="assets/site.min.js" defer></script>
</body>
</html>