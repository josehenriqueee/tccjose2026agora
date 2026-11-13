<?php
// Valida e armazena arquivos de imagem enviados por usuários autenticados.
session_start();
require_once 'conecta.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];

    // 1. Verificação de erros nativos do PHP
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        die("Erro ao enviar o arquivo. Código: " . $arquivo['error']);
    }

    $nome = $arquivo['name'];
    $tamanho = $arquivo['size'];
    $tmp_name = $arquivo['tmp_name'];

    // 2. Validação de Tamanho (2MB = 2 * 1024 * 1024 bytes)
    $tamanhoMax = 2 * 1024 * 1024;
    if ($tamanho > $tamanhoMax) {
        die("Arquivo muito grande. O tamanho máximo permitido é 2MB.");
    }

    // 3. Validação de extensão (Apenas Imagens)
    $extensao = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extensao, $extensoesPermitidas)) {
        die("Tipo de arquivo inválido. Apenas JPG, JPEG, PNG e WEBP são permitidos.");
    }

    // 4. Criação do Nome Único
    $nomeUnico = time() . "_" . uniqid() . "." . $extensao;

    // 5. Garantir que a pasta 'uploads/' existe
    $pastaDestino = "uploads/";
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }

    $destino = $pastaDestino . $nomeUnico;

    // 6. Mover o arquivo para a pasta final
    if (move_uploaded_file($tmp_name, $destino)) {
        
        // Se o upload for para uma CAMPANHA
        if (isset($_POST['id_campanha'])) {
            $id_campanha = (int) $_POST['id_campanha'];
            
            // Certifique-se de que existe a coluna 'foto_campanha' na tabela 'campanha'
            $sql = "UPDATE campanha SET foto_campanha = '$nomeUnico' WHERE id_campanha = $id_campanha";
            
            if (mysqli_query($conexao, $sql)) {
                header("Location: detalhesMestre.php?id=" . $id_campanha);
                exit();
            }
        }

        // Se o upload for para uma FICHA DE PERSONAGEM
        if (isset($_POST['id_ficha'])) {
            $id_ficha = (int) $_POST['id_ficha'];
            
            // Certifique-se de que existe a coluna 'foto_personagem' na tabela 'ficha_personagem'
            $sql = "UPDATE ficha_personagem SET foto_personagem = '$nomeUnico' WHERE id_ficha = $id_ficha";
            
            if (mysqli_query($conexao, $sql)) {
                header("Location: fichas.php");
                exit();
            }
        }

        echo "Imagem enviada com sucesso!";
    } else {
        echo "Erro ao mover o arquivo para a pasta de destino.";
    }
} else {
    header("Location: home.php");
    exit();
}
?>