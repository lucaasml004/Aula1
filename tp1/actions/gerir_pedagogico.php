<?php
require_once '../config.php';
session_start();

// O "Coração" Curricular: Apenas os 'gestores' podem adicionar cursos e UCs aqui.
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'gestor') {
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['acao'])) {
    
    // FUNCIONALIDADE: ADICIONAR UM NOVO CURSO À ESCOLA
    if ($_POST['acao'] == 'add_curso') {
        $nome = trim($_POST['nome_curso']); // Elimina espaços extra (ex: 'Engenharia  ' => 'Engenharia')
        if ($nome) {
            // Insere fisicamente o curso na base de dados
            $stmt = $pdo->prepare("INSERT INTO cursos (nome) VALUES (?)");
            $stmt->execute([$nome]);
        }
        header("Location: ../dashboard.php?page=cursos");
        exit;
    }
    
    // FUNCIONALIDADE: ADICIONAR UMA NOVA DISCIPLINA (UC)
    if ($_POST['acao'] == 'add_uc') {
        $nome = trim($_POST['nome_uc']);
        if ($nome) {
            // As UCs são globais (Matemática pode pertencer a vários cursos diferentes depois)
            $stmt = $pdo->prepare("INSERT INTO unidades_curriculares (nome) VALUES (?)");
            $stmt->execute([$nome]);
        }
        header("Location: ../dashboard.php?page=ucs");
        exit;
    }

    // FUNCIONALIDADE: CRIAR PLANO DE ESTUDOS (Juntar UCs e Cursos)
    if ($_POST['acao'] == 'add_plano') {
        $curso_id = $_POST['curso_id']; // Curso Alvo
        $uc_id = $_POST['uc_id'];       // Disciplina Alvo
        $ano = $_POST['ano'];           // Que ano letivo é esta disciplina? (ex: 1º Ano)
        $semestre = $_POST['semestre']; // Que semestre? (1 ou 2)
        
        try {
            // Insere na tabela 'plano_estudos'. É aqui que se dá a "magia" da ligação!
            $stmt = $pdo->prepare("INSERT INTO plano_estudos (curso_id, uc_id, ano, semestre) VALUES (?, ?, ?, ?)");
            $stmt->execute([$curso_id, $uc_id, $ano, $semestre]);
            
            // Sucesso! Retorna com aviso visual positivo
            header("Location: ../dashboard.php?page=plano&curso_id=$curso_id&msg=sucesso");
        } catch(PDOException $e) {
            // Proteção de Conflito: O bloco try/catch apanha o erro se a pessoa já tiver
            // adicionado a disciplina anteriormente ao Plano (evita erros feios no ecrã e avisa que já existe).
            header("Location: ../dashboard.php?page=plano&curso_id=$curso_id&msg=erro_duplicado");
        }
        exit;
    }
}
