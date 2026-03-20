<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['perfil'])) { header("Location: dashboard.php"); exit; }

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Verifica se email ja existe
    $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $erro = "Ja existe uma conta com este e-mail!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, senha, perfil) VALUES (?, ?, ?, 'aluno')");
        if ($stmt->execute([$nome, $email, $senha_hash])) {
            $sucesso = "Conta criada com sucesso! Ja pode fazer login.";
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
    <title>Criar Conta Aluno | IPCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 400px; padding: 2rem; border-radius: 15px; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4">Registo de Aluno</h3>
        <?php if($erro): ?> <div class="alert alert-danger small"><?= $erro ?></div> <?php endif; ?>
        <?php if($sucesso): ?> 
            <div class="alert alert-success small">
                <?= $sucesso ?><br><br>
                <a href="index.php" class="btn btn-sm btn-success w-100">Ir para o Login</a>
            </div> 
        <?php endif; ?>
        
        <?php if(!$sucesso): ?>
        <form method="POST">
            <div class="mb-3"><label>Nome Completo</label><input type="text" name="nome" class="form-control" required></div>
            <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label>Senha</label><input type="password" name="senha" class="form-control" required></div>
            <button type="submit" class="btn btn-success w-100 mb-3">Registar</button>
            <div class="text-center">
                <a href="index.php" class="text-decoration-none">Ja tenho conta (Login)</a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
