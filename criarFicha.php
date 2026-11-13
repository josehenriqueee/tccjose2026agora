<?php
session_start();
require_once 'conecta.php';

// Verifica se o utilizador está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['convite']) && trim($_GET['convite']) !== '') {
    $token_convite = mysqli_real_escape_string($conexao, trim($_GET['convite']));
    $res_convite = mysqli_query($conexao, "SELECT id_campanha FROM campanha WHERE token_convite = '$token_convite'");

    if ($res_convite && mysqli_num_rows($res_convite) > 0) {
        $_SESSION['convite_pendente'] = $token_convite;
    } else {
        unset($_SESSION['convite_pendente']);
    }
} elseif (!isset($_GET['etapa'])) {
    unset($_SESSION['convite_pendente']);
}

$sufixo_convite = !empty($_SESSION['convite_pendente'])
    ? '&convite=' . urlencode($_SESSION['convite_pendente'])
    : '';

$id_usuario_logado = $_SESSION['id_usuario'];

// Busca o nome do usuário logado para exibir no cabeçalho
$nome_usuario_logado = "Usuário";
$sql_user = "SELECT nome_usuario FROM usuario WHERE id_usuario = '$id_usuario_logado'";
$res_user = mysqli_query($conexao, $sql_user);
if ($res_user && $u = mysqli_fetch_assoc($res_user)) {
    $nome_usuario_logado = $u['nome_usuario'];
}

// Lista completa das 34 Origens do sistema Ordem Paranormal
$origens_detalhadas = [
    "Acadêmico" => [
        "desc" => "Você era um pesquisador ou professor universitário. De forma proposital ou não, seus estudos tocaram em assuntos misteriosos e chamaram a atenção da Ordo Realitas.",
        "pericias" => "Ciências (+5) e Investigação (+5).",
        "poder_nome" => "Saber é Poder",
        "poder_desc" => "Quando faz um teste usando Intelecto, você pode gastar 2 PE para receber +5 nesse teste."
    ],
    "Agente de Saúde" => [
        "desc" => "Você cuidava da saúde de pessoas e presenciou eventos paranormais e inexplicáveis durante o seu trabalho diário.",
        "pericias" => "Medicina (+5) e Intuição (+5).",
        "poder_nome" => "Técnica Medicinal",
        "poder_desc" => "Quando usa Medicina para curar, você pode gastar 2 PE para restaurar +1d6 pontos de vida."
    ],
    "Amnésico" => [
        "desc" => "Você não se lembra do seu passado, mas possui conhecimentos ou reflexos misteriosos que surgem nos momentos de maior necessidade.",
        "pericias" => "Duas perícias à sua escolha (+5 em cada).",
        "poder_nome" => "Lembranças Vagas",
        "poder_desc" => "Uma vez por cena, você pode gastar 2 PE para receber +5 em um teste qualquer."
    ],
    "Artista" => [
        "desc" => "Você expressava sua arte através da música, pintura, atuação ou escrita antes de se deparar com os mistérios do Paranormal.",
        "pericias" => "Artes (+5) e Enganação (+5).",
        "poder_nome" => "Atração",
        "poder_desc" => "Você pode usar Artes para fascinar pessoas ou gastar 2 PE para ganhar +5 em testes de Diplomacia."
    ],
    "Atleta" => [
        "desc" => "Você dedicou sua vida ao esporte e ao condicionamento físico de alto rendimento.",
        "pericias" => "Acrobacia (+5) e Atletismo (+5).",
        "poder_nome" => "Treino Pesado",
        "poder_desc" => "Você recebe +2 em testes de Atletismo e pode gastar 1 PE para dobrar o seu deslocamento no turno."
    ],
    "Chef" => [
        "desc" => "Você trabalhava na cozinha, acostumado ao trabalho sob alta pressão e à liderança tática de equipe.",
        "pericias" => "Fortitude (+5) e Profissão (+5).",
        "poder_nome" => "Ingrediente Secreto",
        "poder_desc" => "Você pode preparar refeições durante o descanso que concedem +2 em testes de Fortitude durante o dia."
    ],
    "Cientista Forense" => [
        "desc" => "Especialista em investigar cenas de crimes, análises laboratoriais e rastreamento de vestígios minuciosos.",
        "pericias" => "Ciências (+5) e Investigação (+5).",
        "poder_nome" => "Olho Clínico",
        "poder_desc" => "Ao investigar uma cena, você pode gastar 2 PE para encontrar automaticamente uma pista oculta."
    ],
    "Criminoso" => [
        "desc" => "Você vivia do outro lado da lei, habituado a agir nas sombras, enganar autoridades e sobreviver nas ruas.",
        "pericias" => "Crime (+5) e Furtividade (+5).",
        "poder_nome" => "Pressionar Mente",
        "poder_desc" => "Você recebe +2 em testes de Crime e pode gastar 1 PE para destrancar trancas sem equipamento."
    ],
    "Cultista Arrependido" => [
        "desc" => "Você já fez parte de um grupo esotérico ou culto paranormal, mas conseguiu escapar antes que fosse tarde demais.",
        "pericias" => "Ocultismo (+5) e Religião (+5).",
        "poder_nome" => "Saber Proibido",
        "poder_desc" => "Você ganha +2 em testes para identificar criaturas e rituais paranormais e começa com um ritual de 1º círculo."
    ],
    "Desgarrado" => [
        "desc" => "Você aprendeu a sobreviver sozinho na natureza ou longe da civilização, contando apenas com os seus instintos.",
        "pericias" => "Adestramento (+5) e Sobrevivência (+5).",
        "poder_nome" => "Calejado",
        "poder_desc" => "Você recebe +1 Ponto de Vida por nível de exposição (NEX)."
    ],
    "Dublê" => [
        "desc" => "Especialista em manobras arriscadas e cenas perigosas do meio cinematográfico.",
        "pericias" => "Acrobacia (+5) e Pilotagem (+5).",
        "poder_nome" => "Especialista em Quedas",
        "poder_desc" => "Você reduz o dano de quedas pela metade e pode gastar 2 PE para ignorar um impacto grave."
    ],
    "Engenheiro" => [
        "desc" => "Especialista em estruturas, mecânica, eletrônica e na resolução prática de problemas complexos.",
        "pericias" => "Profissão (+5) e Tecnologia (+5).",
        "poder_nome" => "Ferramentas Certas",
        "poder_desc" => "Você pode improvisar ferramentas adequadas e gastar 2 PE para reparar itens rapidamente."
    ],
    "Escritor" => [
        "desc" => "Sua mente criativa criava histórias ficcionais até que a realidade Paranormal superou qualquer ficção.",
        "pericias" => "Artes (+5) e Atualidades (+5).",
        "poder_nome" => "Leitura Dinâmica",
        "poder_desc" => "Você consegue analisar textos e pistas rapidamente gastando 2 PE para obter insights extras."
    ],
    "Executivo" => [
        "desc" => "Experiente no mercado corporativo, na negociação de contratos e na gestão de altos recursos financeiros.",
        "pericias" => "Diplomacia (+5) e Intimidação (+5).",
        "poder_nome" => "Processo Eficiente",
        "poder_desc" => "Você pode gastar 2 PE para reorganizar itens ou negociar preços favoráveis."
    ],
    "Gaudério Abutre" => [
        "desc" => "Afeito à cultura tradicionalista e à vivência campeira no campo ou em cenários hostis.",
        "pericias" => "Adestramento (+5) e Luta (+5).",
        "poder_nome" => "Espírito Campeiro",
        "poder_desc" => "Você ganha resistência a condições climáticas e bônus em testes com animais."
    ],
    "Ginasta" => [
        "desc" => "Agilidade incomparável, equilíbrio perfeito e controle corporal cirúrgico.",
        "pericias" => "Acrobacia (+5) e Reflexos (+5).",
        "poder_nome" => "Movimento Fluido",
        "poder_desc" => "Você pode gastar 2 PE para esquivar-se de um ataque com bônus de +5 em Reflexos."
    ],
    "Investigador" => [
        "desc" => "Detetive de polícia ou particular experiente em analisar vestígios e conectar pistas complexas.",
        "pericias" => "Investigação (+5) e Percepção (+5).",
        "poder_nome" => "Faro para Pistas",
        "poder_desc" => "Quando faz um teste de Investigação, pode gastar 2 PE para receber +5 nesse teste."
    ],
    "Jornalista" => [
        "desc" => "Investigador da verdade focado na recolha de fatos, entrevistas e revelação de segredos ocultos.",
        "pericias" => "Atualidades (+5) e Investigação (+5).",
        "poder_nome" => "Fonte Confiável",
        "poder_desc" => "Você obtém informações mais facilmente com PNJ e pode gastar 2 PE para descobrir segredos."
    ],
    "Lutador" => [
        "desc" => "Praticante de artes marciais ou combates corporais intensos.",
        "pericias" => "Luta (+5) e Fortitude (+5).",
        "poder_nome" => "Mão Pesada",
        "poder_desc" => "Seus ataques corpo a corpo causam +2 de dano adicional."
    ],
    "Magnata" => [
        "desc" => "Dono de grandes fortunas e recursos financeiros praticamente ilimitados.",
        "pericias" => "Diplomacia (+5) e Pilotagem (+5).",
        "poder_nome" => "Patrocínio",
        "poder_desc" => "Seu limite de crédito e categoria de itens disponíveis é ampliado."
    ],
    "Mercenário" => [
        "desc" => "Soldado de aluguel, focado em cumprir objetivos táticos sem envolvimento emocional.",
        "pericias" => "Iniciativa (+5) e Tática (+5).",
        "poder_nome" => "Posição de Disparo",
        "poder_desc" => "Você ganha bônus de +2 em testes de ataque e iniciativa no primeiro turno."
    ],
    "Militar" => [
        "desc" => "Treinado pelas forças armadas para disciplina rígida, tática de grupo e uso de armamento pesado.",
        "pericias" => "Pontaria (+5) e Tática (+5).",
        "poder_nome" => "Paraabellum",
        "poder_desc" => "Você ganha +2 em testes de Pontaria e pode gastar 2 PE para aumentar o dano à distância."
    ],
    "Operário" => [
        "desc" => "Trabalhador braçal acostumado ao uso de ferramentas pesadas e esforço físico contínuo.",
        "pericias" => "Fortitude (+5) e Profissão (+5).",
        "poder_nome" => "Ferramenta de Trabalho",
        "poder_desc" => "Escolha uma ferramenta pesada: ela passa a ser considerada uma arma simples e causa +2 de dano."
    ],
    "Policial" => [
        "desc" => "Servidor da lei habituado ao patrulhamento de ruas, abordagens e situações de tiroteio.",
        "pericias" => "Percepção (+5) e Pontaria (+5).",
        "poder_nome" => "Patrulha",
        "poder_desc" => "Você recebe +2 em testes de Defesa e iniciativa."
    ],
    "Professor" => [
        "desc" => "Educador dedicado a transmitir conhecimento e a orientar mentes jovens.",
        "pericias" => "Ciências (+5) e Diplomacia (+5).",
        "poder_nome" => "Aula Prática",
        "poder_desc" => "Você pode gastar 2 PE para conceder +2 num teste a um aliado."
    ],
    "Religioso" => [
        "desc" => "Guiado pela fé e pelo apoio espiritual a pessoas em momentos de desespero.",
        "pericias" => "Religião (+5) e Vontade (+5).",
        "poder_nome" => "Acalentar",
        "poder_desc" => "Você recebe +2 em testes de Vontade e pode gastar 2 PE para acalmar um aliado perturbado."
    ],
    "Revoltado" => [
        "desc" => "Indivíduo marginalizado que luta contra o sistema e recusa submeter-se a injustiças.",
        "pericias" => "Intimidação (+5) e Vontade (+5).",
        "poder_nome" => "Contra Tudo",
        "poder_desc" => "Você ganha bônus quando luta em menor número ou contra alvos mais poderosos."
    ],
    "Servidor Público" => [
        "desc" => "Acostumado à burocracia, leis e ao funcionamento interno das instituições governamentais.",
        "pericias" => "Atualidades (+5) e Diplomacia (+5).",
        "poder_nome" => "Burocracia",
        "poder_desc" => "Você pode gastar 2 PE para ignorar restrições burocráticas ou obter autorizações."
    ],
    "Teórico da Conspiração" => [
        "desc" => "Você sempre soube que havia algo oculto governando o mundo antes mesmo de ver um monstro.",
        "pericias" => "Investigação (+5) e Ocultismo (+5).",
        "poder_nome" => "Eu Já Sabia!",
        "poder_desc" => "Você recebe +2 em testes de Vontade contra efeitos de medo ou paranormais."
    ],
    "T.I." => [
        "desc" => "Especialista em computadores, redes, hacking e sistemas digitais.",
        "pericias" => "Tecnologia (+5) e Investigação (+5).",
        "poder_nome" => "Saber de Rede",
        "poder_desc" => "Você ganha +2 em testes de Tecnologia e pode gastar 2 PE para hackear dispositivos rapidamente."
    ],
    "Trabalhador Rural" => [
        "desc" => "Habituado ao campo, agricultura e ao trabalho duro longe das grandes cidades.",
        "pericias" => "Adestramento (+5) e Sobrevivência (+5).",
        "poder_nome" => "Resistência do Campo",
        "poder_desc" => "Você tem bônus de Fortitude e imunidade a certas condições do meio ambiente."
    ],
    "Trambiqueiro" => [
        "desc" => "Mestre do engano, golpes, disfarces e lábia afiada.",
        "pericias" => "Enganação (+5) e Crime (+5).",
        "poder_nome" => "Na Calada",
        "poder_desc" => "Você ganha +2 em Enganação e pode gastar 2 PE para se livrar de suspeitas."
    ],
    "Universitário" => [
        "desc" => "Estudante jovem, habituado a noites mal dormidas, café e rotina de testes.",
        "pericias" => "Atualidades (+5) e Investigação (+5).",
        "poder_nome" => "Dedicação",
        "poder_desc" => "Você ganha PE adicional ao subir de nível ou descansar."
    ],
    "Vítima" => [
        "desc" => "Você sobreviveu a um ataque paranormal catastrófico e carrega as marcas do trauma.",
        "pericias" => "Vontade (+5) e Reflexos (+5).",
        "poder_nome" => "Cicatrizes",
        "poder_desc" => "Você começa com +10 de Sanidade Máxima ou Vontade reforçada."
    ]
];

// Inicializa a sessão temporária para a criação de ficha
if (!isset($_SESSION['nova_ficha'])) {
    $_SESSION['nova_ficha'] = [
        'agilidade'       => 1,
        'intelecto'       => 1,
        'vigor'           => 1,
        'presenca'        => 1,
        'forca'           => 1,
        'origem'          => '',
        'classe'          => '',
        'nome_personagem' => '',
        'pv_maximo'       => 20
    ];
}

$etapa = isset($_GET['etapa']) ? (int)$_GET['etapa'] : 1;

// Processamento dos dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($etapa == 1) {
        $_SESSION['nova_ficha']['agilidade'] = (int)$_POST['agilidade'];
        $_SESSION['nova_ficha']['intelecto'] = (int)$_POST['intelecto'];
        $_SESSION['nova_ficha']['vigor']     = (int)$_POST['vigor'];
        $_SESSION['nova_ficha']['presenca']  = (int)$_POST['presenca'];
        $_SESSION['nova_ficha']['forca']     = (int)$_POST['forca'];
        header("Location: criarFicha.php?etapa=2" . $sufixo_convite);
        exit();
    } elseif ($etapa == 2) {
        if (!empty($_POST['origem'])) {
            $_SESSION['nova_ficha']['origem'] = $_POST['origem'];
        }
        header("Location: criarFicha.php?etapa=3" . $sufixo_convite);
        exit();
    } elseif ($etapa == 3) {
        $_SESSION['nova_ficha']['classe'] = $_POST['classe'];
        header("Location: criarFicha.php?etapa=4" . $sufixo_convite);
        exit();
    } elseif ($etapa == 4) {
        $_SESSION['nova_ficha']['nome_personagem'] = trim($_POST['nome_personagem'] ?? '');
        $_SESSION['nova_ficha']['pv_maximo']       = (int)$_POST['pv_maximo'];
        $_SESSION['nova_ficha']['aparencia'] = trim($_POST['aparencia'] ?? '');
        $_SESSION['nova_ficha']['personalidade'] = trim($_POST['personalidade'] ?? '');
        $_SESSION['nova_ficha']['historico'] = trim($_POST['historico'] ?? '');
        $_SESSION['nova_ficha']['objetivo'] = trim($_POST['objetivo'] ?? '');

       
        $agi = $_SESSION['nova_ficha']['agilidade'];
        $int = $_SESSION['nova_ficha']['intelecto'];
        $vig = $_SESSION['nova_ficha']['vigor'];
        $pre = $_SESSION['nova_ficha']['presenca'];
        $for = $_SESSION['nova_ficha']['forca'];

        $origem_escolhida = $_SESSION['nova_ficha']['origem'] ?? '';

        $bonus_origem = [
            "Acadêmico" => "int", "Agente de Saúde" => "int", "Amnésico" => "pre", "Artista" => "pre",
            "Atleta" => "for", "Chef" => "vig", "Cientista Forense" => "int", "Criminoso" => "agi",
            "Cultista Arrependido" => "pre", "Desgarrado" => "vig", "Dublê" => "agi", "Engenheiro" => "int",
            "Escritor" => "int", "Executivo" => "pre", "Gaudério Abutre" => "vig", "Ginasta" => "agi",
            "Investigador" => "int", "Jornalista" => "int", "Lutador" => "for", "Magnata" => "pre",
            "Mercenário" => "agi", "Militar" => "for", "Operário" => "for", "Policial" => "vig",
            "Professor" => "int", "Religioso" => "pre", "Revoltado" => "vig", "Servidor Público" => "int",
            "Teórico da Conspiração" => "int", "T.I." => "int", "Trabalhador Rural" => "vig", 
            "Trambiqueiro" => "pre", "Universitário" => "int", "Vítima" => "vig"
        ];

        // 3. MAPA DE PERÍCIAS
        $pericias_origem = [
            "Acadêmico" => "Ciências (+5), Investigação (+5)",
            "Agente de Saúde" => "Medicina (+5), Intuição (+5)",
            "Amnésico" => "Duas perícias à escolha (+5)",
            "Artista" => "Artes (+5), Enganação (+5)",
            "Atleta" => "Acrobacia (+5), Atletismo (+5)",
            "Chef" => "Fortitude (+5), Profissão (+5)",
            "Cientista Forense" => "Ciências (+5), Investigação (+5)",
            "Criminoso" => "Crime (+5), Furtividade (+5)",
            "Cultista Arrependido" => "Ocultismo (+5), Religião (+5)",
            "Desgarrado" => "Adestramento (+5), Sobrevivência (+5)",
            "Dublê" => "Acrobacia (+5), Pilotagem (+5)",
            "Engenheiro" => "Profissão (+5), Tecnologia (+5)",
            "Escritor" => "Artes (+5), Atualidades (+5)",
            "Executivo" => "Diplomacia (+5), Intimidação (+5)",
            "Gaudério Abutre" => "Adestramento (+5), Luta (+5)",
            "Ginasta" => "Acrobacia (+5), Reflexos (+5)",
            "Investigador" => "Investigação (+5), Percepção (+5)",
            "Jornalista" => "Atualidades (+5), Investigação (+5)",
            "Lutador" => "Luta (+5), Fortitude (+5)",
            "Magnata" => "Diplomacia (+5), Pilotagem (+5)",
            "Mercenário" => "Iniciativa (+5), Tática (+5)",
            "Militar" => "Pontaria (+5), Tática (+5)",
            "Operário" => "Fortitude (+5), Profissão (+5)",
            "Policial" => "Percepção (+5), Pontaria (+5)",
            "Professor" => "Ciências (+5), Diplomacia (+5)",
            "Religioso" => "Religião (+5), Vontade (+5)",
            "Revoltado" => "Intimidação (+5), Vontade (+5)",
            "Servidor Público" => "Atualidades (+5), Diplomacia (+5)",
            "Teórico da Conspiração" => "Investigação (+5), Ocultismo (+5)",
            "T.I." => "Tecnologia (+5), Investigação (+5)",
            "Trabalhador Rural" => "Adestramento (+5), Sobrevivência (+5)",
            "Trambiqueiro" => "Enganação (+5), Crime (+5)",
            "Universitário" => "Atualidades (+5), Investigação (+5)",
            "Vítima" => "Vontade (+5), Reflexos (+5)"
        ];

        // 4. Aplica a matemática dos atributos
        if (array_key_exists($origem_escolhida, $bonus_origem)) {
            $atributo_alvo = $bonus_origem[$origem_escolhida];
            if ($atributo_alvo == 'agi') $agi += 1;
            if ($atributo_alvo == 'int') $int += 1;
            if ($atributo_alvo == 'vig') $vig += 1;
            if ($atributo_alvo == 'pre') $pre += 1;
            if ($atributo_alvo == 'for') $for += 1;
        }

        // 5. Pega a string de perícias correspondente
        $str_pericias = $pericias_origem[$origem_escolhida] ?? 'Nenhuma perícia base';
        
        // 6. PROCESSAMENTO DO UPLOAD DA FOTO DO PERSONAGEM
        $foto_nome = NULL;
        if (isset($_FILES['foto_personagem']) && $_FILES['foto_personagem']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto_personagem'];
            $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($extensao, $permitidas) && $file['size'] <= 2 * 1024 * 1024) {
                $nomeUnico = time() . "_" . uniqid() . "." . $extensao;
                if (!is_dir("uploads/")) {
                    mkdir("uploads/", 0755, true);
                }
                if (move_uploaded_file($file['tmp_name'], "uploads/" . $nomeUnico)) {
                    $foto_nome = $nomeUnico;
                }
            }
        }

        // 7. Prepara as variáveis para o banco
        $str_atributos = "AGI: $agi, INT: $int, VIG: $vig, PRE: $pre, FOR: $for";
        $origem = mysqli_real_escape_string($conexao, $origem_escolhida);
        $classe = mysqli_real_escape_string($conexao, $_SESSION['nova_ficha']['classe'] ?? '');
        $nome   = mysqli_real_escape_string($conexao, $_SESSION['nova_ficha']['nome_personagem']);
        $pv     = $_SESSION['nova_ficha']['pv_maximo'];
        $aparencia = mysqli_real_escape_string($conexao, $_SESSION['nova_ficha']['aparencia']);
        $personalidade = mysqli_real_escape_string($conexao, $_SESSION['nova_ficha']['personalidade']);
        $historico = mysqli_real_escape_string($conexao, $_SESSION['nova_ficha']['historico']);
        $objetivo = mysqli_real_escape_string($conexao, $_SESSION['nova_ficha']['objetivo']);

        $foto_sql = $foto_nome ? "'$foto_nome'" : "NULL";

        // 8. Salva ficha, foto e biografia
        $sql = "INSERT INTO ficha_personagem 
                (id_usuario, nome_personagem, origem, classe_ou_trilha, pv_atual, pv_maximo, atributos, pericias, foto_personagem, aparencia, personalidade, historico, objetivo) 
                VALUES 
                ('$id_usuario_logado', '$nome', '$origem', '$classe', '$pv', '$pv', '$str_atributos', '$str_pericias', $foto_sql, '$aparencia', '$personalidade', '$historico', '$objetivo')";

        if (mysqli_query($conexao, $sql)) {
            unset($_SESSION['nova_ficha']);
            if (!empty($_SESSION['convite_pendente'])) {
                $token_retorno = $_SESSION['convite_pendente'];
                unset($_SESSION['convite_pendente']);
                header("Location: convite.php?t=" . urlencode($token_retorno));
            } else {
                header("Location: fichas.php");
            }
            exit();
        } else {
            $erro = "Erro ao guardar no banco de dados: " . mysqli_error($conexao);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D20REALITAS - Criar Ficha</title>
    <link rel="stylesheet" href="home.css?v=4">
</head>
<body class="criar-ficha">

    <!-- CABEÇALHO -->
    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>
        <nav class="site-nav" aria-label="Navegação principal">
            <a href="fichas.php" class="active">Fichas</a>
            <a href="campanhas.php">Campanhas</a>
            <a href="regras.php">Regras</a>
        </nav>
        <div class="header-right"><span class="user-pill"><?php echo htmlspecialchars($nome_usuario_logado); ?></span></div>
    </header>

    <!-- BARRA SUPERIOR DE PASSOS -->
    <div class="sub-header">
        <?php if ($etapa > 1): ?>
            <a href="criarFicha.php?etapa=<?php echo ($etapa - 1) . $sufixo_convite; ?>" class="btn-pill">&larr; Voltar</a>
        <?php else: ?>
            <a href="fichas.php" class="btn-pill">&larr; Voltar</a>
        <?php endif; ?>

        <div class="wizard-steps">
            <span class="step <?php echo $etapa == 1 ? 'active' : ''; ?>">Atributos</span>
            <span class="line"></span>
            <span class="step <?php echo $etapa == 2 ? 'active' : ''; ?>">Origem</span>
            <span class="line"></span>
            <span class="step <?php echo $etapa == 3 ? 'active' : ''; ?>">Classe</span>
            <span class="line"></span>
            <span class="step <?php echo $etapa == 4 ? 'active' : ''; ?>">Biografia</span>
        </div>
        <div style="width: 100px;"></div>
    </div>

    <!-- ETAPA 1: ATRIBUTOS -->
    <?php if ($etapa == 1): ?>
        <div class="info-box atributos-intro">
            <span class="atributos-selo">ETAPA 1 · ATRIBUTOS</span>
            <h1>Como seu agente se destaca?</h1>
            <p>Distribua os valores de 0 a 5 entre os cinco atributos do personagem.</p>
        </div>
        <form action="criarFicha.php?etapa=1<?php echo htmlspecialchars($sufixo_convite, ENT_QUOTES, 'UTF-8'); ?>" method="POST" class="atributos-form">
            <div class="atributos-layout">
                <figure class="atributos-ilustracao">
                    <img src="atributosfoto.png" alt="Diagrama dos cinco atributos do personagem">
                </figure>
                <div class="atributos-painel">
                    <?php
                    $atributos = [
                        ['nome' => 'Agilidade', 'sigla' => 'AGI', 'campo' => 'agilidade', 'descricao' => 'Reflexos e velocidade'],
                        ['nome' => 'Intelecto', 'sigla' => 'INT', 'campo' => 'intelecto', 'descricao' => 'Conhecimento e raciocínio'],
                        ['nome' => 'Vigor', 'sigla' => 'VIG', 'campo' => 'vigor', 'descricao' => 'Resistência e saúde'],
                        ['nome' => 'Presença', 'sigla' => 'PRE', 'campo' => 'presenca', 'descricao' => 'Carisma e determinação'],
                        ['nome' => 'Força', 'sigla' => 'FOR', 'campo' => 'forca', 'descricao' => 'Potência física']
                    ];
                    foreach ($atributos as $atributo):
                        $valor_atributo = (int) ($_SESSION['nova_ficha'][$atributo['campo']] ?? 1);
                    ?>
                        <div class="atributo-linha">
                            <span class="atributo-sigla"><?php echo htmlspecialchars($atributo['sigla']); ?></span>
                            <div class="atributo-detalhe">
                                <strong><?php echo htmlspecialchars($atributo['nome']); ?></strong>
                                <small><?php echo htmlspecialchars($atributo['descricao']); ?></small>
                            </div>
                            <div class="atributo-controle">
                                <button type="button" class="atributo-ajuste" data-atributo-ajuste="-1" aria-label="Diminuir <?php echo htmlspecialchars($atributo['nome']); ?>">−</button>
                                <input type="number"
                                       name="<?php echo htmlspecialchars($atributo['campo']); ?>"
                                       min="0" max="5" value="<?php echo $valor_atributo; ?>"
                                       aria-label="<?php echo htmlspecialchars($atributo['nome']); ?>"
                                       required>
                                <button type="button" class="atributo-ajuste" data-atributo-ajuste="1" aria-label="Aumentar <?php echo htmlspecialchars($atributo['nome']); ?>">+</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="atributos-rodape">
                <p class="atributos-soma" aria-live="polite">Pontos distribuídos: <strong data-atributos-soma>5</strong></p>
                <button type="submit" class="btn-pill">Avançar</button>
            </div>
        </form>

    <!-- ETAPA 2: ORIGEM -->
    <?php elseif ($etapa == 2): 
        $origem_salva = $_SESSION['nova_ficha']['origem'] ?? '';
    ?>
        <div class="info-box origem-intro">
            <span class="atributos-selo">ETAPA 2 · ORIGEM</span>
            <h1>Quem você era antes da Ordem?</h1>
            <p>Sua origem define parte da sua história, concede perícias e um poder especial. Abra uma opção para conhecer os detalhes.</p>
        </div>

        <form action="criarFicha.php?etapa=2<?php echo htmlspecialchars($sufixo_convite, ENT_QUOTES, 'UTF-8'); ?>" method="POST" id="form-origem">
            <input type="hidden" name="origem" id="origem_selecionada" value="<?php echo htmlspecialchars($origem_salva); ?>">

            <div class="origem-container">
                <?php foreach ($origens_detalhadas as $nome_origem => $info): 
                    $esta_selecionada = ($origem_salva === $nome_origem);
                ?>
                    <details class="origem-card" <?php echo $esta_selecionada ? 'open' : ''; ?>>
                        <summary class="origem-summary">
                            <div class="origem-title-box">
                                <span class="chevron-icon">&#10095;</span>
                                <span class="origem-name"><?php echo htmlspecialchars($nome_origem); ?></span>
                            </div>
                            <button type="button" 
                                    class="btn-escolher <?php echo $esta_selecionada ? 'selected' : ''; ?>" 
                                    onclick="selecionarOrigem('<?php echo htmlspecialchars($nome_origem); ?>', this)">
                                <?php echo $esta_selecionada ? 'Selecionado' : 'Escolher'; ?>
                            </button>
                        </summary>
                        <div class="origem-body">
                            <hr class="red-divider">
                            <p><?php echo htmlspecialchars($info['desc']); ?></p><br>
                            <p><strong>Perícias treinadas;</strong> <?php echo htmlspecialchars($info['pericias']); ?></p>
                            <p><strong><?php echo htmlspecialchars($info['poder_nome']); ?>;</strong> <?php echo htmlspecialchars($info['poder_desc']); ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>

            <div class="footer-action">
                <button type="submit" class="btn-pill">Avançar</button>
            </div>
        </form>

    <!-- ETAPA 3: CLASSE -->
    <?php elseif ($etapa == 3): ?>
        <div class="info-box classe-intro">
            <span class="atributos-selo">ETAPA 3 · CLASSE</span>
            <h1>Qual será o seu papel?</h1>
            <p>Escolha a classe que combina com o jeito do seu agente agir durante as missões.</p>
        </div>
        <form action="criarFicha.php?etapa=3<?php echo htmlspecialchars($sufixo_convite, ENT_QUOTES, 'UTF-8'); ?>" method="POST" class="classe-form">
            <div class="classe-grid">
                <article class="classe-card">
                    <span class="classe-numero">01</span>
                    <h2>Combatente</h2>
                    <p>Treinado para enfrentar ameaças de perto, proteger a equipe e aguentar situações extremas.</p>
                    <button type="submit" name="classe" value="Combatente" class="btn-escolher">Escolher classe</button>
                </article>
                <article class="classe-card">
                    <span class="classe-numero">02</span>
                    <h2>Especialista</h2>
                    <p>Versátil e preparado para investigação, tecnologia, estratégia e apoio ao grupo.</p>
                    <button type="submit" name="classe" value="Especialista" class="btn-escolher">Escolher classe</button>
                </article>
                <article class="classe-card">
                    <span class="classe-numero">03</span>
                    <h2>Ocultista</h2>
                    <p>Estuda o paranormal e usa rituais para manipular forças que poucos compreendem.</p>
                    <button type="submit" name="classe" value="Ocultista" class="btn-escolher">Escolher classe</button>
                </article>
            </div>
        </form>

    <!-- ETAPA 4: BIOGRAFIA E FOTO -->
    <?php elseif ($etapa == 4): ?>
        <form action="criarFicha.php?etapa=4<?php echo htmlspecialchars($sufixo_convite, ENT_QUOTES, 'UTF-8'); ?>" method="POST" enctype="multipart/form-data" class="biografia-form">
            <div class="biografia-topo">
                <label class="foto-escolha" for="foto_personagem">
                    <img src="" alt="Prévia da foto do personagem" data-foto-preview hidden>
                    <span class="foto-icone" aria-hidden="true">&#128247;</span>
                    <span class="foto-texto">Adicionar foto</span>
                    <input type="file" id="foto_personagem" name="foto_personagem" accept="image/jpeg,image/png,image/webp">
                </label>

                <div class="biografia-identificacao">
                    <label class="campo-biografia">
                        Nome do personagem
                        <input type="text" name="nome_personagem" maxlength="150"
                               value="<?php echo htmlspecialchars($_SESSION['nova_ficha']['nome_personagem'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                    <div class="biografia-dados">
                        <label class="campo-biografia">
                            Jogador
                            <input type="text" value="<?php echo htmlspecialchars($nome_usuario_logado, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                        </label>
                        <label class="campo-biografia campo-pv">
                            PV máximo
                            <input type="number" name="pv_maximo" min="1" value="<?php echo (int) ($_SESSION['nova_ficha']['pv_maximo'] ?? 20); ?>" required>
                        </label>
                    </div>
                </div>
            </div>

            <div class="biografia-campos">
                <label class="campo-biografia">
                    Aparência
                    <textarea name="aparencia" maxlength="2000" placeholder="Gênero, idade, descrição física..."><?php echo htmlspecialchars($_SESSION['nova_ficha']['aparencia'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>
                <label class="campo-biografia">
                    Personalidade
                    <textarea name="personalidade" maxlength="2000" placeholder="Traços, opiniões, jeito de agir..."><?php echo htmlspecialchars($_SESSION['nova_ficha']['personalidade'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>
                <label class="campo-biografia">
                    Histórico
                    <textarea name="historico" maxlength="4000" placeholder="O que fez até agora? Qual é a sua história?"><?php echo htmlspecialchars($_SESSION['nova_ficha']['historico'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>
                <label class="campo-biografia">
                    Objetivo
                    <textarea name="objetivo" maxlength="2000" placeholder="O que marcou seu agente e o fez seguir esse caminho?"><?php echo htmlspecialchars($_SESSION['nova_ficha']['objetivo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </label>
            </div>

            <?php if (isset($erro)): ?>
                <p class="erro-msg biografia-erro"><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <div class="biografia-rodape">
                <button type="submit" class="btn-pill">Salvar ficha</button>
            </div>
        </form>
    <?php endif; ?>

    <script>
        function selecionarOrigem(nome, elemento) {
            document.getElementById('origem_selecionada').value = nome;
            
            const botoes = document.querySelectorAll('.btn-escolher');
            botoes.forEach(btn => {
                btn.classList.remove('selected');
                btn.innerText = 'Escolher';
            });

            elemento.classList.add('selected');
            elemento.innerText = 'Selecionado';
        }
    </script>
<script src="assets/site.min.js?v=4" defer></script>
</body>
</html>