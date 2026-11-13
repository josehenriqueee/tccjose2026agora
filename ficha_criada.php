<?php
session_start();
require_once 'conecta.php';

// 1. Verificação de login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = (int) $_SESSION['id_usuario'];

// 2. Validação do ID da ficha via GET
if (!isset($_GET['id']) || $_GET['id'] == '') {
    echo "Erro: ID da ficha não fornecido.";
    exit();
}

$id_ficha = (int) $_GET['id'];

// 3. Processamento do formulário de Perícias (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['treino'])) {
    $treino = array();
    if (isset($_POST['treino'])) {
        $treino = $_POST['treino'];
    }

    $outros = array();
    if (isset($_POST['outros'])) {
        $outros = $_POST['outros'];
    }

    $pericias_salvar = array();

    foreach ($treino as $nome => $valor_t) {
        $val_t = (int) $valor_t;
        $val_o = 0;
        if (isset($outros[$nome])) {
            $val_o = (int) $outros[$nome];
        }

        if ($val_t > 0 || $val_o != 0) {
            $pericias_salvar[$nome] = array(
                't' => $val_t,
                'o' => $val_o
            );
        }
    }

    foreach ($outros as $nome => $valor_o) {
        $val_o = (int) $valor_o;
        if (!isset($pericias_salvar[$nome]) && $val_o != 0) {
            $pericias_salvar[$nome] = array(
                't' => 0,
                'o' => $val_o
            );
        }
    }

    $json_dados = json_encode($pericias_salvar, JSON_UNESCAPED_UNICODE);
    $json_dados = mysqli_real_escape_string($conexao, $json_dados);

    $sql_update = "UPDATE ficha_personagem SET pericias = '$json_dados' WHERE id_ficha = $id_ficha AND id_usuario = $id_usuario";
    mysqli_query($conexao, $sql_update);

    $mensagem_sucesso = "Perícias salvas com sucesso!";
}

// 4. Busca os dados da ficha
$somente_leitura = false;
$id_campanha_mestre = 0;

$sql = "SELECT * FROM ficha_personagem WHERE id_ficha = $id_ficha AND id_usuario = $id_usuario";
$resultado = mysqli_query($conexao, $sql);

if (mysqli_num_rows($resultado) == 0) {
    $sql_mestre = "SELECT f.*, c.id_campanha 
                   FROM ficha_personagem f
                   JOIN campanha_fichas cf ON cf.id_ficha = f.id_ficha
                   JOIN campanha c ON c.id_campanha = cf.id_campanha
                   WHERE f.id_ficha = $id_ficha AND c.id_usuario = $id_usuario
                   LIMIT 1";
    $resultado = mysqli_query($conexao, $sql_mestre);
    $somente_leitura = true;
}

if (mysqli_num_rows($resultado) == 0) {
    echo "Erro: Ficha não encontrada ou sem permissão de acesso.";
    exit();
}

$ficha = mysqli_fetch_assoc($resultado);

if ($somente_leitura) {
    $id_campanha_mestre = (int) $ficha['id_campanha'];
}

$id_dono = (int) $ficha['id_usuario'];
$sql_dono = "SELECT nome_usuario FROM usuario WHERE id_usuario = $id_dono";
$res_dono = mysqli_query($conexao, $sql_dono);
$user = mysqli_fetch_assoc($res_dono);

$pericias_salvas = '';
if (isset($ficha['pericias'])) {
    $pericias_salvas = $ficha['pericias'];
}

function getPericiaValor($nome, $tipo, $salvo) {
    if (empty($salvo)) {
        return 0;
    }

    if (substr($salvo, 0, 1) == '{') {
        $dados = json_decode($salvo, true);
        if (isset($dados[$nome])) {
            if (is_array($dados[$nome])) {
                if (isset($dados[$nome][$tipo])) {
                    return (int) $dados[$nome][$tipo];
                }
            } else {
                if ($tipo == 't') {
                    return (int) $dados[$nome];
                }
            }
        }
    } else {
        if ($tipo == 't' && strpos($salvo, $nome) !== false) {
            return 5;
        }
    }

    return 0;
}

$pericias = array(
    'Acrobacia+'    => 'AGI',
    'Adestramento*' => 'PRE',
    'Artes*'        => 'PRE',
    'Atletismo'     => 'FOR',
    'Atualidades'   => 'INT',
    'Ciências*'     => 'INT',
    'Crime*+'       => 'AGI',
    'Diplomacia'    => 'PRE',
    'Enganação'     => 'PRE',
    'Fortitude'     => 'VIG',
    'Furtividade+'  => 'AGI',
    'Iniciativa'    => 'AGI',
    'Intimidação'   => 'PRE',
    'Intuição'      => 'PRE',
    'Investigação'  => 'INT',
    'Luta'          => 'FOR',
    'Medicina'      => 'INT',
    'Ocultismo*'    => 'INT',
    'Percepção'     => 'PRE',
    'Pilotagem*'    => 'AGI',
    'Pontaria'      => 'AGI',
    'Proficiência*' => 'INT',
    'Reflexos'      => 'AGI',
    'Religião*'     => 'PRE',
    'Sobrevivência' => 'INT',
    'Tática*'       => 'INT',
    'Tecnologia*'   => 'INT',
    'Vontade'       => 'PRE'
);

// ===== EXTRAÇÃO DOS ATRIBUTOS DA STRING =====
// Procura dentro da string (ex: "AGI: 1, INT: 1...") e separa cada valor. Se não achar, fica 0.
$attr_string = $ficha['atributos'];
$agi = $int = $vig = $pre = $for = 0;

if (preg_match('/AGI:\s*(\d+)/i', $attr_string, $matches)) $agi = $matches[1];
if (preg_match('/INT:\s*(\d+)/i', $attr_string, $matches)) $int = $matches[1];
if (preg_match('/VIG:\s*(\d+)/i', $attr_string, $matches)) $vig = $matches[1];
if (preg_match('/PRE:\s*(\d+)/i', $attr_string, $matches)) $pre = $matches[1];
if (preg_match('/FOR:\s*(\d+)/i', $attr_string, $matches)) $for = $matches[1];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($ficha['nome_personagem']); ?> - D20REALITAS</title>
    <link rel="stylesheet" href="home.css?v=4">
    <style>
        .topo-ficha { display: flex; gap: 15px; background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; align-items: center; }
        .foto-avatar { width: 100px; height: 100px; object-fit: cover; border-radius: 6px; border: 2px solid #cc0000; background: #ddd; }
        .sem-foto { width: 100px; height: 100px; border-radius: 6px; background: #333; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; }
        .info-ficha { display: flex; justify-content: space-between; flex: 1; }
        .btn-salvar { background: #cc0000; color: #fff; border: none; padding: 10px 20px; font-weight: bold; cursor: pointer; border-radius: 4px; width: 100%; margin-top: 15px; }

       /* BANNER SKYSCRAPER ESQUERDA (MAIS PARA BAIXO) */
        .skyscraper-esquerda {
            position: absolute;
            top: 250px;
            left: 20px;
            width: 160px;
            z-index: 99;
            display: flex;
            align-items: flex-start;
        }
        .skyscraper-esquerda img {
            width: 100%;
            height: auto;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        /* LAYOUT DA FICHA */
        .container.ficha-sheet {
            margin-left: 220px; 
            max-width: 1200px; 
        }

        /* DIVISÃO EM COLUNAS */
        .layout-colunas {
            display: grid;
            grid-template-columns: 360px minmax(400px, 1fr);
            grid-template-areas:
                "informacoes pericias"
                "inventario inventario";
            gap: 20px;
            align-items: flex-start;
        }

        .coluna-esquerda {
            grid-area: informacoes;
            width: 100%;
        }

        .coluna-direita {
            grid-area: pericias;
            width: 100%;
        }

        .coluna-inventario {
            grid-area: inventario;
            width: 100%;
            margin-top: 0;
        }

        .coluna-esquerda .barra {
            width: 100%;
            box-sizing: border-box;
        }

        .coluna-esquerda .barra:last-of-type {
            margin-bottom: 0;
        }

        .coluna-direita .caixa-pericias {
            margin-top: 0;
        }

        .tabela-pericias th,
        .tabela-pericias td {
            padding: 6px 8px;
        }

        /* ========================================================
           ESTILOS DO NOVO PENTAGRAMA DE ATRIBUTOS (IMAGEM) 
           ======================================================== */
        .container-atributos-img {
            position: relative;
            width: 100%;
            max-width: 320px; /* Tamanho da imagem no site */
            margin: 0 auto 30px auto; /* Centraliza a imagem e dá espaço embaixo */
        }
        
        .container-atributos-img img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .valor-atributo {
            position: absolute;
            font-size: 2.5em; /* Tamanho do número */
            font-weight: 900;
            color: #cc0000; /* Número em vermelho para combinar com o layout */
            transform: translate(-50%, -50%); 
            /* Sombra preta ao redor do número para destacar no fundo escuro da imagem */
            text-shadow: 2px 2px 0 #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
            z-index: 10;
        }
        
        /* Ajuste fino da posição (top/left) de cada atributo em cima da imagem */
        .pos-agi { top: 28%; left: 50%; }
        .pos-int { top: 49%; left: 82%; }
        .pos-vig { top: 85%; left: 69%; }
        .pos-pre { top: 85%; left: 31%; }
        .pos-for { top: 49%; left: 18%; }


        /* ESTILOS DO BOTÃO DE ROLAR DADO */
        .btn-rolar-dado {
            background: #cc0000;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 1.1em;
            transition: 0.2s;
        }
        .btn-rolar-dado:hover {
            background: #a80000;
            transform: scale(1.1);
        }

        /* TOAST/POPUP DO RESULTADO DO DADO */
        .painel-toast {
            position: fixed;
            bottom: -200px; /* Começa escondido */
            right: 30px;
            width: 250px;
            background: #fff;
            border: 2px solid #cc0000;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            transition: bottom 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            font-family: sans-serif;
        }
        .painel-toast.mostrar {
            bottom: 30px; /* Sobe pra tela */
        }
        .toast-header {
            background: #cc0000;
            color: #fff;
            padding: 10px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
        }
        .toast-header button {
            background: transparent;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .toast-body {
            padding: 15px;
            text-align: center;
        }
        .dado-valor {
            font-size: 3em;
            font-weight: bold;
            color: #cc0000;
            margin-bottom: 5px;
        }
        .dado-detalhes {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
        }
        .dado-total {
            font-size: 1.2em;
            font-weight: bold;
            color: #111;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        /* Responsividade */
        @media (max-width: 1200px) {
            .skyscraper-esquerda { display: none; }
            .container.ficha-sheet { margin-left: auto; margin-right: auto; }
        }

        @media (max-width: 900px) {
            .layout-colunas {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "informacoes"
                    "pericias"
                    "inventario";
            }
            .coluna-esquerda, .coluna-direita, .coluna-inventario { width: 100%; min-width: 0; }
        }
    </style>
</head>
<body>

    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav">
            <?php if ($somente_leitura == false): ?>
                <a href="fichas.php" class="active">Fichas</a>
                <a href="campanhas.php">Campanhas</a>
            <?php else: ?>
                <a href="fichas.php">Fichas</a>
                <a href="campanhas.php" class="active">Campanhas</a>
            <?php endif; ?>
            <a href="regras.php">Regras</a>
        </nav>
        <div class="header-right">
            <?php if ($somente_leitura == true): ?>
                <a href="detalhesCampanha.php?id=<?php echo $id_campanha_mestre; ?>" class="btn-voltar">&larr; Voltar</a>
            <?php else: ?>
                <a href="fichas.php" class="btn-voltar">&larr; Voltar</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- BANNER LATERAL ESQUERDO -->
    <div class="skyscraper-esquerda">
        <img src="ad.png" alt="D20REALITAS Banner">
    </div>

    <div class="container ficha-sheet">

        <?php if ($somente_leitura == true): ?>
            <p style="background:#fff3cd; padding:10px; border-radius:5px;">Modo Mestre: visualizando ficha de <strong><?php echo htmlspecialchars($user['nome_usuario']); ?></strong> (somente leitura).</p>
        <?php endif; ?>

        <!-- TOPO COM FOTO DO PERSONAGEM (Largura total) -->
        <div class="topo-ficha">
            <?php 
            $caminho_foto = 'uploads/' . $ficha['foto_personagem'];
            if (!empty($ficha['foto_personagem']) && file_exists($caminho_foto)): 
            ?>
                <img src="<?php echo htmlspecialchars($caminho_foto); ?>" alt="Foto do Personagem" class="foto-avatar">
            <?php else: ?>
                <div class="sem-foto">SEM FOTO</div>
            <?php endif; ?>

            <div class="info-ficha">
                <div>
                    <p><strong>PERSONAGEM:</strong> <?php echo htmlspecialchars($ficha['nome_personagem']); ?></p>
                    <p><strong>ORIGEM:</strong> <?php echo htmlspecialchars($ficha['origem']); ?></p>
                </div>
                <div>
                    <p><strong>JOGADOR:</strong> <?php echo htmlspecialchars($user['nome_usuario']); ?></p>
                    <p><strong>CLASSE:</strong> <?php echo htmlspecialchars($ficha['classe_ou_trilha']); ?></p>
                </div>
            </div>
        </div>

        <?php
        $campos_biografia = [
            'Aparência' => $ficha['aparencia'] ?? '',
            'Personalidade' => $ficha['personalidade'] ?? '',
            'Histórico' => $ficha['historico'] ?? '',
            'Objetivo' => $ficha['objetivo'] ?? ''
        ];
        $tem_biografia = count(array_filter($campos_biografia, 'strlen')) > 0;
        ?>
        <?php if ($tem_biografia): ?>
            <div class="ficha-biografia">
                <?php foreach ($campos_biografia as $titulo => $texto): ?>
                    <?php if (trim($texto) !== ''): ?>
                        <section>
                            <h3><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); ?></p>
                        </section>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- LAYOUT DIVIDIDO -->
        <div class="layout-colunas">
            
            <!-- COLUNA ESQUERDA (Atributos, Barras) -->
            <div class="coluna-esquerda">
                
                <!-- AQUI ESTÁ O NOVO PENTAGRAMA DE ATRIBUTOS COM A IMAGEM -->
                <div class="container-atributos-img">
                    <img src="atributosfoto.png" alt="Atributos Pentagrama">
                    
                    <div class="valor-atributo pos-agi"><?php echo $agi; ?></div>
                    <div class="valor-atributo pos-int"><?php echo $int; ?></div>
                    <div class="valor-atributo pos-vig"><?php echo $vig; ?></div>
                    <div class="valor-atributo pos-pre"><?php echo $pre; ?></div>
                    <div class="valor-atributo pos-for"><?php echo $for; ?></div>
                </div>

                <div class="barra vida" style="margin-bottom: 10px;">
                    <span>VIDA:</span>
                    <div class="controles">
                        <span class="seta" onclick="mudarValor('vida', -1)">&lt;</span>
                        <span id="valor_vida"><?php echo (int)$ficha['pv_atual']; ?></span> / <span id="max_vida"><?php echo (int)$ficha['pv_maximo']; ?></span>
                        <span class="seta" onclick="mudarValor('vida', 1)">&gt;</span>
                    </div>
                </div>

                <div class="barra sanidade" style="margin-bottom: 10px;">
                    <span>SANIDADE:</span>
                    <div class="controles">
                        <span class="seta" onclick="mudarValor('sanidade', -1)">&lt;</span>
                        <span id="valor_sanidade">20</span> / <span id="max_sanidade">20</span>
                        <span class="seta" onclick="mudarValor('sanidade', 1)">&gt;</span>
                    </div>
                </div>

                <div class="barra defesa" style="margin-bottom: 20px;">
                    <span>DEFESA:</span>
                    <div class="controles">
                        <span class="seta" onclick="mudarValorSemLimite('defesa', -1)">&lt;</span>
                        <span id="valor_defesa">11</span>
                        <span class="seta" onclick="mudarValorSemLimite('defesa', 1)">&gt;</span>
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA (Perícias) -->
            <div class="coluna-direita">
                <div class="caixa-pericias">
                    <h3>PERÍCIAS</h3>

                    <?php if (isset($mensagem_sucesso)): ?>
                        <p style="color: green; font-weight: bold; text-align: center;"><?php echo $mensagem_sucesso; ?></p>
                    <?php endif; ?>

                    <form method="POST" action="ficha_criada.php?id=<?php echo $id_ficha; ?>">
                        <table class="tabela-pericias" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Perícia</th>
                                    <th>Dado</th>
                                    <th>Treino</th>
                                    <th>Outro</th>
                                    <th>Rolar</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($pericias as $nome => $atributo): 
                                    $nome_limpo = str_replace(array('+', '*'), '', $nome);
                                    $val_t = getPericiaValor($nome_limpo, 't', $pericias_salvas);
                                    $val_o = getPericiaValor($nome_limpo, 'o', $pericias_salvas);
                                ?>
                                    <tr>
                                        <td style="font-size: 0.9em;"><strong><?php echo htmlspecialchars($nome); ?></strong></td>
                                        <td style="font-size: 0.8em; color: #666;">(<?php echo htmlspecialchars($atributo); ?>)</td>
                                        <td>
                                            <input type="number" name="treino[<?php echo htmlspecialchars($nome_limpo); ?>]" class="input-pericia" <?php if ($somente_leitura == true) { echo 'disabled'; } ?> value="<?php echo $val_t; ?>" step="5" min="0" style="width: 40px;">
                                        </td>
                                        <td>
                                            <input type="number" name="outros[<?php echo htmlspecialchars($nome_limpo); ?>]" class="input-pericia" <?php if ($somente_leitura == true) { echo 'disabled'; } ?> value="<?php echo $val_o; ?>" step="1" style="width: 40px;">
                                        </td>
                                        <!-- BOTÃO DE ROLAGEM -->
                                        <td>
                                            <button type="button" class="btn-rolar-dado" title="Rolar Teste de <?php echo htmlspecialchars($nome_limpo); ?>" onclick="rolarTeste('<?php echo htmlspecialchars($nome_limpo); ?>', this)">🎲</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if ($somente_leitura == false): ?>
                            <button type="submit" class="btn-salvar">Salvar Perícias</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- INFERIOR (Inventário) -->
            <div class="coluna-inventario">
                <?php include 'inventario.php'; ?>
            </div>

        </div> <!-- Fim Layout Colunas -->
    </div>

    <!-- CAIXA FLUTUANTE (POPUP) QUE MOSTRA O RESULTADO DO DADO -->
    <div id="painel-rolagem" class="painel-toast">
        <div class="toast-header">
            <span id="toast-titulo">Teste</span>
            <button type="button" onclick="fecharRolagem()">X</button>
        </div>
        <div class="toast-body">
            <div class="dado-valor" id="toast-dado">20</div>
            <div class="dado-detalhes" id="toast-detalhes">1d20 + 0 mod</div>
            <div class="dado-total">Total: <span id="toast-total">20</span></div>
        </div>
    </div>

    <script>
        // Funções Originais das barras de status
        function mudarValor(attr, alt) {
            var v = document.getElementById('valor_' + attr);
            var m = document.getElementById('max_' + attr);
            var val = parseInt(v.innerText) + alt;
            if (val >= 0 && val <= parseInt(m.innerText)) {
                v.innerText = val;
            }
        }
        function mudarValorSemLimite(attr, alt) {
            var v = document.getElementById('valor_' + attr);
            var val = parseInt(v.innerText) + alt;
            if (val >= 0) {
                v.innerText = val;
            }
        }

        // --- SISTEMA DE ROLAGEM DE DADOS JS ---
        let tempoEsconder; 

        function rolarTeste(pericia, botao) {
            var tr = botao.closest('tr');
            
            var inputTreino = tr.querySelector('input[name^="treino"]');
            var inputOutros = tr.querySelector('input[name^="outros"]');
            
            var treino = parseInt(inputTreino.value) || 0;
            var outros = parseInt(inputOutros.value) || 0;
            var modificadorTotal = treino + outros;
            
            var d20 = Math.floor(Math.random() * 20) + 1;
            var resultadoTotal = d20 + modificadorTotal;
            
            document.getElementById('toast-titulo').innerText = "Teste: " + pericia;
            document.getElementById('toast-dado').innerText = d20;
            
            var sinal = (modificadorTotal >= 0) ? "+" : "";
            document.getElementById('toast-detalhes').innerText = "1d20 (" + d20 + ") " + sinal + modificadorTotal + " mod";
            
            document.getElementById('toast-total').innerText = resultadoTotal;
            
            document.getElementById('painel-rolagem').classList.add('mostrar');
            
            clearTimeout(tempoEsconder);
            tempoEsconder = setTimeout(fecharRolagem, 4000);
        }

        function fecharRolagem() {
            document.getElementById('painel-rolagem').classList.remove('mostrar');
        }
    </script>
<script src="assets/site.min.js" defer></script>
</body>
</html>