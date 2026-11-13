<?php
session_start();
require_once 'conecta.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_campanha = trim((string) ($_POST['nome_campanha'] ?? ''));
    $descricao_campanha = trim((string) ($_POST['descricao_campanha'] ?? ''));
    $id_usuario = (int) $_SESSION['id_usuario'];

    $res_total = mysqli_query($conexao, "SELECT COUNT(*) AS total FROM campanha WHERE id_usuario = $id_usuario");
    $total_campanhas = $res_total && $row_total = mysqli_fetch_assoc($res_total) ? (int) $row_total['total'] : 0;

    if ($total_campanhas >= 9) {
        $erro = 'Você já atingiu o limite de 9 campanhas. Exclua uma campanha para criar outra.';
    }

    $foto_campanha = '';

    if (isset($_FILES['foto_campanha']) && $_FILES['foto_campanha']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['foto_campanha'];
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $permitidas, true)) {
            $pasta = 'uploads/';
            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $nomeUnico = time() . '_' . uniqid('', true) . '.' . $ext;
            $destino = $pasta . $nomeUnico;

            if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
                $foto_campanha = $nomeUnico;
            } else {
                $erro = 'Não foi possível salvar a imagem da campanha.';
            }
        } else {
            $erro = 'Formato de imagem inválido. Use JPG, PNG ou WEBP.';
        }
    }

    if (empty($erro)) {
        $nome_campanha = mysqli_real_escape_string($conexao, $nome_campanha);
        $descricao_campanha = mysqli_real_escape_string($conexao, $descricao_campanha);
        $foto_campanha = mysqli_real_escape_string($conexao, $foto_campanha);
        $token_convite = bin2hex(random_bytes(16));

        $sql = "INSERT INTO campanha (nome_campanha, descricao_campanha, id_usuario, token_convite" . ($foto_campanha !== '' ? ', foto_campanha' : '') . ")
                VALUES ('$nome_campanha', '$descricao_campanha', '$id_usuario', '$token_convite'" . ($foto_campanha !== '' ? ", '$foto_campanha'" : '') . ")";

        if (mysqli_query($conexao, $sql)) {
            $id_nova = mysqli_insert_id($conexao);
            header('Location: detalhesCampanha.php?id=' . (int) $id_nova);
            exit();
        }

        $erro = 'Erro ao criar a campanha: ' . mysqli_error($conexao);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Campanha</title>
    <link rel="stylesheet" href="home.css">
    <style>
        :root {
            --bg: #efefef;
            --panel: #f5f5f5;
            --card: #ffffff;
            --border: #dedede;
            --text: #1a1a1a;
            --muted: #666;
            --red: #d51f1f;
            --red-dark: #b30d0d;
            --shadow: 0 18px 40px rgba(0,0,0,0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 400;
        }

        .page-shell {
            max-width: 1030px;
            margin: 28px auto 40px;
            padding: 0 18px;
        }

        .editor-shell {
            background: rgba(255,255,255,0.3);
            border: 1px solid rgba(0,0,0,0.04);
            border-radius: 18px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .editor-topbar {
            background: #f0f0f0;
            border-bottom: 1px solid #dcdcdc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            gap: 14px;
        }

        .editor-brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #242424;
        }

        .editor-bread {
            color: #666;
            font-size: 12px;
        }

        .create-layout {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 700px;
            background: #f3f3f3;
        }

        .preview-panel {
            display: none;
        }

        .mockup-stage {
            width: 100%;
            max-width: 520px;
            min-height: 560px;
            border-radius: 22px;
            background: #f1f1f1;
            border: 1px solid #d8d8d8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px;
        }

        .mockup-card {
            width: 100%;
            max-width: 430px;
            background: #f7f7f7;
            border: 1px solid #d7d7d7;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 12px 28px rgba(0,0,0,0.05);
        }

        .mockup-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            gap: 8px;
        }

        .mockup-nav .nav-left,
        .mockup-nav .nav-right {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .nav-pill {
            background: #fff;
            border: 1px solid #d7d7d7;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 11px;
            color: #444;
            font-weight: 600;
        }

        .nav-brand {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #111;
        }

        .campaign-preview {
            background: #f1f1f1;
            border: 1px solid #d9d9d9;
            border-radius: 12px;
            padding: 18px;
        }

        .mockup-nav .nav-left,
        .mockup-nav .nav-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-brand {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #111;
        }

        .nav-pill {
            background: #fff;
            border: 1px solid #d7d7d7;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 11px;
            color: #444;
            font-weight: 600;
        }

        .preview-visual {
            border-radius: 12px;
            overflow: hidden;
            background: #e9e9e9;
            border: 1px solid #d7d7d7;
            height: 160px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .preview-visual img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .preview-visual.has-image img {
            display: block;
        }

        .placeholder-img {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #d9d9d9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: #7b7b7b;
        }

        .preview-title {
            font-size: 18px;
            margin: 0 0 10px;
            font-weight: 700;
            color: #1d1d1d;
            min-height: 26px;
        }

        .preview-desc {
            min-height: 46px;
            margin: 0;
            color: #5b5b5b;
            line-height: 1.5;
            font-size: 14px;
        }

        .form-panel {
            padding: 28px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: rgba(255,255,255,0.45);
        }

        .form-panel > form {
            width: min(100%, 420px);
        }

        .form-header {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 18px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #202020;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 999px;
            font-weight: 600;
            padding: 8px 0;
            margin-bottom: 10px;
        }

        .btn-back:hover { color: var(--red); }

        .form-panel h1 {
            margin: 0 0 18px;
            font-size: clamp(26px, 3vw, 34px);
            font-weight: 700;
            color: #1a1a1a;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field label {
            font-size: 13px;
            color: #242424;
            font-weight: 600;
        }

        .field input,
        .field textarea {
            width: 100%;
            border: 1px solid #d7d7d7;
            background: #f8f8f8;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 15px;
            color: #1a1a1a;
            font-family: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }

        .field textarea {
            resize: vertical;
            min-height: 120px;
        }

        .field input:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(213,31,31,0.08);
        }

        .upload-box {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 90px;
            border: 1px dashed #d3d3d3;
            border-radius: 12px;
            background: #f8f8f8;
            position: relative;
            overflow: hidden;
        }

        .upload-box input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .upload-trigger {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #1a1a1a;
            font-weight: 600;
            cursor: pointer;
            pointer-events: none;
        }

        .upload-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #d7d7d7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 52px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(180deg, var(--red) 0%, var(--red-dark) 100%);
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(213,31,31,0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 20px rgba(213,31,31,0.26);
        }

        .error-box {
            background: #fbe7e7;
            border: 1px solid #f1b3b3;
            color: #8b1d1d;
            border-radius: 10px;
            padding: 12px 14px;
            font-weight: 600;
            width: min(100%, 420px);
            margin-bottom: 10px;
        }

        @media (max-width: 920px) {
            .create-layout {
                grid-template-columns: 1fr;
            }

            .preview-panel {
                border-right: none;
                border-bottom: 1px solid #dddddd;
            }
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
        <div class="header-right"></div>
    </header>

    <div class="page-shell">
        <div class="editor-shell">
            <div class="editor-topbar">
                <div class="editor-brand">🎲 Nova campanha</div>
                <div class="editor-bread">Campanhas / Criar</div>
            </div>

            <div class="create-layout">
                <div class="form-panel" style="padding-top: 30px; padding-bottom: 30px;">
                    <div class="form-header">
                        <a href="campanhas.php" class="btn-back">← Voltar</a>
                    </div>

                    <?php if (!empty($erro)): ?>
                        <div class="error-box"><?php echo htmlspecialchars($erro); ?></div>
                    <?php endif; ?>

                    <h1>Criar campanha</h1>

                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="field">
                            <label for="nome_campanha">Nome da Campanha</label>
                            <input type="text" id="nome_campanha" name="nome_campanha" value="<?php echo htmlspecialchars($_POST['nome_campanha'] ?? ''); ?>" placeholder="Nome da campanha" required>
                        </div>

                        <div class="field">
                            <label for="descricao_campanha">Breve descrição</label>
                            <textarea id="descricao_campanha" name="descricao_campanha" placeholder="Descreva a atmosfera, a proposta e o tema da sua campanha" required><?php echo htmlspecialchars($_POST['descricao_campanha'] ?? ''); ?></textarea>
                        </div>

                        <div class="field">
                            <label for="foto_campanha">Foto da campanha</label>
                            <div class="upload-box">
                                <input type="file" id="foto_campanha" name="foto_campanha" accept="image/*">
                                <span class="upload-trigger">
                                    <span class="upload-icon">📷</span>
                                    <span>Adicionar imagem</span>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Criar campanha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const nomeInput = document.getElementById('nome_campanha');
        const descInput = document.getElementById('descricao_campanha');
        const tituloPreview = document.getElementById('previewTitulo');
        const descPreview = document.getElementById('previewDescricao');
        const previewVisual = document.getElementById('previewVisual');
        const previewImage = document.getElementById('previewImage');
        const previewPlaceholder = document.getElementById('previewPlaceholder');
        const fileInput = document.getElementById('foto_campanha');

        function atualizarPreview() {
            const nome = nomeInput.value.trim() || 'Nome da Campanha';
            const descricao = descInput.value.trim() || 'Breve descrição da sua campanha, temática e objetivo da mesa.';

            tituloPreview.textContent = nome;
            descPreview.textContent = descricao;
        }

        nomeInput.addEventListener('input', atualizarPreview);
        descInput.addEventListener('input', atualizarPreview);

        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                previewVisual.classList.remove('has-image');
                previewPlaceholder.style.display = 'flex';
                previewImage.src = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                previewImage.src = event.target.result;
                previewVisual.classList.add('has-image');
                previewPlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });

        atualizarPreview();
    </script>

    <script src="assets/site.min.js" defer></script>
</body>
</html>