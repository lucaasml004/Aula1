<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'gestor') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ficha_id = $_POST['ficha_id'];
    $decisao = $_POST['decisao']; // 'Aprovada' ou 'Rejeitada'
    $obs = trim($_POST['observacoes']);
    $user_id = $_SESSION['user_id'];

    if (in_array($decisao, ['Aprovada', 'Rejeitada'])) {
        $stmt = $pdo->prepare("UPDATE fichas_aluno SET estado = ?, observacoes = ?, validado_por = ?, data_validacao = NOW() WHERE id = ?");
        $stmt->execute([$decisao, $obs, $user_id, $ficha_id]);
    }

    header("Location: ../dashboard.php?page=gestao_fichas");
    exit;
}
