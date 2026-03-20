<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'aluno') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $curso_id = $_POST['curso_id'];
    $nome_aluno = $_POST['nome_aluno'];
    $turma = $_POST['turma'];
    $data_nascimento = $_POST['data_nascimento'];
    $bi = $_POST['bi'];
    $acao = $_POST['acao']; // 'Rascunho' ou 'Submetida'
    $estado_novo = ($acao == 'Submetida') ? 'Submetida' : 'Rascunho';

    $stmt = $pdo->prepare("SELECT id FROM fichas_aluno WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $ficha_existente = $stmt->fetch();

    $nome_foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'png', 'jpeg']) && $_FILES['foto']['size'] < 2000000) {
            $nome_foto = time() . "_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/" . $nome_foto);
        }
    }

    if ($ficha_existente) {
        // UPDATE
        $sql = "UPDATE fichas_aluno SET curso_id=?, nome_aluno=?, turma=?, data_nascimento=?, bi=?, estado=?";
        $params = [$curso_id, $nome_aluno, $turma, $data_nascimento, $bi, $estado_novo];
        if ($nome_foto) {
            $sql .= ", foto=?";
            $params[] = $nome_foto;
        }
        $sql .= " WHERE user_id=?";
        $params[] = $user_id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // INSERT
        $stmt = $pdo->prepare("INSERT INTO fichas_aluno (user_id, curso_id, nome_aluno, turma, data_nascimento, bi, foto, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $curso_id, $nome_aluno, $turma, $data_nascimento, $bi, $nome_foto, $estado_novo]);
    }

    header("Location: ../dashboard.php?page=minha_ficha&msg=sucesso");
    exit;
}
