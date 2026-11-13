<?php
session_start();
require_once 'conecta.php';

$nome_exibir = 'Aventureiro';
$eh_admin = false; // Começa assumindo que não é admin

if (isset($_SESSION['id_usuario'])) {
    $id = $_SESSION['id_usuario'];

    // Consulta o nome e o tipo do usuário de uma vez só
    $sql = "SELECT nome_usuario, tipo_usuario FROM usuario WHERE id_usuario = '$id'";
    $resultado = mysqli_query($conexao, $sql);

    if ($resultado && $usuario = mysqli_fetch_assoc($resultado)) {
        $nome_exibir = $usuario['nome_usuario'];
        
        // Verifica se é admin
        if ($usuario['tipo_usuario'] === 'admin') {
            $eh_admin = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D20REALITAS</title>
    <link rel="stylesheet" href="home.css">
</head>
<body class="pagina-home">

    <!-- CABEÇALHO COM A VERIFICAÇÃO DE ADMIN -->
    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        
        <nav class="site-nav" aria-label="Navegação principal">
            <a href="fichas.php">Fichas</a>
            <a href="campanhas.php">Campanhas</a>
            <a href="regras.php">Regras</a>
            
            <!-- O PHP entra em ação aqui: só cria o link se $eh_admin for verdadeiro -->
            <?php if ($eh_admin) { ?>
                <a href="admin.php" style="color: #cc0000; font-weight: bold;">Painel Admin</a>
            <?php } ?>
        </nav>
        
        <div class="header-right"><span class="user-pill">Olá, <?php echo htmlspecialchars($nome_exibir); ?>!</span></div>
    </header>

    <!-- RESTO DA PÁGINA (CARROSSEL E CONTEÚDO) -->
    <main>
        <section class="banner-hero">
            <div class="voltar-wrap">
                <a href="index.php" class="btn-voltar">&larr; Sair</a>
            </div>
            <img src="banner_home.png" alt="Banner D20 Realitas">
        </section>

        <section class="texto-introducao">
            <p>Você já imaginou jogar RPG de forma totalmente Realista e Imersiva? Com o D20REALITAS, você se aventura no universo de Ordem Paranormal com muito mais dinamismo e facilidade.</p>
        </section>

        <section class="carrossel-master">

            <button class="seta-principal" onclick="mudarSlide(-1)">&lt;</button>

            <!-- SLIDE 1: CAMPANHAS -->
            <div class="janela-conteudo ativa">
                <div class="imagem-lado">
                    <img src="agathatablet.webp" alt="Campanhas Tablet">
                </div>
                <div class="info-lado">
                    <h2 class="titulo-sessao">CAMPANHAS</h2>
                    <div class="card-cinza">
                        <p>Nesta página você consegue criar e acessar campanhas! Basta Clicar no botão <strong>Campanhas</strong>!</p>
                        <a href="campanhas.php" class="btn-vermelho">Campanhas</a>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: FICHAS -->
            <div class="janela-conteudo">
                <div class="imagem-lado">
                    <img src="louco.webp" alt="Fichas Arte">
                </div>
                <div class="info-lado">
                    <h2 class="titulo-sessao">FICHAS</h2>
                    <div class="card-cinza">
                        <p>Nesta página você acessa todas suas fichas em um lugar só! Basta clicar em <strong>Consultar Fichas</strong>!</p>
                        <a href="fichas.php" class="btn-vermelho">Consultar Fichas</a>
                    </div>
                </div>
            </div>

            <!-- SLIDE 3: REGRAS -->
            <div class="janela-conteudo">
                <div class="imagem-lado">
                    <img src="agatharegras.png" alt="Regras Arte">
                </div>
                <div class="info-lado">
                    <h2 class="titulo-sessao">REGRAS</h2>
                    <div class="card-cinza">
                        <p>Nesta página você consegue acessar regras! Basta Clicar no botao <strong>Consultar Regras</strong>!</p>
                        <a href="regras.php" class="btn-vermelho">Consultar Regras</a>
                    </div>
                </div>
            </div>

            <button class="seta-principal" onclick="mudarSlide(1)">&gt;</button>

        </section>
    </main>

    <script>
        let slideAtual = 0;

        function mudarSlide(direcao) {
            const slides = document.querySelectorAll('.janela-conteudo');
            slides[slideAtual].classList.remove('ativa');
            slideAtual = (slideAtual + direcao + slides.length) % slides.length;
            slides[slideAtual].classList.add('ativa');
        }
    </script>
<script src="assets/site.min.js" defer></script>
</body>
</html>