<?php
// Ligação à base de dados e arranque do sistema de sessão.
require_once '../config.php';
session_start();

// Bloqueia tentativas de acesso por pessoas que não iniciaram sessão.
if (!isset($_SESSION['perfil'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // FUNCIONALIDADE DO ALUNO: EFETUAR O PEDIDO DE MATRÍCULA
    if ($_SESSION['perfil'] == 'aluno' && isset($_POST['pedir_matricula'])) {
        $curso_id = $_POST['curso_id'];
        $aluno_id = $_SESSION['user_id'];
        
        // Proteção Lógica: Evita que o aluno se matricule 2 vezes no mesmo curso 
        // a não ser que o seu pedido anterior tenha sido 'Rejeitado'.
        $stmt = $pdo->prepare("SELECT id FROM matriculas WHERE aluno_id = ? AND curso_id = ? AND estado != 'Rejeitado'");
        $stmt->execute([$aluno_id, $curso_id]);
        
        if (!$stmt->fetch()) { 
            // Se o aluno ainda não tentou entrar, insere o pedido na base de dados com estado "Pendente".
            $stmt = $pdo->prepare("INSERT INTO matriculas (aluno_id, curso_id, estado) VALUES (?, ?, 'Pendente')");
            $stmt->execute([$aluno_id, $curso_id]);
        }
        
        // Devolve o aluno à sua página de histórico de matrículas
        header("Location: ../dashboard.php?page=matricula_status");
        exit;
    }

    // FUNCIONALIDADE DO FUNCIONÁRIO: VALIDAR OU REJEITAR UMA MATRÍCULA
    if ($_SESSION['perfil'] == 'funcionario' && isset($_POST['decisao'])) {
        $matricula_id = $_POST['matricula_id']; // Qual é a matrícula em processo?
        $decisao = $_POST['decisao'];           // Decidimos 'Aprovar' ou 'Rejeitar'?
        $obs = trim($_POST['observacoes']);     // Justificação dada pelo funcionário (ex: "Falta de Vagas")
        $resp_id = $_SESSION['user_id'];        // Qual é o funcionário que se encarregou disto?
        
        // Regista a decisão na Base de Dados e altera o estado da Matrícula para o que escolhemos.
        $stmt = $pdo->prepare("UPDATE matriculas SET estado = ?, observacoes = ?, responsavel_id = ?, data_decisao = NOW() WHERE id = ?");
        $stmt->execute([$decisao, $obs, $resp_id, $matricula_id]);
        
        // Devolve o funcionário à tabela com as restantes matrículas pendentes
        header("Location: ../dashboard.php?page=validar_pedidos");
        exit;
    }
}
