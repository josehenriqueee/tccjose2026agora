<?php
session_start();
require_once 'conecta.php';

// 1. Proteção básica
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_logado = $_SESSION['id_usuario'];

// 2. TRAVA DE SEGURANÇA E BUSCA DE NOME: Verifica se é "admin" e pega o nome
$sql_verifica_admin = "SELECT tipo_usuario, nome_usuario FROM usuario WHERE id_usuario = '$id_usuario_logado'";
$res_verifica = mysqli_query($conexao, $sql_verifica_admin);
$dados_user = mysqli_fetch_assoc($res_verifica);

if ($dados_user['tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Guarda o nome do admin para usar no HTML
$nome_admin = $dados_user['nome_usuario'];

// 3. Lógica de Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// 4. Lógica para Deletar Usuário
if (isset($_GET['deletar'])) {
    $id_deletar = (int) $_GET['deletar'];
    if ($id_deletar != $id_usuario_logado) {
        $sql_del = "DELETE FROM usuario WHERE id_usuario = '$id_deletar'";
        mysqli_query($conexao, $sql_del);
    }
    header("Location: admin.php");
    exit();
}

// 5. Lógica para Deletar Campanha
if (isset($_GET['deletar_campanha'])) {
    $id_camp_del = (int) $_GET['deletar_campanha'];
    $sql_del_camp = "DELETE FROM campanha WHERE id_campanha = '$id_camp_del'";
    mysqli_query($conexao, $sql_del_camp);
    header("Location: admin.php");
    exit();
}

// --- BUSCAR ESTATÍSTICAS PARA O DASHBOARD ---
$q_jogadores = mysqli_query($conexao, "SELECT COUNT(*) as total FROM usuario WHERE tipo_usuario = 'jogador'");
$tot_jogadores = mysqli_fetch_assoc($q_jogadores)['total'] ?? 0;

$q_campanhas = mysqli_query($conexao, "SELECT COUNT(*) as total FROM campanha");
$tot_campanhas = mysqli_fetch_assoc($q_campanhas)['total'] ?? 0;

$q_fichas = mysqli_query($conexao, "SELECT COUNT(*) as total FROM ficha_personagem");
$tot_fichas = mysqli_fetch_assoc($q_fichas)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">
    <style>
        .admin-hero {
            position: relative;
            display: flex;
            min-height: 300px;
            align-items: flex-end;
            overflow: hidden;
            padding: 35px max(24px, calc((100% - 1100px) / 2));
            color: #fff;
            background: #171719;
        }
        .admin-hero::before {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(0, 0, 0, .72), rgba(0, 0, 0, .08)), url("verissimo.webp") center 42% / cover;
            content: "";
            animation: hero-drift 24s ease-in-out infinite alternate;
        }
        .admin-hero-content { position: relative; animation: fade-up .65s ease both; }
        .admin-hero h1 { margin: 0 0 8px; font-family: Georgia, serif; font-size: clamp(1.8rem, 4vw, 2.7rem); }
        .admin-hero p { margin: 0; font-size: 1.05rem; }
        .admin-main { max-width: 1100px; margin: 0 auto; padding: 34px 24px 55px; }
        .admin-top-actions { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 20px; }
        .btn {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 20px;
            background: #cc0000;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            transition: background-color .2s ease, transform .2s ease;
        }
        .btn:hover { background: #a80000; transform: translateY(-2px); }
        .btn-voltar { background: #d3d3d3; color: #111; }
        .btn-voltar:hover { background: #c4c4c4; }
        .btn-logout { background: #3976b8; }
        .btn-logout:hover { background: #2d6097; }
        .dashboard { display: flex; flex-direction: column; gap: 28px; margin: 0 auto 55px; max-width: 820px; }
        .stat-row { display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 28px; min-height: 190px; }
        .stat-row:nth-child(even) .stat-art { order: -1; }
        .motion-ready .js-reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity .55s ease, transform .55s ease;
        }
        .motion-ready .js-reveal.is-visible { opacity: 1; transform: translateY(0); }
        .stat-card {
            display: flex;
            min-height: 150px;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            padding: 24px 28px;
            border-radius: 22px;
            background: #d9d9d9;
            box-shadow: 5px 6px 0 rgba(0, 0, 0, .12);
            color: #111;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 7px 10px 0 rgba(0, 0, 0, .12); }
        .stat-card h2 { margin: 0 0 5px; font-family: Georgia, serif; font-size: 1.2rem; }
        .stat-card .numero { margin: 0; font-size: 2.2rem; font-weight: 800; line-height: 1.1; }
        .stat-card .stat-link { margin-top: 12px; font-size: .85rem; text-decoration: underline; text-underline-offset: 3px; }
        .stat-art { display: flex; height: 190px; align-items: flex-end; justify-content: center; }
        .stat-art img { max-height: 100%; width: auto; object-fit: contain; transition: transform .25s ease; }
        .stat-art-verissimo { height: 250px; }
        .stat-row:hover .stat-art img { transform: translateY(-4px); }
        .sessao-tabela { margin: 0 0 38px; scroll-margin-top: 24px; }
        .sessao-tabela h2 { margin: 0; font-family: Georgia, serif; }
        .secao-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .btn-secundario { background: #222; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #fff; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background: #222; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        tbody tr { transition: background-color .18s ease; }
        tbody tr:hover { background: #f1f1f1; }
        .acoes a { margin: 0 5px; font-size: 18px; text-decoration: none; }
        .tag-admin { margin-left: 5px; padding: 3px 8px; border-radius: 12px; background: #0056b3; color: #fff; font-size: 11px; vertical-align: middle; }
        .tabela-wrap { overflow-x: auto; }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes hero-drift {
            from { transform: scale(1); }
            to { transform: scale(1.045); }
        }
        @media (max-width: 650px) {
            .admin-hero { min-height: 230px; }
            .admin-main { padding: 24px 16px 40px; }
            .dashboard { gap: 18px; }
            .stat-row { min-height: 145px; gap: 14px; }
            .stat-card { min-height: 125px; padding: 18px; }
            .stat-card h2 { font-size: 1rem; }
            .stat-card .numero { font-size: 1.8rem; }
            .stat-art { height: 145px; }
            .stat-art-verissimo { height: 190px; }
            .admin-top-actions { align-items: flex-start; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
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
    <div class="header-right"><span class="user-pill"><?php echo htmlspecialchars($nome_admin); ?></span></div>
</header>

<section class="admin-hero">
    <div class="admin-hero-content">
        <h1>Painel do Administrador</h1>
        <p>Bem-vindo de volta, Senhor(a) <strong><?php echo htmlspecialchars($nome_admin); ?></strong>.</p>
    </div>
</section>

<main class="admin-main">
    <div class="admin-top-actions">
        <a href="Index.php" class="btn btn-voltar">&larr; Voltar ao site</a>
        <a href="?logout=1" class="btn btn-logout">Encerrar sessão</a>
    </div>

    <section class="dashboard" aria-label="Resumo do sistema">
        <div class="stat-row">
            <a class="stat-card js-reveal" href="#usuarios">
                <h2>Total de jogadores</h2>
                <p class="numero" data-total="<?php echo (int) $tot_jogadores; ?>"><?php echo (int) $tot_jogadores; ?></p>
                <span class="stat-link">Visualizar jogadores</span>
            </a>
            <div class="stat-art stat-art-verissimo js-reveal"><img src="verissimogif.gif" alt="Veríssimo"></div>
        </div>
        <div class="stat-row">
            <a class="stat-card js-reveal" href="#campanhas">
                <h2>Campanhas ativas</h2>
                <p class="numero" data-total="<?php echo (int) $tot_campanhas; ?>"><?php echo (int) $tot_campanhas; ?></p>
                <span class="stat-link">Visualizar campanhas</span>
            </a>
            <div class="stat-art js-reveal"><img src="agathaquadrada.webp" alt="Ilustração de personagem do D20 Realitas"></div>
        </div>
        <div class="stat-row">
            <a class="stat-card js-reveal" href="#fichas">
                <h2>Fichas criadas</h2>
                <p class="numero" data-total="<?php echo (int) $tot_fichas; ?>"><?php echo (int) $tot_fichas; ?></p>
                <span class="stat-link">Visualizar fichas</span>
            </a>
            <div class="stat-art js-reveal"><img src="ad.png" alt="Ilustração de personagem do D20 Realitas"></div>
        </div>
    </section>

    <!-- TABELA DE USUÁRIOS -->
    <section class="sessao-tabela js-reveal" id="usuarios">
        <div class="secao-heading">
            <h2>Usuários do Sistema</h2>
            <a href="cadastrar_admin.php" class="btn btn-secundario">+ Novo Administrador</a>
        </div>
        
        <?php
        $sql_users = "SELECT * FROM usuario ORDER BY id_usuario DESC";
        $resultado_users = mysqli_query($conexao, $sql_users);

        if ($resultado_users && mysqli_num_rows($resultado_users) > 0) {
            echo "<div class='tabela-wrap'><table>";
            echo "<tr> <th> ID </th> <th> NOME </th> <th> E-MAIL </th> <th> OPÇÕES </th> </tr>";

            while ($user = mysqli_fetch_assoc($resultado_users)) { 
                $email = !empty($user['email']) ? htmlspecialchars($user['email']) : 'Sem e-mail';
                $isAdmin = ($user['tipo_usuario'] === 'admin') ? "<span class='tag-admin'>ADMIN</span>" : "";

                echo "<tr>";
                echo "<td> {$user['id_usuario']} </td>"; 
                echo "<td> " . htmlspecialchars($user['nome_usuario']) . " $isAdmin </td>";
                echo "<td> {$email} </td>";
                
                echo "<td class='acoes'>";
                echo "<a href='editar.php?id={$user['id_usuario']}' title='Editar'>✏️</a>";
                if ($user['id_usuario'] != $id_usuario_logado) {
                    echo "<a href='?deletar={$user['id_usuario']}' title='Deletar' onclick=\"return confirm('Apagar o utilizador " . htmlspecialchars($user['nome_usuario']) . " permanentemente?');\">🗑️</a>";
                }
                echo "</td>"; 
                echo "</tr>";
            }
            echo "</table></div>";
        } else {
            echo "<p>Nenhum utilizador encontrado.</p>";
        }
        ?>
    </section>

    <!-- TABELA DE CAMPANHAS -->
    <section class="sessao-tabela js-reveal" id="campanhas">
        <h2>Campanhas em Andamento</h2>
        
        <?php
        $sql_camps = "SELECT c.id_campanha, c.nome_campanha, u.nome_usuario AS mestre 
                      FROM campanha c 
                      JOIN usuario u ON c.id_usuario = u.id_usuario 
                      ORDER BY c.id_campanha DESC";
        $resultado_camps = mysqli_query($conexao, $sql_camps);

        if ($resultado_camps && mysqli_num_rows($resultado_camps) > 0) {
            echo "<div class='tabela-wrap'><table>";
            echo "<tr> <th> ID </th> <th> NOME DA CAMPANHA </th> <th> MESTRE </th> <th> OPÇÕES </th> </tr>";

            while ($camp = mysqli_fetch_assoc($resultado_camps)) { 
                echo "<tr>";
                echo "<td> {$camp['id_campanha']} </td>"; 
                echo "<td><strong>" . htmlspecialchars($camp['nome_campanha']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($camp['mestre']) . "</td>";
                
                echo "<td class='acoes'>";
                echo "<a href='detalhesCampanha.php?id={$camp['id_campanha']}' target='_blank' title='Ver Campanha'>👁️</a>"; 
                echo "<a href='?deletar_campanha={$camp['id_campanha']}' title='Deletar Campanha' onclick=\"return confirm('Tem a certeza que deseja APAGAR a campanha " . htmlspecialchars($camp['nome_campanha']) . " e remover todos os jogadores dela?');\">🗑️</a>";
                echo "</td>"; 
                echo "</tr>";
            }
            echo "</table></div>";
        } else {
            echo "<p style='color: #666; margin-top: 10px;'>Nenhuma campanha a decorrer no momento.</p>";
        }
        ?>
    </section>

    <section class="sessao-tabela js-reveal" id="fichas">
        <h2>Fichas Criadas</h2>
        <?php
        $sql_fichas = "SELECT f.id_ficha, f.nome_personagem, f.classe_ou_trilha, u.nome_usuario AS jogador
                       FROM ficha_personagem f
                       JOIN usuario u ON f.id_usuario = u.id_usuario
                       ORDER BY f.id_ficha DESC";
        $resultado_fichas = mysqli_query($conexao, $sql_fichas);

        if ($resultado_fichas && mysqli_num_rows($resultado_fichas) > 0) {
            echo "<div class='tabela-wrap'><table>";
            echo "<tr><th>ID</th><th>PERSONAGEM</th><th>CLASSE</th><th>JOGADOR</th></tr>";
            while ($ficha = mysqli_fetch_assoc($resultado_fichas)) {
                echo "<tr>";
                echo "<td>" . (int) $ficha['id_ficha'] . "</td>";
                echo "<td>" . htmlspecialchars($ficha['nome_personagem']) . "</td>";
                echo "<td>" . htmlspecialchars($ficha['classe_ou_trilha'] ?: 'Sem classe') . "</td>";
                echo "<td>" . htmlspecialchars($ficha['jogador']) . "</td>";
                echo "</tr>";
            }
            echo "</table></div>";
        } else {
            echo "<p style='color:#666; margin-top:10px;'>Nenhuma ficha criada ainda.</p>";
        }
        ?>
    </section>

</main>

<script src="assets/admin.min.js" defer></script>

</body>
</html>