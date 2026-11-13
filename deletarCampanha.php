<?php
// Exclui uma campanha. Só o MESTRE (quem criou) pode excluir, e apenas via POST.
session_start();
require_once 'conecta.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_campanha'])) {
    header("Location: campanhas.php");
    exit();
}

$id_usuario  = (int) $_SESSION['id_usuario'];
$id_campanha = (int) $_POST['id_campanha'];

// Confirma que a campanha é do usuário logado
$res = mysqli_query($conexao, "SELECT id_campanha FROM campanha WHERE id_campanha = $id_campanha AND id_usuario = $id_usuario");

if ($res && mysqli_num_rows($res) > 0) {
    // Primeiro solta as fichas dos jogadores, depois apaga a campanha
    mysqli_query($conexao, "DELETE FROM campanha_fichas WHERE id_campanha = $id_campanha");
    mysqli_query($conexao, "DELETE FROM campanha WHERE id_campanha = $id_campanha AND id_usuario = $id_usuario");
}

header("Location: campanhas.php");
exit();
