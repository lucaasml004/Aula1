<?php
// Inclui dados de acesso à base de dados e Sessão
require_once '../config.php';
session_start();

// Esta página lida diretamente com os dados sensíveis dos estudantes, por isso só perfis 'aluno' entram.
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'aluno') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Recebemos os inputs preenchidos pelo form do Aluno
    $user_id = $_SESSION['user_id'];
    $curso_id = $_POST['curso_id'];
    $nome_aluno = $_POST['nome_aluno'];
    $turma = $_POST['turma'];
    $data_nascimento = $_POST['data_nascimento'];
    $bi = $_POST['bi'];
    
    // Verifica se o aluno clicou em "Rascunho" ou "Submeter para Validação"
    $acao = $_POST['acao']; 
    $estado_novo = ($acao == 'Submetida') ? 'Submetida' : 'Rascunho';

    // 2. Procuramos se já existe alguma "folha/ficha" deste aluno na BD
    $stmt = $pdo->prepare("SELECT id FROM fichas_aluno WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $ficha_existente = $stmt->fetch();

    $nome_foto = null;
    
    // 3. SEGURANÇA E UPLOAD DA FOTO DE PERFIL
    // O sistema verifica se existe um upload de foto E se ele chegou íntegro (UPLOAD_ERR_OK)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        // Impede hackers de carregarem scripts: só imagens JPG, PNG ou JPEG de até 2 megabytes (!2000000 bytes)
        if (in_array($ext, ['jpg', 'png', 'jpeg']) && $_FILES['foto']['size'] < 2000000) {
            // Gera um nome encriptado único (+ carimbo do tempo) para evitar substituir fotos antigas sem querer
            $nome_foto = time() . "_" . uniqid() . "." . $ext;
            // Move do alojamento temporário para a nossa pasta "uploads" visível ao Gestor
            move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/" . $nome_foto);
        }
    }

    // 4. GRAVAÇÃO DOS DADOS
    if ($ficha_existente) {
        // ROTINA "UPDATE": Se a ficha já existia (Era um Rascunho), então Atualizamos os dados na BD
        $sql = "UPDATE fichas_aluno SET curso_id=?, nome_aluno=?, turma=?, data_nascimento=?, bi=?, estado=?";
        $params = [$curso_id, $nome_aluno, $turma, $data_nascimento, $bi, $estado_novo];
        
        // Regra Especial: Só inserimos o código do nome_foto no update se o upload foi bem sucedido.
        // Se ele não colocou foto desta vez, mantemos a que ele já tinha registado!
        if ($nome_foto) {
            $sql .= ", foto=?";
            $params[] = $nome_foto;
        }
        $sql .= " WHERE user_id=?";
        $params[] = $user_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // ROTINA "INSERT": O aluno nunca tinha preenchido nada, então criámos a Entrada do Zero!
        $stmt = $pdo->prepare("INSERT INTO fichas_aluno (user_id, curso_id, nome_aluno, turma, data_nascimento, bi, foto, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $curso_id, $nome_aluno, $turma, $data_nascimento, $bi, $nome_foto, $estado_novo]);
    }

    // Assinala que o formulário foi enviado ("Sucesso") e redireciona.
    header("Location: ../dashboard.php?page=minha_ficha&msg=sucesso");
    exit;
}
