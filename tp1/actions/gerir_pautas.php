<?php
// Inclui o ficheiro de ligação à Base de Dados e inicia a Sessão do Utilizador
require_once '../config.php';
session_start();

// Proteção da Página: Garante que apenas quem tem o perfil de 'funcionario' a pode executar.
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'funcionario') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // FUNCIONALIDADE 1: CRIAR UMA NOVA PAUTA DE AVALIAÇÃO
    if (isset($_POST['criar_pauta'])) {
        $uc_id = $_POST['uc_id'];             // Disciplina selecionada
        $epoca = $_POST['epoca'];             // Época de avaliação (ex: Normal, Recurso)
        $ano_letivo = trim($_POST['ano_letivo']); // Ano escolar (ex: 2023/24)
        
        // 1. Cria a pauta no sistema
        $stmt = $pdo->prepare("INSERT INTO pautas (uc_id, ano_letivo, epoca) VALUES (?, ?, ?)");
        $stmt->execute([$uc_id, $ano_letivo, $epoca]);
        $pauta_id = $pdo->lastInsertId(); // Pega no ID da pauta recém-criada
        
        // 2. Procura automaticamente todos os alunos que estão matriculados ("Aprovados") nos cursos que contêm esta disciplina
        $sql = "SELECT m.aluno_id FROM matriculas m 
                JOIN plano_estudos pe ON m.curso_id = pe.curso_id
                WHERE m.estado = 'Aprovado' AND pe.uc_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uc_id]);
        $alunos = $stmt->fetchAll();
        
        // 3. Insere uma linha de nota vazia (NULL) prévia para cada aluno encontrado, preparando a lista.
        foreach($alunos as $a) {
            $stmt = $pdo->prepare("INSERT INTO notas (pauta_id, aluno_id, nota_final) VALUES (?, ?, NULL)");
            try { $stmt->execute([$pauta_id, $a['aluno_id']]); } catch(Exception $e) { /* Ignora caso o aluno já esteja na pauta acidentalmente */ }
        }
        
        // Redireciona diretamente para o modo de "Lançar Notas" dessa pauta!
        header("Location: ../dashboard.php?page=lancar_notas&id=" . $pauta_id);
        exit;
    }

    // FUNCIONALIDADE 2: GRAVAR AS NOTAS DOS ALUNOS
    if (isset($_POST['lancar_notas'])) {
        $pauta_id = $_POST['pauta_id'];
        
        // Recebe um array com as notas de todos os alunos submetidas num único formulário
        if(isset($_POST['notas'])) {
            foreach($_POST['notas'] as $aluno_id => $nota) {
                // Se a caixa ficou vazia, grava como "NULL", senão grava a nota.
                $nota = $nota !== '' ? $nota : NULL;
                
                // Grava a nota do aluno na base de dados (UPDATE em vez de INSERT porque elas já lá existem).
                $stmt = $pdo->prepare("UPDATE notas SET nota_final = ? WHERE pauta_id = ? AND aluno_id = ?");
                $stmt->execute([$nota, $pauta_id, $aluno_id]);
            }
        }
        
        // Regressa à lista de Pautas
        header("Location: ../dashboard.php?page=pautas");
        exit;
    }
}
