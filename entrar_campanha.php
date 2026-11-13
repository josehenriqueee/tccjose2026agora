<?php
// Entrada direta na campanha (usada por formulários que já conhecem o id da campanha).
// O fluxo normal por link de convite é feito em convite.php.
session_start();
require_once 'conecta.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: campanhas.php");
    exit();
}

$id_usuario  = (int) $_SESSION['id_usuario'];
$id_campanha = (int) ($_POST['id_campanha'] ?? 0);
$id_ficha    = (int) ($_POST['id_ficha'] ?? 0);

// A campanha precisa existir e a ficha precisa ser do próprio usuário
$res_c = mysqli_query($conexao, "SELECT id_usuario FROM campanha WHERE id_campanha = $id_campanha");
$res_f = mysqli_query($conexao, "SELECT 1 FROM ficha_personagem WHERE id_ficha = $id_ficha AND id_usuario = $id_usuario");

if (!$res_c || mysqli_num_rows($res_c) == 0 || !$res_f || mysqli_num_rows($res_f) == 0) {
    header("Location: campanhas.php");
    exit();
}

$campanha = mysqli_fetch_assoc($res_c);

// Mestre não entra como jogador na própria campanha; quem já está não entra duas vezes
$res_ja = mysqli_query($conexao, "SELECT 1 FROM campanha_fichas WHERE id_campanha = $id_campanha AND id_usuario = $id_usuario");

if ((int) $campanha['id_usuario'] !== $id_usuario && mysqli_num_rows($res_ja) == 0) {
    mysqli_query($conexao, "INSERT INTO campanha_fichas (id_campanha, id_usuario, id_ficha) VALUES ($id_campanha, $id_usuario, $id_ficha)");
}

header("Location: detalhesCampanha.php?id=" . $id_campanha);
exit();
