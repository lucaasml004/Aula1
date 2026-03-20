<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['perfil'])) { header("Location: index.php"); exit; }

$perfil = $_SESSION['perfil'];
$user_id = $_SESSION['user_id'];
$page = $_GET['page'] ?? 'home';

// --- LOGOUT ---
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }

// ============================================================
// LÓGICA DE PROCESSAMENTO (ACTIONS)
// ============================================================

// --- ALUNO: Submeter Ficha com Foto (RF3) ---
if (isset($_POST['submeter_ficha'])) {
    $morada = $_POST['morada'];
    $bi = $_POST['bi'];
    $nome_foto = time() . "_" . $_FILES['foto']['name']; // Nome único para evitar sobreposição
    $alvo = "uploads/" . basename($nome_foto);

    $extensao = strtolower(pathinfo($alvo, PATHINFO_EXTENSION));
    if (in_array($extensao, ['jpg', 'png', 'jpeg']) && $_FILES['foto']['size'] < 2000000) {
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $alvo)) {
            $stmt = $pdo->prepare("INSERT INTO fichas_aluno (user_id, morada, bi, foto, estado) VALUES (?, ?, ?, ?, 'Submetida')");
            $stmt->execute([$user_id, $morada, $bi, $nome_foto]);
            header("Location: dashboard.php?page=minha_ficha&msg=sucesso");
            exit;
        }
    }
}

// --- GESTOR: CRUD Cursos (RF2) ---
if (isset($_POST['add_curso']) && $perfil == 'gestor') {
    $nome = trim($_POST['nome_curso']);
    $stmt = $pdo->prepare("INSERT INTO cursos (Nome) VALUES (?)");
    $stmt->execute([$nome]);
    header("Location: dashboard.php?page=cursos"); exit;
}

// --- GESTOR: Validar Ficha de Aluno (RF3.2 + Auditoria) ---
if (isset($_POST['validar_ficha']) && $perfil == 'gestor') {
    $ficha_id = $_POST['ficha_id'];
    $decisao = $_POST['decisao'];
    $obs = $_POST['observacoes'];
    $stmt = $pdo->prepare("UPDATE fichas_aluno SET estado = ?, obs_gestor = ?, validado_por = ?, data_validacao = NOW() WHERE id = ?");
    $stmt->execute([$decisao, $obs, $user_id, $ficha_id]);
    header("Location: dashboard.php?page=gestao_fichas"); exit;
}

// --- FUNCIONÁRIO: Aprovar Matrícula (RF4 + Auditoria) ---
if (isset($_GET['aprovar_mat'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("UPDATE matriculas SET estado = 'Aprovado', responsavel_id = ?, data_decisao = NOW() WHERE id = ?");
    $stmt->execute([$user_id, $id]);
    header("Location: dashboard.php?page=validar_pedidos"); exit;
}

// --- FUNCIONÁRIO: Lançar Nota na Pauta (RF5) ---
if (isset($_POST['lancar_nota'])) {
    $stmt = $pdo->prepare("INSERT INTO pautas (uc_id, aluno_id, nota_final, epoca, ano_letivo) VALUES (?, ?, ?, ?, '2025/26')");
    $stmt->execute([$_POST['uc_id'], $_POST['aluno_id'], $_POST['nota'], $_POST['epoca']]);
    header("Location: dashboard.php?page=pautas&msg=nota_ok"); exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>IPCA | Portal Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background: #0f172a; color: white; position: fixed; width: 250px; }
        .nav-link { color: #cbd5e1; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: #1e293b; color: white; border-radius: 8px; }
        .main { margin-left: 250px; padding: 30px; background: #f8f9fa; min-height: 100vh; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="sidebar p-3">
    <h4 class="text-center mb-4">IPCA Portal</h4>
    <div class="text-center mb-4"><span class="badge bg-info"><?= strtoupper($perfil) ?></span></div>
    
    <ul class="nav flex-column">
        <a class="nav-link <?= $page=='home'?'active':'' ?>" href="?page=home"><i class="fa fa-home me-2"></i> Início</a>
        
        <?php if($perfil == 'aluno'): ?>
            <a class="nav-link <?= $page=='minha_ficha'?'active':'' ?>" href="?page=minha_ficha"><i class="fa fa-user me-2"></i> Minha Ficha</a>
            <a class="nav-link <?= $page=='matricula'?'active':'' ?>" href="?page=matricula"><i class="fa fa-file-signature me-2"></i> Matrícula</a>
        <?php endif; ?>

        <?php if($perfil == 'gestor'): ?>
            <a class="nav-link <?= $page=='cursos'?'active':'' ?>" href="?page=cursos"><i class="fa fa-book me-2"></i> Cursos e UCs</a>
            <a class="nav-link <?= $page=='gestao_fichas'?'active':'' ?>" href="?page=gestao_fichas"><i class="fa fa-id-card me-2"></i> Validar Fichas</a>
        <?php endif; ?>

        <?php if($perfil == 'funcionario' || $perfil == 'gestor'): ?>
            <a class="nav-link <?= $page=='validar_pedidos'?'active':'' ?>" href="?page=validar_pedidos"><i class="fa fa-check-double me-2"></i> Validar Pedidos</a>
            <a class="nav-link <?= $page=='pautas'?'active':'' ?>" href="?page=pautas"><i class="fa fa-table me-2"></i> Pautas</a>
        <?php endif; ?>

        <hr class="bg-secondary">
        <a class="nav-link text-danger" href="?logout=1"><i class="fa fa-sign-out-alt me-2"></i> Sair</a>
    </ul>
</div>

<main class="main">
    <header class="d-flex justify-content-between mb-4">
        <h4>Olá, <?= $_SESSION['nome'] ?></h4>
        <div class="text-muted"><?= date('d/m/Y') ?></div>
    </header>

    <?php if($page == 'home'): ?>
        <div class="row g-4">
            <div class="col-md-12"><div class="card p-5 text-center"><h3>Bem-vindo ao Sistema de Gestão IPCA</h3></div></div>
        </div>

    <?php elseif($page == 'minha_ficha'): ?>
        <div class="card p-4">
            <h4>Ficha de Aluno (RF3)</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3"><label>Morada</label><input type="text" name="morada" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>BI / CC</label><input type="text" name="bi" class="form-control" required></div>
                </div>
                <div class="mb-3"><label>Fotografia</label><input type="file" name="foto" class="form-control" required></div>
                <button type="submit" name="submeter_ficha" class="btn btn-primary">Submeter Ficha</button>
            </form>
        </div>

    <?php elseif($page == 'cursos' && $perfil == 'gestor'): ?>
        <div class="card p-4 mb-4">
            <h5>Adicionar Novo Curso</h5>
            <form method="POST" class="d-flex gap-2">
                <input type="text" name="nome_curso" class="form-control" placeholder="Ex: Engenharia Informática" required>
                <button name="add_curso" class="btn btn-success">Adicionar</button>
            </form>
        </div>
        <div class="card p-4">
            <h5>Cursos Existentes</h5>
            <table class="table">
                <?php $cursos = $pdo->query("SELECT * FROM cursos")->fetchAll();
                foreach($cursos as $c): ?>
                    <tr><td><?= $c['Nome'] ?></td><td class="text-end"><button class="btn btn-sm btn-light">Editar</button></td></tr>
                <?php endforeach; ?>
            </table>
        </div>

    <?php elseif($page == 'gestao_fichas' && $perfil == 'gestor'): ?>
        <h4>Validar Fichas de Alunos</h4>
        <?php $fichas = $pdo->query("SELECT f.*, u.nome FROM fichas_aluno f JOIN utilizadores u ON f.user_id = u.id WHERE f.estado = 'Submetida'")->fetchAll();
        foreach($fichas as $f): ?>
            <div class="card p-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-md-1"><img src="uploads/<?= $f['foto'] ?>" class="img-fluid rounded shadow-sm"></div>
                    <div class="col-md-6"><strong><?= $f['nome'] ?></strong><br><small><?= $f['morada'] ?> | BI: <?= $f['bi'] ?></small></div>
                    <div class="col-md-5">
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="ficha_id" value="<?= $f['id'] ?>">
                            <input type="text" name="observacoes" class="form-control form-control-sm" placeholder="Obs...">
                            <button name="validar_ficha" value="Aprovada" class="btn btn-sm btn-success">Aceitar</button>
                            <button name="validar_ficha" value="Rejeitada" class="btn btn-sm btn-danger">Negar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php elseif($page == 'validar_pedidos'): ?>
        <h4>Matrículas Pendentes (RF4)</h4>
        <div class="card p-3 shadow-sm">
            <table class="table">
                <thead><tr><th>Aluno</th><th>Curso</th><th>Ação</th></tr></thead>
                <tbody>
                    <?php $q = $pdo->query("SELECT m.id, u.nome as aluno, c.Nome as curso FROM matriculas m JOIN utilizadores u ON m.aluno_id = u.id JOIN cursos c ON m.curso_id = c.Id_cursos WHERE m.estado = 'Pendente'")->fetchAll();
                    foreach($q as $row): ?>
                        <tr>
                            <td><?= $row['aluno'] ?></td><td><?= $row['curso'] ?></td>
                            <td><a href="?aprovar_mat=1&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Aprovar Matrícula</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif($page == 'pautas'): ?>
        <h4>Lançamento de Notas (RF5)</h4>
        <div class="card p-4 shadow-sm">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Disciplina (UC)</label>
                        <select name="uc_id" class="form-select">
                            <?php $ucs = $pdo->query("SELECT * FROM unidades_curriculares")->fetchAll();
                            foreach($ucs as $u) echo "<option value='{$u['id']}'>{$u['nome']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Aluno</label>
                        <select name="aluno_id" class="form-select">
                            <?php $alunos = $pdo->query("SELECT * FROM utilizadores WHERE perfil = 'aluno'")->fetchAll();
                            foreach($alunos as $a) echo "<option value='{$a['id']}'>{$a['nome']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-2"><label>Nota (0-20)</label><input type="number" step="0.1" name="nota" class="form-control" required></div>
                    <div class="col-md-2">
                        <label>Época</label>
                        <select name="epoca" class="form-select"><option>Normal</option><option>Recurso</option></select>
                    </div>
                    <div class="col-md-2"><label>&nbsp;</label><button name="lancar_nota" class="btn btn-primary d-block w-100">Registar</button></div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</main>

</body>
</html>