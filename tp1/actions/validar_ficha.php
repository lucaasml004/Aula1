<?php
// Configuração da base de dados e arranque da sessão
require_once '../config.php';
session_start();

// Proteção de Rota: Apenas os utilizadores com perfil de 'gestor' podem aceder a esta página.
// Se não for gestor, é reencaminhado de volta para a página inicial.
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'gestor') {
    header("Location: ../index.php");
    exit;
}

// Verifica se os dados foram enviados através do formulário (Método POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ficha_id = $_POST['ficha_id'];       // ID da ficha a ser validada
    $decisao = $_POST['decisao'];         // Decisão do Gestor ('Aprovada' ou 'Rejeitada')
    $obs = trim($_POST['observacoes']);   // Observações/Motivo (opcional ou obrigatório pelo HTML)
    $user_id = $_SESSION['user_id'];      // Quem está a validar (ID do Gestor logado)

    // Medida de Segurança: Garante que apenas os valores 'Aprovada' ou 'Rejeitada' são inseridos na BD
    if (in_array($decisao, ['Aprovada', 'Rejeitada'])) {
        // Atualiza a Ficha de Aluno no banco de dados com a nova decisão, autor e data atual.
        $stmt = $pdo->prepare("UPDATE fichas_aluno SET estado = ?, observacoes = ?, validado_por = ?, data_validacao = NOW() WHERE id = ?");
        $stmt->execute([$decisao, $obs, $user_id, $ficha_id]);
    }

    // Após gravar a decisão, redireciona o Gestor novamente para a página de validações
    header("Location: ../dashboard.php?page=gestao_fichas");
    exit;
}
