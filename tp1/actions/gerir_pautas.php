<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'funcionario') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['criar_pauta'])) {
        $uc_id = $_POST['uc_id'];
        $epoca = $_POST['epoca'];
        $ano_letivo = trim($_POST['ano_letivo']);
        
        // cria pauta
        $stmt = $pdo->prepare("INSERT INTO pautas (uc_id, ano_letivo, epoca) VALUES (?, ?, ?)");
        $stmt->execute([$uc_id, $ano_letivo, $epoca]);
        $pauta_id = $pdo->lastInsertId();
        
        // Pega todos os alunos aprovados matriculados nos cursos que tem essa UC
        // Ou mais simples: Aluno com matricula aprovada no curso que contem essa uc
        $sql = "SELECT m.aluno_id FROM matriculas m 
                JOIN plano_estudos pe ON m.curso_id = pe.curso_id
                WHERE m.estado = 'Aprovado' AND pe.uc_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uc_id]);
        $alunos = $stmt->fetchAll();
        
        // Insere registos de notas nulos
        foreach($alunos as $a) {
            $stmt = $pdo->prepare("INSERT INTO notas (pauta_id, aluno_id, nota_final) VALUES (?, ?, NULL)");
            try { $stmt->execute([$pauta_id, $a['aluno_id']]); } catch(Exception $e) {}
        }
        
        header("Location: ../dashboard.php?page=lancar_notas&id=" . $pauta_id);
        exit;
    }

    if (isset($_POST['lancar_notas'])) {
        $pauta_id = $_POST['pauta_id'];
        if(isset($_POST['notas'])) {
            foreach($_POST['notas'] as $aluno_id => $nota) {
                $nota = $nota !== '' ? $nota : NULL;
                $stmt = $pdo->prepare("UPDATE notas SET nota_final = ? WHERE pauta_id = ? AND aluno_id = ?");
                $stmt->execute([$nota, $pauta_id, $aluno_id]);
            }
        }
        header("Location: ../dashboard.php?page=pautas");
        exit;
    }
}
