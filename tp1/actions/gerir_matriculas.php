<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['perfil'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SESSION['perfil'] == 'aluno' && isset($_POST['pedir_matricula'])) {
        $curso_id = $_POST['curso_id'];
        $aluno_id = $_SESSION['user_id'];
        // Evita duplicados
        $stmt = $pdo->prepare("SELECT id FROM matriculas WHERE aluno_id = ? AND curso_id = ? AND estado != 'Rejeitado'");
        $stmt->execute([$aluno_id, $curso_id]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO matriculas (aluno_id, curso_id, estado) VALUES (?, ?, 'Pendente')");
            $stmt->execute([$aluno_id, $curso_id]);
        }
        header("Location: ../dashboard.php?page=matricula_status");
        exit;
    }

    if ($_SESSION['perfil'] == 'funcionario' && isset($_POST['decisao'])) {
        $matricula_id = $_POST['matricula_id'];
        $decisao = $_POST['decisao'];
        $obs = trim($_POST['observacoes']);
        $resp_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("UPDATE matriculas SET estado = ?, observacoes = ?, responsavel_id = ?, data_decisao = NOW() WHERE id = ?");
        $stmt->execute([$decisao, $obs, $resp_id, $matricula_id]);
        header("Location: ../dashboard.php?page=validar_pedidos");
        exit;
    }
}
