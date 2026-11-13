<?php
// Lista as campanhas do usuário, separando as que ele mestra das que participa.
session_start();
require_once 'conecta.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = (int) $_SESSION['id_usuario'];

// Busca o nome do usuário logado para exibir no cabeçalho
$nome_exibir = 'Agente';
$res_user = mysqli_query($conexao, "SELECT nome_usuario FROM usuario WHERE id_usuario = $id_usuario");
if ($res_user && $u = mysqli_fetch_assoc($res_user)) {
    $nome_exibir = $u['nome_usuario'];
}

// 1) Campanhas que o usuário MESTRA (criou) + foto + quantidade de jogadores
$sql_mestre = "SELECT c.id_campanha, c.nome_campanha, c.descricao_campanha, c.foto_campanha,
                      (SELECT COUNT(*) FROM campanha_fichas cf WHERE cf.id_campanha = c.id_campanha) AS total_jogadores
               FROM campanha c
               WHERE c.id_usuario = $id_usuario
               ORDER BY c.id_campanha DESC";
$res_mestre = mysqli_query($conexao, $sql_mestre);

// 2) Campanhas em que o usuário JOGA (entrou por convite com uma ficha) + foto
$sql_jogador = "SELECT c.id_campanha, c.nome_campanha, c.descricao_campanha, c.foto_campanha,
                       u.nome_usuario AS nome_mestre, f.nome_personagem
                FROM campanha_fichas cf
                JOIN campanha c ON c.id_campanha = cf.id_campanha
                JOIN usuario u ON u.id_usuario = c.id_usuario
                LEFT JOIN ficha_personagem f ON f.id_ficha = cf.id_ficha
                WHERE cf.id_usuario = $id_usuario
                ORDER BY c.id_campanha DESC";
$res_jogador = mysqli_query($conexao, $sql_jogador);

function resumo($texto, $max = 95) {
    $texto = trim((string) $texto);
    return mb_strlen($texto) > $max ? mb_substr($texto, 0, $max) . '...' : $texto;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campanhas - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">
    <style>
        body {
            background: #f3f3f3;
            color: #171717;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 400;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        body, a, button, input, p, h1, h2, h3, h4, span, strong {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h1, h2, h3, h4, .pagina, .secao-titulo, .item-card h3, .modal-box h3 {
            font-weight: 400 !important;
        }

        strong {
            font-weight: 400;
        }

        .container {
            max-width: 1150px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
            box-sizing: border-box;
            flex: 1;
        }

        .cabecalho {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e3e3e3;
            padding-bottom: 20px;
            margin-bottom: 35px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .pagina {
            font-size: 32px;
            margin: 0;
            color: #111;
            letter-spacing: 0;
            font-weight: 400;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.2;
        }

        .barra-botoes {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-criar {
            background-color: #b30d0d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 400;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
            box-shadow: 0 4px 12px rgba(179, 13, 13, 0.18);
        }

        .btn-criar:hover {
            background-color: #8d0b0b;
            transform: translateY(-1px);
        }

        .btn-entrar {
            background-color: #fff;
            color: #222;
            border: 1px solid #d9d9d9;
            box-shadow: none;
        }

        .btn-entrar:hover {
            background-color: #f6f6f6;
            border-color: #c9c9c9;
        }

        .secao-titulo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            color: #222;
            margin-top: 30px;
            margin-bottom: 20px;
            font-weight: 400;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-mestre {
            background-color: #fff0f0;
            color: #b30d0d;
            border: 1px solid #e7b4b4;
        }

        .badge-jogador {
            background-color: #f5f5f5;
            color: #5a5a5a;
            border: 1px solid #d7d7d7;
        }

        .lista-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .item-card {
            background: #fff;
            border: 1px solid #e4e4e4;
            border-top: 4px solid #b30d0d;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .item-card.jogador {
            border-top-color: #7a7a7a;
        }

        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
        }

        .img-campanha-card {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #f3f3f3;
            display: block;
            border: 1px solid #ececec;
        }

        .placeholder-capa {
            width: 100%;
            height: 150px;
            border-radius: 8px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f6f6f6, #ececec);
            border: 1px solid #e5e5e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #b2b2b2;
        }

        .item-card h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
            color: #111;
            font-weight: 400;
        }

        .sub {
            font-size: 14px;
            color: #555;
            margin: 0 0 10px 0;
            line-height: 1.5;
        }

        .sub strong {
            color: #222;
        }

        .acoes {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #efefef;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-abrir,
        .btn-excluir {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 400;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .btn-abrir {
            background-color: #b30d0d;
            color: #fff;
        }

        .btn-abrir:hover {
            background-color: #8d0b0b;
            transform: translateY(-1px);
        }

        .btn-excluir {
            background-color: #f1f1f1;
            color: #5a5a5a;
            border: 1px solid #d9d9d9;
        }

        .btn-excluir:hover {
            background-color: #eaeaea;
            transform: translateY(-1px);
        }

        .card-vazio {
            grid-column: 1 / -1;
            background: #fff;
            border: 1px dashed #d9d9d9;
            border-radius: 10px;
            padding: 45px 20px;
            text-align: center;
            box-shadow: inset 0 0 25px rgba(0, 0, 0, 0.02);
        }

        .icone-vazio {
            font-size: 42px;
            margin-bottom: 12px;
            opacity: 0.7;
        }

        .card-vazio p {
            font-size: 17px;
            color: #222;
            margin: 0 0 6px 0;
            font-weight: 400;
        }

        .dica-vazia {
            font-size: 14px;
            color: #666;
            display: block;
            margin-bottom: 18px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(2px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.aberto {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border: 1px solid #e7e7e7;
            border-top: 4px solid #b30d0d;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .modal-box h3 {
            margin: 0 0 15px 0;
            color: #111;
            font-size: 20px;
        }

        .modal-box input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            background: #fafafa;
            border: 1px solid #d9d9d9;
            border-radius: 6px;
            color: #111;
            box-sizing: border-box;
            font-size: 14px;
        }

        .modal-box input:focus {
            outline: none;
            border-color: #b30d0d;
        }

        .btn-verde {
            background-color: #1f8b4d;
        }

        .btn-verde:hover {
            background-color: #17733f;
        }

        .btn-cinza {
            background-color: #efefef;
            color: #111;
        }

        .btn-cinza:hover {
            background-color: #e3e3e3;
        }
    </style>
</head>
<body>

    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav" aria-label="Navegação principal">
            <a href="fichas.php">Fichas</a>
            <a href="campanhas.php" class="active">Campanhas</a>
            <a href="regras.php">Regras</a>
        </nav>
        <div class="header-right">
            <span class="user-pill">Olá, <?php echo htmlspecialchars($nome_exibir); ?>!</span>
        </div>
    </header>

    <div class="container">

        <div class="cabecalho">
            <h1 class="pagina">Operações & Campanhas</h1>
            <div class="barra-botoes">
                <a href="criarCampanha.php" class="btn-criar">+ Criar Nova Campanha</a>
                <button type="button" class="btn-criar btn-entrar" onclick="abrirModal()">Entrar com Convite</button>
            </div>
        </div>

        <!-- ===== COMO MESTRE ===== -->
        <h2 class="secao-titulo"><span class="badge badge-mestre">Mestre</span> Campanhas que eu mestro</h2>
        <div class="lista-cards">
            <?php if ($res_mestre && mysqli_num_rows($res_mestre) > 0): ?>
                <?php while ($c = mysqli_fetch_assoc($res_mestre)): ?>
                    <div class="item-card">
                        <div>
                            <?php if (!empty($c['foto_campanha']) && file_exists('uploads/' . $c['foto_campanha'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($c['foto_campanha']); ?>" alt="Banner da Campanha" class="img-campanha-card">
                            <?php else: ?>
                                <div class="placeholder-capa">🗺️</div>
                            <?php endif; ?>

                            <h3><?php echo htmlspecialchars($c['nome_campanha']); ?></h3>
                            <p class="sub"><?php echo htmlspecialchars(resumo($c['descricao_campanha'])); ?></p>
                            <p class="sub">👥 <strong><?php echo (int) $c['total_jogadores']; ?></strong> agente(s) na mesa</p>
                        </div>
                        <div class="acoes">
                            <a href="detalhesCampanha.php?id=<?php echo (int) $c['id_campanha']; ?>" class="btn-abrir">Abrir Painel</a>
                            <form action="deletarCampanha.php" method="POST" onsubmit="return confirm('Deseja realmente excluir esta campanha?');" style="margin:0;">
                                <input type="hidden" name="id_campanha" value="<?php echo (int) $c['id_campanha']; ?>">
                                <button type="submit" class="btn-excluir">Excluir</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card-vazio">
                    <div class="icone-vazio">📂</div>
                    <p>Nenhuma operação ativa no seu comando</p>
                    <span class="dica-vazia">Você ainda não criou nenhuma campanha. Inicie uma nova investigação para reunir seus jogadores.</span>
                    <a href="criarCampanha.php" class="btn-criar">+ Criar Minha Primeira Campanha</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===== COMO JOGADOR ===== -->
        <h2 class="secao-titulo"><span class="badge badge-jogador">Agente</span> Campanhas em que eu jogo</h2>
        <div class="lista-cards">
            <?php if ($res_jogador && mysqli_num_rows($res_jogador) > 0): ?>
                <?php while ($c = mysqli_fetch_assoc($res_jogador)): ?>
                    <div class="item-card jogador">
                        <div>
                            <?php if (!empty($c['foto_campanha']) && file_exists('uploads/' . $c['foto_campanha'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($c['foto_campanha']); ?>" alt="Banner da Campanha" class="img-campanha-card">
                            <?php else: ?>
                                <div class="placeholder-capa">Sem capa</div>
                            <?php endif; ?>

                            <h3><?php echo htmlspecialchars($c['nome_campanha']); ?></h3>
                            <p class="sub">
                                Mestre: <strong><?php echo htmlspecialchars($c['nome_mestre']); ?></strong><br>
                                Agente: <strong><?php echo htmlspecialchars($c['nome_personagem'] ?? 'Sem ficha vinculada'); ?></strong>
                            </p>
                        </div>
                        <div class="acoes">
                            <a href="detalhesCampanha.php?id=<?php echo (int) $c['id_campanha']; ?>" class="btn-abrir">Entrar na Mesa</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card-vazio">
                    <p>Nenhuma missão atribuída</p>
                    <span class="dica-vazia">Você ainda não entrou em nenhuma campanha como jogador. Peça o link de convite ao seu mestre!</span>
                    <button type="button" class="btn-criar btn-entrar" onclick="abrirModal()">Inserir Código de Convite</button>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- POP-UP: COLAR LINK DE CONVITE -->
    <div id="modalLink" class="modal-overlay" onclick="if (event.target === this) fecharModal();">
        <div class="modal-box">
            <h3>Acesso à Operação</h3>
            <p style="color: #8a8a93; font-size: 13px; margin-bottom: 15px;">Cole o link ou código fornecido pelo mestre da mesa.</p>
            <input type="text" id="linkConvite" placeholder="Ex: http://.../convite.php?t=abc123">
            <div class="barra-botoes" style="justify-content:center;">
                <button type="button" class="btn-abrir btn-verde" onclick="irParaLink()">Confirmar</button>
                <button type="button" class="btn-abrir btn-cinza" onclick="fecharModal()">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('modalLink').classList.add('aberto');
            document.getElementById('linkConvite').focus();
        }

        function fecharModal() {
            document.getElementById('modalLink').classList.remove('aberto');
        }

        function irParaLink() {
            var texto = document.getElementById('linkConvite').value.trim();
            if (texto === '') {
                alert('Por favor, cole o link antes de clicar em confirmar.');
                return;
            }

            var token = texto;
            try {
                var t = new URL(texto).searchParams.get('t');
                if (t) token = t;
            } catch (e) { /* usa o texto diretamente como token */ }

            window.location.href = 'convite.php?t=' + encodeURIComponent(token);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') fecharModal();
        });
    </script>

    <script src="assets/site.min.js" defer></script>
</body>
</html>