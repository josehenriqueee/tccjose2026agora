<?php
session_start();
include "conecta.php";

// verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_ficha = $_POST['id_ficha'];

// consulta se a ficha existe e pertence ao usuário logado
$sql = "SELECT * FROM ficha_personagem WHERE id_ficha = '$id_ficha' AND id_usuario = '$id_usuario'";
$res = mysqli_query($conexao, $sql);

// se encontrou a ficha...
if (mysqli_affected_rows($conexao) > 0) {
    $dados = mysqli_fetch_assoc($res);

    // se tiver foto salva, apaga da pasta uploads
    if (!empty($dados['foto_personagem'])) {
        $arquivo = "uploads/" . $dados['foto_personagem'];
        if (file_exists($arquivo)) {
            unlink($arquivo);
        }
    }

    // apaga a ficha das campanhas onde ela está cadastrada
    $sql_campanha = "DELETE FROM campanha_fichas WHERE id_ficha = '$id_ficha'";
    mysqli_query($conexao, $sql_campanha);

    // apaga a ficha da tabela principal
    $sql_delete = "DELETE FROM ficha_personagem WHERE id_ficha = '$id_ficha' AND id_usuario = '$id_usuario'";
    mysqli_query($conexao, $sql_delete);
}

// redireciona de volta para a lista de fichas
header("Location: fichas.php");
exit();
?>