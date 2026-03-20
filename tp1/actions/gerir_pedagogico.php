<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'gestor') {
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['acao'])) {
    if ($_POST['acao'] == 'add_curso') {
        $nome = trim($_POST['nome_curso']);
        if ($nome) {
            $stmt = $pdo->prepare("INSERT INTO cursos (nome) VALUES (?)");
            $stmt->execute([$nome]);
        }
        header("Location: ../dashboard.php?page=cursos");
        exit;
    }
    
    if ($_POST['acao'] == 'add_uc') {
        $nome = trim($_POST['nome_uc']);
        if ($nome) {
            $stmt = $pdo->prepare("INSERT INTO unidades_curriculares (nome) VALUES (?)");
            $stmt->execute([$nome]);
        }
        header("Location: ../dashboard.php?page=ucs");
        exit;
    }

    if ($_POST['acao'] == 'add_plano') {
        $curso_id = $_POST['curso_id'];
        $uc_id = $_POST['uc_id'];
        $ano = $_POST['ano'];
        $semestre = $_POST['semestre'];
        try {
            $stmt = $pdo->prepare("INSERT INTO plano_estudos (curso_id, uc_id, ano, semestre) VALUES (?, ?, ?, ?)");
            $stmt->execute([$curso_id, $uc_id, $ano, $semestre]);
            header("Location: ../dashboard.php?page=plano&curso_id=$curso_id&msg=sucesso");
        } catch(PDOException $e) {
            // Evitar duplicados (PK composta)
            header("Location: ../dashboard.php?page=plano&curso_id=$curso_id&msg=erro_duplicado");
        }
        exit;
    }
}
