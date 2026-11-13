<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>D20REALITAS - Home</title>
    <link rel="stylesheet" href="home.css">
</head>
<body class="pagina-inicial">
    <header class="site-header">
        <a href="Index.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav" aria-label="Navegação principal">
            <a href="login.php">Fichas</a>
            <a href="login.php">Campanhas</a>
            <a href="login.php">Regras</a>
        </nav>
        <div class="header-right"><a href="login.php" class="btn-entrar">Iniciar Sessão</a></div>
    </header>

    <section class="banner-hero">
        <img src="banner.png" alt="Logo D20 Realitas">
    </section>

    <main class="conteudo-inicial">
        <section class="sobre">
            <img src="agathalendo.png" alt="Personagem lendo">
            <div class="caixa-texto">
                <h2>Sobre o projeto</h2>
                <p>O objetivo do nosso site é oferecer uma solução que centralize a gestão de fichas, mecânicas e comunicação. A relevância deste projeto é fundamentada na necessidade de modernizar a experiência das sessões de RPG sem descaracterizar sua natureza presencial.</p>
            </div>
        </section>

        <section class="explicacao">
            <h2>Como funciona?</h2>
            <div class="cards-container">
                <div class="card">
                    <p>Crie campanhas, adicione pistas visuais, gerencie o inventário dos seus jogadores e consulte as regras de Ordem Paranormal direto no painel, sem pausar a narrativa.</p>
                </div>

                <div class="card">
                    <p>Monte sua ficha de Investigador em poucos minutos. Escolha sua Classe, Trilha e Origem com distribuição automática de Atributos e Perícias.</p>
                </div>

                <div class="card">
                    <p>Totalmente web! Convide seus amigos com um link e comecem a desvendar os mistérios sem downloads ou configurações complicadas.</p>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <nav class="footer-links" aria-label="Informações do site">
            <a href="privacidade.php">Privacidade</a>
            <a href="sobre-sistema.php">Sobre o sistema</a>
            <a href="termos.php">Termos de uso</a>
        </nav>
        <p>Sobre o D20REALITAS | Contato: teste@gmail.com</p>
    </footer>

    <script>
        const itens = document.querySelectorAll('.caixa-texto, .cards-container .card');

        if ('IntersectionObserver' in window) {
            const observador = new IntersectionObserver((entradas, observadorAtual) => {
                entradas.forEach((entrada) => {
                    if (!entrada.isIntersecting) return;

                    entrada.target.classList.add('visivel');
                    observadorAtual.unobserve(entrada.target);
                });
            }, { threshold: 0.15 });

            itens.forEach((item) => {
                item.classList.add('revelar');
                observador.observe(item);
            });
        }
    </script>
    <script src="assets/site.min.js" defer></script>
</body>
</html>