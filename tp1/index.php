<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['perfil'])) { header("Location: dashboard.php"); exit; }

$erro = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verificacao de password segura com password_verify
    if ($user && password_verify($senha, $user['senha'])) { 
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['perfil'] = $user['perfil'];
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Credenciais invalidas!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login | IPCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 400px; padding: 2rem; border-radius: 15px; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4">IPCA Portal</h3>
        <?php if($erro): ?> <div class="alert alert-danger small"><?= $erro ?></div> <?php endif; ?>
        <form method="POST">
            <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label>Senha</label><input type="password" name="senha" class="form-control" required></div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Entrar</button>
            <div class="text-center">
                <a href="registar.php" class="text-decoration-none">Criar Conta de Aluno</a>
            </div>
        </form>
    </div>
</body>
</html>