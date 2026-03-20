<?php
// Configurações e Ligação à Base de Dados
require_once 'config.php';
// Inicia ou retoma a sessão (para podermos usar a variável super-global $_SESSION)
session_start();

// REGRA DE REDIRECIONAMENTO DE SEGURANÇA
// Se a variável 'perfil' já existir na sessão, significa que o utilizador já está logado.
// Então "saltamos" logo para a página principal para não mostrar a página de login sem precisão!
if (isset($_SESSION['perfil'])) { header("Location: dashboard.php"); exit; }

$erro = ""; // Variável para mostrar mensagens vermelhas no ecrã (Credenciais Inválidas)

// LÓGICA DE LOGIN ESTA AQUI
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];   // E-mail escrito pelo utilizador
    $senha = $_POST['senha'];   // Senha escrita pelo utilizador

    // Proteção Anti-Hacker (Segurança PDO): 
    // Em vez de injetar o $email diretamente no texto do comando (que permite Injeções SQL), 
    // usamos o "?" (Binding) para o servidor analisar como texto literal. 
    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(); // Vai buscar os dados desse utilizador à tabela da BD

    // Verificação Mágica Segura: A palavra passe no banco de dados está "Criptografada" (um Hash ilegível).
    // O comando "password_verify" verifica se a "senha" 1234 corresponde  ao "h2i3urhf239hdq3d" registado na BD!
    if ($user && password_verify($senha, $user['senha'])) { 
        // Sucesso: Guardar os dados na Sessão para o resto das Páginas
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['perfil'] = $user['perfil']; // Importante: 'aluno', 'gestor' ou 'funcionario'
        
        // Entramos! Vai para o Painel de Controlo!
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Credenciais inválidas!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login | IPCA Portal</title>
    <!-- Bibliotecas de Estilos da Internet Boostrap (Layout do Ecrã) e FontAwesome (Bonecos e Ícones) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- O nosso ficheiro customizado onde criámos as sombras e o DARK MODE!! -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo"><i class="fa-solid fa-graduation-cap"></i></div>
            <h3 class="auth-title">Bem-vindo ao IPCA</h3>
            
            <!-- Zona Onde aparece os Erros (Se houver algum erro no PHP, ele cria esta caixa vermelha HTML!) -->
            <?php if($erro): ?> <div class="alert alert-danger mb-4"><i class="fa-solid fa-circle-exclamation me-2"></i><?= $erro ?></div> <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
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
                <button type="submit" class="btn btn-primary w-100 mb-4 py-2 mt-2">Entrar no Portal <i class="fa-solid fa-arrow-right ms-2"></i></button>
                <div class="text-center mt-3">
                    <span class="text-muted">Ainda não tem conta?</span> <a href="registar.php" class="auth-link">Criar Conta de Aluno</a>
                </div>
            </form>
        </div>
    </div>
    <!-- Script de Gestão Dinâmica do Tema de Cores da Página Escuro/Claro que fizemos em JS -->
    <script src="assets/js/theme.js"></script>
</body>
</html>