<?php
session_start();

// Redireciona quem ainda não iniciou uma sessão.
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conecta.php';

$id_usuario_logado = (int) $_SESSION['id_usuario'];
$id_campanha = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Volta para a lista se o identificador não for válido.
if ($id_campanha === 0) {
    header("Location: campanhas.php");
    exit();
}

// Carrega os dados da campanha e o nome do mestre.
$sql_campanha = "SELECT c.*, u.nome_usuario AS nome_mestre 
                 FROM campanha c 
                 JOIN usuario u ON c.id_usuario = u.id_usuario 
                 WHERE c.id_campanha = $id_campanha";
$resultado_campanha = mysqli_query($conexao, $sql_campanha);

if (!$resultado_campanha || mysqli_num_rows($resultado_campanha) == 0) {
    die("Campanha não encontrada.");
}

$campanha = mysqli_fetch_assoc($resultado_campanha);

// O mestre usa a página de detalhes própria.
if ((int) $campanha['id_usuario'] === $id_usuario_logado) {
    header("Location: detalhesMestre.php?id=" . $id_campanha);
    exit();
}

// Carrega as fichas e separa a do usuário das demais.
$sql_fichas = "SELECT cf.*, f.id_ficha, f.nome_personagem, f.classe_ou_trilha, u.nome_usuario, u.id_usuario AS id_dono 
               FROM campanha_fichas cf
               JOIN ficha_personagem f ON cf.id_ficha = f.id_ficha
               JOIN usuario u ON cf.id_usuario = u.id_usuario
               WHERE cf.id_campanha = $id_campanha";
$res_fichas = mysqli_query($conexao, $sql_fichas);

$minha_ficha = null;
$outros_agentes = [];

if ($res_fichas) {
    while ($f = mysqli_fetch_assoc($res_fichas)) {
        if ((int)$f['id_dono'] === $id_usuario_logado) {
            $minha_ficha = $f;
        } else {
            $outros_agentes[] = $f;
        }
    }
}

// Usa uma imagem padrão quando a campanha não tem capa.
$imagem_mostrar = $campanha['foto_campanha'] ?: "https://via.placeholder.com/900x350?text=Sem+Imagem+Cadastrada";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($campanha['nome_campanha']); ?> - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">
    <style>
        body { background-color: #f0f0f0; margin: 0; font-family: sans-serif; color: #333; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        .btn-voltar { display: inline-block; background-color: #555; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; margin-bottom: 20px; font-weight: bold; }
        
        /* Organiza o conteúdo em duas colunas. */
        .grid-operacao { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .grid-operacao { grid-template-columns: 1fr; } }

        /* Estilos dos painéis. */
        .painel { background: #fff; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 5px solid #28a745; }
        .painel-azul { border-left-color: #007bff; }
        .painel-vermelho { border-left-color: #dc3545; }

        .tag-jogador { background-color: #28a745; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        
        .capa-campanha { width: 100%; height: 350px; object-fit: cover; border-radius: 6px; margin: 15px 0; border: 1px solid #ddd; display: block; }
        
        /* Ficha do usuário. */
        .card-minha-ficha { background: #f8f9fa; border: 1px solid #e9ecef; border-left: 4px solid #28a745; border-radius: 6px; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .card-minha-ficha h3 { margin: 0 0 5px 0; color: #111; }
        .btn-abrir-ficha { background-color: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .btn-abrir-ficha:hover { background-color: #218838; }

        /* Lista de personagens dos outros jogadores. */
        .lista-jogadores { margin-top: 15px; list-style: none; padding: 0; }
        .item-jogador { background: #f8f9fa; padding: 12px 15px; border-radius: 6px; margin-bottom: 8px; border: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .link-personagem { color: #111; text-decoration: none; font-weight: bold; }
        
        /* Rolador e exibição do resultado. */
        .dado-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .btn-dado { background: #e9ecef; border: 1px solid #ced4da; color: #333; padding: 10px 0; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .btn-dado:hover { background: #dee2e6; }
        .btn-dado-destaque { background: #dc3545; color: white; border-color: #c82333; }
        .btn-dado-destaque:hover { background: #bd2130; }
        
        .resultado-dado { background: #f8f9fa; border: 1px dashed #ccc; padding: 15px; text-align: center; border-radius: 6px; margin-top: 15px; }
        .resultado-valor { font-size: 32px; font-weight: bold; color: #28a745; margin: 10px 0; }
        .resultado-texto { font-size: 13px; color: #666; }
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
        <a href="campanhas.php" class="btn-voltar">< Voltar</a>

        <div class="grid-operacao">
            <div class="coluna-principal">
                <!-- Informações da campanha. -->
                <div class="painel">
                    <span class="tag-jogador">JOGADOR</span>
                    <h2 style="margin-top:10px; margin-bottom:5px;"><?php echo htmlspecialchars($campanha['nome_campanha']); ?></h2>
                    <p style="color:#666; margin-top:0;">Mestre: <strong><?php echo htmlspecialchars($campanha['nome_mestre']); ?></strong></p>
                    
                    <img src="<?php echo htmlspecialchars($imagem_mostrar); ?>" alt="Capa da Campanha" class="capa-campanha">

                    <p><?php echo nl2br(htmlspecialchars($campanha['descricao_campanha'] ?? 'Sem descrição.')); ?></p>
                </div>

                <!-- Ficha vinculada ao usuário. -->
                <div class="painel">
                    <h3 style="margin-top:0;">Meu Personagem</h3>
                    <?php if ($minha_ficha): ?>
                        <div class="card-minha-ficha">
                            <div>
                                <h3><?php echo htmlspecialchars($minha_ficha['nome_personagem']); ?></h3>
                                <span style="color:#666; font-size:14px;"><?php echo htmlspecialchars($minha_ficha['classe_ou_trilha'] ?: 'Sem classe'); ?></span>
                            </div>
                            <a href="ficha_criada.php?id=<?php echo $minha_ficha['id_ficha']; ?>" class="btn-abrir-ficha" target="_blank">Abrir Ficha</a>
                        </div>
                    <?php else: ?>
                        <p style="color:#dc3545;">Você ainda não tem uma ficha vinculada a esta campanha.</p>
                    <?php endif; ?>
                </div>

                <!-- Personagens dos demais jogadores. -->
                <div class="painel painel-azul">
                    <h3 style="margin-top:0;">Personagens na Mesa</h3>
                    <?php if (count($outros_agentes) > 0): ?>
                        <ul class="lista-jogadores">
                            <?php foreach ($outros_agentes as $agente): ?>
                                <li class="item-jogador">
                                    <div>
                                        <span class="link-personagem"><?php echo htmlspecialchars($agente['nome_personagem']); ?></span>
                                        <span style="color:#777;">(<?php echo htmlspecialchars($agente['classe_ou_trilha'] ?: 'Sem classe'); ?>)</span>
                                    </div>
                                    <span style="font-size:13px; color:#555;">Jogador: <?php echo htmlspecialchars($agente['nome_usuario']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="color:#777;">Nenhum outro personagem na mesa ainda.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rolador de dados. -->
            <div class="coluna-lateral">
                <div class="painel painel-vermelho" style="position: sticky; top: 20px;">
                    <h3 style="margin-top:0;">Rolador de Dados</h3>
                    <div class="dado-grid">
                        <button class="btn-dado" onclick="rolarDado(4, 'd4')">d4</button>
                        <button class="btn-dado" onclick="rolarDado(6, 'd6')">d6</button>
                        <button class="btn-dado" onclick="rolarDado(8, 'd8')">d8</button>
                        <button class="btn-dado" onclick="rolarDado(10, 'd10')">d10</button>
                        <button class="btn-dado" onclick="rolarDado(12, 'd12')">d12</button>
                        <button class="btn-dado btn-dado-destaque" onclick="rolarDado(20, 'd20')">d20</button>
                        <button class="btn-dado" onclick="rolarDado(100, 'd100')" style="grid-column: span 3;">d100 (Porcentagem)</button>
                    </div>

                    <div class="resultado-dado" id="caixa-resultado" style="display: none;">
                        <div class="resultado-texto" id="texto-dado">Rolou</div>
                        <div class="resultado-valor" id="valor-resultado">--</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Gera e exibe o resultado do dado escolhido. -->
    <script>
        function rolarDado(faces, nomeDado) {
            document.getElementById('caixa-resultado').style.display = 'block';
            const elValor = document.getElementById('valor-resultado');
            const elTexto = document.getElementById('texto-dado');
            
            elTexto.innerText = "Rolando " + nomeDado + "...";
            elValor.style.color = "#888";
            elValor.innerText = "...";

            setTimeout(() => {
                const resultado = Math.floor(Math.random() * faces) + 1;
                elTexto.innerText = "Resultado (" + nomeDado + ")";
                
                if (faces === 20 && resultado === 20) {
                    elValor.style.color = "#d39e00";
                } else if (faces === 20 && resultado === 1) {
                    elValor.style.color = "#dc3545";
                } else {
                    elValor.style.color = "#28a745";
                }
                
                elValor.innerText = resultado;
            }, 300);
        }
    </script>
<script src="assets/site.min.js" defer></script>
</body>
</html>