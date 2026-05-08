<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['perfil'])) { header("Location: dashboard.php"); exit; }

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_aluno'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = ? AND perfil = 'aluno'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha'])) { 
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['perfil'] = 'aluno';
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Candidato não encontrado ou senha inválida.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Candidato | IPCA</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-split">
        <div class="auth-visual">
            <img src="https://images.unsplash.com/photo-1541339907198-e08759df9a73?auto=format&fit=crop&w=1200&q=80" alt="IPCA Campus">
            <div class="auth-visual-content">
                <i class="fa-solid fa-graduation-cap fa-4x mb-4"></i>
                <h2 class="display-5 fw-bold mb-3">Futuro em Construção.</h2>
                <p class="lead">Inicie a sua jornada académica no Instituto Politécnico do Cávado e do Ave.</p>
            </div>
        </div>
        <div class="auth-form-container">
            <div class="auth-card-professional">
                <div class="auth-header">
                    <h1>Login do Aluno</h1>
                    <p class="text-muted">Acesso exclusivo para consulta de matrículas e pautas.</p>
                </div>

                <?php if($erro): ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-size: 0.875rem;">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $erro ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="login_aluno" value="1">
                    <div class="input-group-modern">
                        <label class="input-label">Email Institucional</label>
                        <input type="email" name="email" class="control-styled" placeholder="nome@ipca.pt" required>
                    </div>
                    <div class="input-group-modern">
                        <label class="input-label">Palavra-passe</label>
                        <input type="password" name="senha" class="control-styled" placeholder="••••••••" required>
                    </div>
                    
                    <button type="submit" class="btn-primary-pro">
                        Entrar no Portal <i class="fa-solid fa-arrow-right"></i>
                    </button>

                    <div style="text-center; margin-top: 2rem; font-size: 0.875rem;">
                        <span class="text-muted">É pessoal docente ou administrativo?</span>
                        <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 600;"> Login Staff</a>
                    </div>
                    <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--border);">
                    <div style="text-align: center;">
                        <p class="text-muted mb-2">Ainda não tem conta?</p>
                        <a href="registar.php" style="display: block; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; color: var(--text-heading); text-decoration: none; font-weight: 600;">Candidatar-me agora</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
