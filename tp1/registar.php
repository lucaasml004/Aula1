<?php
require_once 'config.php';
session_start();

// Se o utilizador já tiver sessão iniciada, deve ir para a página de trabalho porque o login já passou
if (isset($_SESSION['perfil'])) { header("Location: dashboard.php"); exit; }

$erro = "";
$sucesso = "";

// LÓGICA DE REGISTO NA PLATAFORMA QUANDO O BOTÃO É CLICADO (FORMULÁRIO POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // FUNÇÃO "TRIM": Remove os espaços acidentais deixados pela pessoa (ex:"  maria  " -> "maria")
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    // SISTEMA DE SEGURANÇA 1: Criptografia da Password
    // O Hash transforma a "senha" visível numa série de caracteres como "$2y$10$t3r..." 
    // Nunca guardamos palavras-passe em estado visível/texto aberto!
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // SISTEMA DE SEGURANÇA 2: Regra de E-mail Único
    // Prepara a verificação pedindo à BD para procurar contas já submetidas com esse email
    $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        // Se encontrei alguém (fetch = true), dá erro e recusa.
        $erro = "Já existe uma conta com este e-mail!";
    } else {
        // O Aluno é "limpo" (não existe). Processo de Injeção dos 3 dados na Base + Cargo ("aluno" por default)
        $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, senha, perfil) VALUES (?, ?, ?, 'aluno')");
        if ($stmt->execute([$nome, $email, $senha_hash])) {
            $sucesso = "Conta criada com sucesso! Já pode fazer login.";
        } else {
            $erro = "Erro ao criar conta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta | IPCA Portal</title>
    <!-- Inclui Bibliotecas standard de layout  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Link para o Nosso CSS customizado  onde configuramos Modo Escuro Claro (Estilo visual) -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo"><i class="fa-solid fa-user-plus"></i></div>
            <h3 class="auth-title">Registo de Aluno</h3>
            
            <!-- Mostra ERRO VÍSÍVEL em caso de email já existir -->
            <?php if($erro): ?> <div class="alert alert-danger mb-4"><i class="fa-solid fa-circle-exclamation me-2"></i><?= $erro ?></div> <?php endif; ?>
            
            <!-- Mostra MENSAGEM VERDE GIGANTE (SUCESSO) que esconde o formulário porque a conta já foi criada! -->
            <?php if($sucesso): ?> 
                <div class="alert alert-success text-center p-4">
                    <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                    <p class="mb-4"><strong><?= $sucesso ?></strong></p>
                    <a href="index.php" class="btn btn-success w-100">Ir para o Login <i class="fa-solid fa-arrow-right ms-2"></i></a>
                </div> 
            <?php endif; ?>
            
            <!-- Formulário para Criar Conta! (O bloco só aparece até o Registo acabar com sucesso!) -->
            <?php if(!$sucesso): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nome Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-regular fa-id-badge"></i></span>
                        <input type="text" name="nome" class="form-control border-start-0 ps-0" placeholder="O seu nome..." required>
                    </div>
                </div>
                <!-- etc. (o método é POST para a informação nunca aparecer na Barra de Endereço URL ao carregar) -->
                <div class="mb-3">
                    <label class="form-label">Email Institucional</label>
                     <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="exemplo@ipca.pt" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Palavra-passe</label>
                     <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="senha" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100 mb-4 py-2 mt-2">Criar Conta</button>
                <div class="text-center mt-3">
                    <span class="text-muted">Já tem conta?</span> <a href="index.php" class="auth-link">Iniciar Sessão</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Link JS para animar a mudança de cor do ecrã! -->
    <script src="assets/js/theme.js"></script>
</body>
</html>
