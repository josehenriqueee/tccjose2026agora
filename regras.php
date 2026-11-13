<?php
// Apresenta as regras disponíveis para consulta no sistema.
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regras Gerais - D20REALITAS</title>
    <link rel="stylesheet" href="home.css">
    <style>
        body {
            background-color: #f0f0f0;
            margin: 0;
            font-family: sans-serif;
        }

        /* ESTILOS DO BANNER - SOLUÇÃO DEFINITIVA */
        .banner-regras {
            position: relative;
            width: 100%;
            margin-bottom: 30px;
            /* Sem limites de altura, o contêiner adapta-se à imagem */
        }
        
        .banner-regras img {
            width: 100%;
            height: auto; /* Mantém a proporção original perfeita */
            display: block; /* Remove espaços fantasmas abaixo da imagem */
        }
        
        .btn-voltar {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #c00000;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            z-index: 10;
        }

        /* Estilos da Caixa de Regras */
        .caixa-rolavel {
            background: #e5e5e5;
            border-radius: 8px;
            padding: 20px 30px;
            color: #111;
            margin-bottom: 25px;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: inset 0px 0px 5px rgba(0,0,0,0.05);
        }

        .caixa-rolavel::-webkit-scrollbar {
            width: 10px;
        }
        .caixa-rolavel::-webkit-scrollbar-track {
            background: #d1d1d1;
            border-radius: 8px;
        }
        .caixa-rolavel::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 8px;
        }

        .titulo-secao {
            font-size: 24px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-bottom: 15px;
            font-weight: 400;
        }
    </style>
</head>
<body>

    <header class="site-header">
        <a href="home.php" class="site-logo"><img src="logo.png" alt="Ícone D20">D20REALITAS</a>

        <nav class="site-nav" aria-label="Navegação principal">
            <a href="fichas.php">Fichas</a>
            <a href="campanhas.php">Campanhas</a>
            <a href="regras.php" class="active">Regras</a>
        </nav>
    </header>

    
    <div class="banner-regras">
        <a href="javascript:history.back()" class="btn-voltar">&lt; Voltar</a>
        <img src="regraas.png" alt="Banner Regras D20 Realitas">
    </div>

    <div class="container" style="max-width: 900px; margin: 0 auto; padding: 0 20px 40px 20px;">
        
        <h2 class="titulo-secao">Regras básicas</h2>
        

        <div class="caixa-rolavel">
            <ul style="margin: 0; padding: 0; list-style-type: none;">
                <li style="margin-bottom: 20px; font-size: 16px; line-height: 1.5;">
                    <strong>1. Atributos Principais:</strong> Agilidade, Força, Intelecto, Presença e Vigor. Todos os personagens iniciam com valor 1. Você recebe 4 pontos para distribuir (máximo inicial 3 em cada). É possível reduzir um atributo para 0 para receber 1 ponto extra.
                </li>
                
                <li style="margin-bottom: 20px; font-size: 16px; line-height: 1.5;">
                    <strong>2. Testes de D20:</strong> Rola-se uma quantidade de d20 igual ao valor do atributo testado e escolhe-se o <strong>MAIOR</strong> resultado. Se o atributo for 0, rolam-se 2d20 e escolhe-se o <strong>MENOR</strong> resultado. A soma (dado + bônus de perícia) deve igualar ou superar a Dificuldade do Teste (DT) para obter sucesso.
                </li>
                
                <li style="margin-bottom: 20px; font-size: 16px; line-height: 1.5;">
                    <strong>3. Graus de Treinamento:</strong> O bônus de perícia varia conforme o nível de treinamento do agente: Destreinado (+0), Treinado (+5), Veterano (+10) e Expert (+15).
                </li>
                
                <li style="margin-bottom: 20px; font-size: 16px; line-height: 1.5;">
                    <strong>4. Classes de Agente:</strong> 
                    <br>- <em>Combatente:</em> Focado em combate. PV Inicial: 20 + Vigor (+4 PV por NEX).
                    <br>- <em>Especialista:</em> Focado em perícias. PV Inicial: 16 + Vigor (+3 PV por NEX).
                    <br>- <em>Ocultista:</em> Focado em rituais. PV Inicial: 12 + Vigor (+2 PV por NEX).
                </li>
                
                <li style="margin-bottom: 20px; font-size: 16px; line-height: 1.5;">
                    <strong>5. Exposição Paranormal (NEX):</strong> Personagens iniciantes começam com 5% de NEX. Acumular Pontos de Exposição (PEX) aumenta o NEX, concedendo novos atributos, habilidades, PV, PE e Sanidade.
                </li>

                <li style="margin-bottom: 20px; font-size: 16px; line-height: 1.5;">
                    <strong>6. Combate:</strong> O sistema de combate é baseado em turnos. Testes de ataque usam Luta (corpo a corpo) ou Pontaria (à distância) contra a Defesa do alvo. Um 20 natural no d20 acerta criticamente.
                </li>
                
                <li style="margin-bottom: 10px; font-size: 16px; line-height: 1.5;">
                    <strong>7. Investigação:</strong> Os jogadores podem descobrir pistas e interagir com o ambiente usando perícias contra a DT da cena. É possível Facilitar a Investigação (+5 para um aliado) ou Prestar Ajuda.
                </li>
            </ul>
        </div>

    </div>

<script src="assets/site.min.js" defer></script>
</body>
</html>