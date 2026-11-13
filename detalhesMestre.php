<?php
session_start();

// 1. Verificação de login
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conecta.php'; 

$id_usuario_logado = (int) $_SESSION['id_usuario'];
$id_campanha = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_campanha <= 0) {
    header("Location: campanhas.php");
    exit();
}

// 2. Busca os dados da campanha
$sql_campanha = "SELECT * FROM campanha WHERE id_campanha = $id_campanha";
$resultado_campanha = mysqli_query($conexao, $sql_campanha);

if (!$resultado_campanha || mysqli_num_rows($resultado_campanha) === 0) {
    header("Location: campanhas.php");
    exit();
}

$campanha = mysqli_fetch_assoc($resultado_campanha);
$id_mestre_campanha = (int) $campanha['id_usuario'];

// 3. TRAVA DE SEGURANÇA: Apenas o Mestre acede a esta página
if ($id_usuario_logado !== $id_mestre_campanha) {
    header("Location: detalhesCampanha.php?id=" . $id_campanha);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'remover_ficha') {
    $id_ficha_remover = isset($_POST['id_ficha']) ? (int) $_POST['id_ficha'] : 0;
    $id_campanha_target = isset($_POST['id_campanha']) ? (int) $_POST['id_campanha'] : $id_campanha;

    if ($id_ficha_remover > 0 && $id_campanha_target > 0) {
        $sql_delete = "DELETE FROM campanha_fichas WHERE id_campanha = $id_campanha_target AND id_ficha = $id_ficha_remover";
        mysqli_query($conexao, $sql_delete);
    }

    // Redireciona para atualizar a lista
    header("Location: detalhesMestre.php?id=" . $id_campanha_target);
    exit();
}

// 5. Busca fichas e calcula vida total da mesa
$sql_fichas = "SELECT cf.*, f.nome_personagem, f.pv_atual, f.pv_maximo, u.nome_usuario 
               FROM campanha_fichas cf
               JOIN ficha_personagem f ON cf.id_ficha = f.id_ficha
               JOIN usuario u ON cf.id_usuario = u.id_usuario
               WHERE cf.id_campanha = $id_campanha";
$res_fichas = mysqli_query($conexao, $sql_fichas);

$total_jogadores = $res_fichas ? mysqli_num_rows($res_fichas) : 0;
$pv_atual_mesa = 0;
$pv_max_mesa = 0;

$fichas_array = [];
if ($res_fichas) {
    while ($row = mysqli_fetch_assoc($res_fichas)) {
        $pv_atual_mesa += (int) $row['pv_atual'];
        $pv_max_mesa += (int) $row['pv_maximo'];
        $fichas_array[] = $row;
    }
}

// Monta o link de convite dinâmico
$protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$link_convite = $protocolo . "://" . $_SERVER['HTTP_HOST'] . "/tecece/convite.php?t=" . urlencode($campanha['token_convite'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Mestre - <?php echo htmlspecialchars($campanha['nome_campanha']); ?></title>
    <link rel="stylesheet" href="home.css">
    <style>
        body { background-color: #f0f0f0; margin: 0; font-family: sans-serif; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .painel { background: #fff; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 5px solid #c00000; }
        .tag-mestre { background-color: #c00000; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .btn-voltar { display: inline-block; background-color: #555; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin-bottom: 20px; font-weight: bold; }
        .estatisticas-mesa { display: flex; gap: 15px; margin-top: 20px; }
        .card-stat { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; min-width: 120px; border: 1px solid #ddd; }
        .card-stat h3 { margin: 0 0 5px 0; font-size: 22px; color: #111; }
        .card-stat p { margin: 0; font-size: 12px; color: #666; }
        .input-link { width: 100%; padding: 10px; margin-top: 10px; border: 1px solid #ccc; border-radius: 5px; background: #e9ecef; font-family: monospace; cursor: pointer; box-sizing: border-box; }
        .lista-jogadores { margin-top: 15px; list-style: none; padding: 0; }
        .item-jogador { background: #f8f9fa; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; border: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .banner-campanha { width: 100%; max-height: 250px; object-fit: cover; border-radius: 6px; margin-top: 15px; }
        .btn-acao { background-color: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn-acao:hover { background-color: #218838; }
        
        .btn-remover { background-color: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; cursor: pointer; }
        .btn-remover:hover { background-color: #c82333; }
    </style>
</head>
<body>

    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav">
            <a href="fichas.php">Fichas</a>
            <a href="campanhas.php" class="active">Campanhas</a>
            <a href="regras.php">Regras</a>
        </nav>
    </header>

    <div class="container">
        <a href="campanhas.php" class="btn-voltar">&lt; Voltar</a>

        <!-- PAINEL PRINCIPAL -->
        <div class="painel">
            <span class="tag-mestre">PAINEL DO MESTRE</span>
            <h2 style="margin-top:10px; margin-bottom:5px;"><?php echo htmlspecialchars($campanha['nome_campanha']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($campanha['descricao_campanha'] ?? 'Sem descrição.')); ?></p>

            <?php if (!empty($campanha['foto_campanha'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($campanha['foto_campanha']); ?>" alt="Banner da Campanha" class="banner-campanha">
            <?php endif; ?>

            <div class="estatisticas-mesa">
                <div class="card-stat">
                    <h3><?php echo $total_jogadores; ?></h3>
                    <p>Jogadores</p>
                </div>
                <div class="card-stat">
                    <h3><?php echo $pv_atual_mesa . '/' . $pv_max_mesa; ?></h3>
                    <p>PV da Mesa</p>
                </div>
            </div>
        </div>

        <!-- FORMULÁRIO PARA ALTERAR A FOTO DA CAMPANHA -->
        <div class="painel" style="border-left-color: #17a2b8;">
            <h3 style="margin-top:0;">Foto da Campanha</h3>
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_campanha" value="<?php echo $id_campanha; ?>">
                <input type="file" name="arquivo" accept="image/*" required>
                <br><br>
                <button type="submit" class="btn-acao">Enviar Foto</button>
            </form>
        </div>

        <!-- LINK DE CONVITE -->
        <div class="painel" style="border-left-color: #333;">
            <h3 style="margin-top:0;">Convidar Jogadores</h3>
            <p style="font-size:14px; color:#555;">Envie este link para os jogadores entrarem na sua mesa:</p>
            <input type="text" class="input-link" value="<?php echo htmlspecialchars($link_convite); ?>" readonly onclick="this.select();">
        </div>

        <!-- LISTA DOS JOGADORES -->
        <div class="painel" style="border-left-color: #007bff;">
            <h3 style="margin-top:0;">Jogadores na Mesa</h3>
            <?php if (count($fichas_array) > 0): ?>
                <ul class="lista-jogadores">
                    <?php foreach ($fichas_array as $f): ?>
                        <li class="item-jogador">
                            <div>
                                <strong><?php echo htmlspecialchars($f['nome_personagem']); ?></strong> 
                                <span style="font-size:13px; color:#555;">(<?php echo htmlspecialchars($f['nome_usuario']); ?>)</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <strong style="color: #c00000;"><?php echo $f['pv_atual'] . '/' . $f['pv_maximo']; ?> PV</strong>
                                
                                <!-- FORMULÁRIO DE REMOÇÃO (Envia para a própria página) -->
                                <form action="detalhesMestre.php?id=<?php echo $id_campanha; ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este personagem da campanha?');" style="margin:0;">
                                    <input type="hidden" name="acao" value="remover_ficha">
                                    <input type="hidden" name="id_ficha" value="<?php echo (int) $f['id_ficha']; ?>">
                                    <button type="submit" class="btn-remover">Remover</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color:#777;">Nenhum jogador entrou nesta mesa ainda.</p>
            <?php endif; ?>
        </div>
    </div>

<script src="assets/site.min.js" defer></script>
</body>
</html>