<?php
session_start();
require_once 'conecta.php';

// Se não estiver logado, guarda o convite e volta para ele depois do login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['t']) || trim($_GET['t']) === '') {
    die("Link de convite inválido ou incompleto.");
}

$id_usuario = (int) $_SESSION['id_usuario'];
$token = mysqli_real_escape_string($conexao, trim($_GET['t']));

// Procura a campanha correspondente ao token
$res_campanha = mysqli_query($conexao, "SELECT id_campanha, nome_campanha, id_usuario FROM campanha WHERE token_convite = '$token'");

if (!$res_campanha || mysqli_num_rows($res_campanha) == 0) {
    die("Campanha não encontrada ou convite expirado.");
}

$campanha = mysqli_fetch_assoc($res_campanha);
$id_campanha = (int) $campanha['id_campanha'];

// O mestre não precisa de convite para a própria campanha
if ((int) $campanha['id_usuario'] === $id_usuario) {
    header("Location: detalhesCampanha.php?id=" . $id_campanha);
    exit();
}

// Se já está na campanha, vai direto para ela
$res_verifica = mysqli_query($conexao, "SELECT 1 FROM campanha_fichas WHERE id_campanha = $id_campanha AND id_usuario = $id_usuario");
if (mysqli_num_rows($res_verifica) > 0) {
    header("Location: detalhesCampanha.php?id=" . $id_campanha);
    exit();
}

// Quando o jogador escolhe a ficha e envia o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ficha = (int) ($_POST['id_ficha'] ?? 0);

    // A ficha escolhida precisa ser DESTE jogador
    $res_dona = mysqli_query($conexao, "SELECT 1 FROM ficha_personagem WHERE id_ficha = $id_ficha AND id_usuario = $id_usuario");

    if (!$res_dona || mysqli_num_rows($res_dona) == 0) {
        $erro = "Ficha inválida. Escolha uma das suas fichas.";
    } else {
        $sql_insert = "INSERT INTO campanha_fichas (id_campanha, id_usuario, id_ficha) VALUES ($id_campanha, $id_usuario, $id_ficha)";
        if (mysqli_query($conexao, $sql_insert)) {
            header("Location: detalhesCampanha.php?id=" . $id_campanha);
            exit();
        }
        $erro = "Erro ao entrar na campanha.";
    }
}

// Fichas do jogador para ele escolher
$res_fichas = mysqli_query($conexao, "SELECT id_ficha, nome_personagem, classe_ou_trilha FROM ficha_personagem WHERE id_usuario = $id_usuario ORDER BY nome_personagem");
$tem_fichas = $res_fichas && mysqli_num_rows($res_fichas) > 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite para Campanha - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>

    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav" aria-label="Navegação principal">
            <a href="fichas.php">Fichas</a>
            <a href="campanhas.php" class="active">Campanhas</a>
            <a href="regras.php">Regras</a>
        </nav>
        <div class="header-right"></div>
    </header>

    <div class="convite-card">
        <h2>Convite de Campanha</h2>
        <p>Você foi convidado para jogar:<br><strong><?php echo htmlspecialchars($campanha['nome_campanha']); ?></strong></p>

        <?php if (isset($erro)): ?>
            <div class="erro-msg" style="margin-top:15px;"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <?php if ($tem_fichas): ?>
            <form method="POST" action="">
                <label for="id_ficha"><strong>Escolha com qual personagem você vai jogar:</strong></label>
                <select name="id_ficha" id="id_ficha" required>
                    <option value="" disabled selected>Selecione uma ficha...</option>
                    <?php while ($ficha = mysqli_fetch_assoc($res_fichas)): ?>
                        <option value="<?php echo (int) $ficha['id_ficha']; ?>">
                            <?php echo htmlspecialchars($ficha['nome_personagem'] . ' — ' . ($ficha['classe_ou_trilha'] ?: 'Sem classe')); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="btn-criar">Entrar na Campanha</button>
            </form>
        <?php else: ?>
            <p style="margin:20px 0;">Você ainda não tem nenhuma ficha. Crie um personagem para continuar entrando nesta campanha.</p>
            <a href="criarFicha.php?convite=<?php echo urlencode($token); ?>" class="btn-criar">+ Criar Ficha</a>
        <?php endif; ?>
    </div>

<script src="assets/site.min.js" defer></script>
</body>
</html>
