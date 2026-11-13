<?php
session_start();
require_once 'conecta.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario_logado = (int) $_SESSION['id_usuario'];
$id_campanha = isset($_POST['id_campanha']) ? (int) $_POST['id_campanha'] : 0;
$id_ficha = isset($_POST['id_ficha']) ? (int) $_POST['id_ficha'] : 0;

if ($id_campanha <= 0 || $id_ficha <= 0) {
    header("Location: campanhas.php");
    exit();
}

// TRAVA DE SEGURANÇA: Garante que apenas o Mestre da campanha pode remover fichas
$sql_mestre = "SELECT id_usuario FROM campanha WHERE id_campanha = $id_campanha";
$res_mestre = mysqli_query($conexao, $sql_mestre);

if ($res_mestre && mysqli_num_rows($res_mestre) > 0) {
    $campanha = mysqli_fetch_assoc($res_mestre);
    
    if ((int) $campanha['id_usuario'] === $id_usuario_logado) {
        // Apaga o vínculo da ficha com a campanha
        $sql_delete = "DELETE FROM campanha_fichas WHERE id_campanha = $id_campanha AND id_ficha = $id_ficha";
        mysqli_query($conexao, $sql_delete);
    }
}

header("Location: detalhesMestre.php?id=" . $id_campanha);
exit();
?>