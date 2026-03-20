<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['perfil'])) { header("Location: index.php"); exit; }

$perfil = $_SESSION['perfil'];
$user_id = $_SESSION['user_id'];
$page = $_GET['page'] ?? 'home';

if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>IPCA | Portal Academico</title>
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
        <a class="nav-link <?= $page=='home'?'active':'' ?>" href="?page=home"><i class="fa fa-home me-2"></i> Inicio</a>
        
        <?php if($perfil == 'aluno'): ?>
            <a class="nav-link <?= $page=='minha_ficha'?'active':'' ?>" href="?page=minha_ficha"><i class="fa fa-user me-2"></i> Ficha de Aluno</a>
            <a class="nav-link <?= ($page=='fazer_matricula'||$page=='matricula_status')?'active':'' ?>" href="?page=matricula_status"><i class="fa fa-file-signature me-2"></i> Matriculas</a>
        <?php endif; ?>

        <?php if($perfil == 'gestor'): ?>
            <a class="nav-link <?= $page=='cursos'?'active':'' ?>" href="?page=cursos"><i class="fa fa-book me-2"></i> Cursos</a>
            <a class="nav-link <?= $page=='ucs'?'active':'' ?>" href="?page=ucs"><i class="fa fa-list me-2"></i> UCs</a>
            <a class="nav-link <?= $page=='gestao_fichas'?'active':'' ?>" href="?page=gestao_fichas"><i class="fa fa-id-card me-2"></i> Validar Fichas</a>
        <?php endif; ?>

        <?php if($perfil == 'funcionario' || $perfil == 'gestor'): ?>
            <a class="nav-link <?= $page=='validar_pedidos'?'active':'' ?>" href="?page=validar_pedidos"><i class="fa fa-check-double me-2"></i> Validar Matriculas</a>
            <a class="nav-link <?= $page=='pautas'?'active':'' ?>" href="?page=pautas"><i class="fa fa-table me-2"></i> Pautas de Avaliacao</a>
        <?php endif; ?>

        <hr class="bg-secondary">
        <a class="nav-link text-danger" href="?logout=1"><i class="fa fa-sign-out-alt me-2"></i> Sair</a>
    </ul>
</div>

<main class="main">
    <header class="d-flex justify-content-between mb-4">
        <h4>Ola, <?= $_SESSION['nome'] ?></h4>
        <div class="text-muted"><?= date('d/m/Y') ?></div>
    </header>

    <?php if($page == 'home'): ?>
        <div class="card p-5 text-center"><h3>Bem-vindo ao Sistema de Gestao IPCA</h3></div>

    <?php elseif($page == 'minha_ficha' && $perfil == 'aluno'): 
        $stmt = $pdo->prepare("SELECT * FROM fichas_aluno WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $ficha = $stmt->fetch();
        $isSubmetida = ($ficha && $ficha['estado'] != 'Rascunho');
    ?>
        <div class="card p-4">
            <h4>Ficha de Aluno</h4>
            <?php if(isset($_GET['msg']) && $_GET['msg']=='sucesso') echo "<div class='alert alert-success'>Operacao realizada com sucesso!</div>"; ?>
            <?php if($ficha): ?>
                <div class="alert alert-info">Estado atual da ficha: <strong><?= $ficha['estado'] ?></strong>
                    <?php if($ficha['observacoes']) echo "<br><small>Obs do Gestor: " . $ficha['observacoes'] . "</small>"; ?>
                </div>
            <?php endif; ?>

            <form action="actions/submeter_ficha.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nome do Aluno</label>
                        <input type="text" name="nome_aluno" class="form-control" value="<?= $ficha['nome_aluno'] ?? $_SESSION['nome'] ?>" required <?= $isSubmetida?'readonly':'' ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Curso Pretendido</label>
                        <select name="curso_id" class="form-select" required <?= $isSubmetida?'disabled':'' ?>>
                            <?php 
                            $cursos = $pdo->query("SELECT * FROM cursos")->fetchAll();
                            foreach($cursos as $c) {
                                $sel = ($ficha && $ficha['curso_id']==$c['id']) ? 'selected' : '';
                                echo "<option value='{$c['id']}' $sel>{$c['nome']}</option>";
                            }
                            ?>
                        </select>
                        <?php if($isSubmetida && $ficha['curso_id']) echo "<input type='hidden' name='curso_id' value='{$ficha['curso_id']}'>"; ?>
                    </div>
                    <div class="col-md-4 mb-3"><label>Turma</label><input type="text" name="turma" class="form-control" value="<?= $ficha['turma'] ?? '' ?>" required <?= $isSubmetida?'readonly':'' ?>></div>
                    <div class="col-md-4 mb-3"><label>Data de Nascimento</label><input type="date" name="data_nascimento" class="form-control" value="<?= $ficha['data_nascimento'] ?? '' ?>" required <?= $isSubmetida?'readonly':'' ?>></div>
                    <div class="col-md-4 mb-3"><label>BI / CC</label><input type="text" name="bi" class="form-control" value="<?= $ficha['bi'] ?? '' ?>" required <?= $isSubmetida?'readonly':'' ?>></div>
                    <div class="col-md-12 mb-3">
                        <label>Fotografia (JPG/PNG)</label>
                        <input type="file" name="foto" accept=".jpg,.png,.jpeg" class="form-control" <?= $isSubmetida?'disabled':'' ?>>
                        <?php if($ficha && $ficha['foto']) echo "<small>Foto atual: <a href='uploads/{$ficha['foto']}' target='_blank'>Ver Foto</a></small>"; ?>
                    </div>
                </div>
                
                <?php if(!$isSubmetida): ?>
                    <button type="submit" name="acao" value="Rascunho" class="btn btn-secondary">Guardar como Rascunho</button>
                    <button type="submit" name="acao" value="Submetida" class="btn btn-primary">Submeter para Validacao</button>
                <?php endif; ?>
            </form>
        </div>

    <?php elseif($page == 'gestao_fichas' && $perfil == 'gestor'): ?>
        <h4>Validar Fichas de Alunos</h4>
        <?php 
        $stmt = $pdo->query("SELECT f.*, c.nome as curso FROM fichas_aluno f LEFT JOIN cursos c ON f.curso_id = c.id WHERE f.estado = 'Submetida'");
        $fichas = $stmt->fetchAll();
        foreach($fichas as $f): ?>
            <div class="card p-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-md-1">
                        <?php if($f['foto']): ?>
                            <img src="uploads/<?= $f['foto'] ?>" class="img-fluid rounded shadow-sm">
                        <?php else: ?>
                            <div class="bg-light text-center py-2 rounded"><i class="fa fa-user fa-lg text-secondary"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-5">
                        <strong><?= htmlspecialchars($f['nome_aluno']) ?></strong> (<?= htmlspecialchars($f['curso']) ?>)<br>
                        <small>Turma: <?= htmlspecialchars($f['turma']) ?> | BI: <?= htmlspecialchars($f['bi']) ?></small>
                    </div>
                    <div class="col-md-6">
                        <form action="actions/validar_ficha.php" method="POST" class="d-flex gap-2">
                            <input type="hidden" name="ficha_id" value="<?= $f['id'] ?>">
                            <input type="text" name="observacoes" class="form-control form-control-sm" placeholder="Observacoes..." required>
                            <button name="decisao" value="Aprovada" class="btn btn-sm btn-success">Aprovar</button>
                            <button name="decisao" value="Rejeitada" class="btn btn-sm btn-danger">Rejeitar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php elseif($page == 'cursos' && $perfil == 'gestor'): ?>
        <div class="card p-4 mb-4">
            <h5>Adicionar Novo Curso</h5>
            <form action="actions/gerir_pedagogico.php" method="POST" class="d-flex gap-2">
                <input type="hidden" name="acao" value="add_curso">
                <input type="text" name="nome_curso" class="form-control" placeholder="Nome do Curso..." required>
                <button class="btn btn-success">Adicionar</button>
            </form>
        </div>
        <div class="card p-4">
            <h5>Cursos Registados</h5>
            <table class="table">
                <?php $cursos = $pdo->query("SELECT * FROM cursos")->fetchAll();
                foreach($cursos as $c): ?>
                    <tr>
                        <td><?= $c['nome'] ?></td>
                        <td class="text-end"><a href="?page=plano&curso_id=<?= $c['id'] ?>" class="btn btn-sm btn-info text-white">Ver Plano Estudos</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    <?php elseif($page == 'ucs' && $perfil == 'gestor'): ?>
        <div class="card p-4 mb-4">
            <h5>Adicionar Nova Disciplina (UC)</h5>
            <form action="actions/gerir_pedagogico.php" method="POST" class="d-flex gap-2">
                <input type="hidden" name="acao" value="add_uc">
                <input type="text" name="nome_uc" class="form-control" placeholder="Nome da UC..." required>
                <button class="btn btn-success">Adicionar</button>
            </form>
        </div>
        <div class="card p-4">
            <table class="table">
                <?php $ucs = $pdo->query("SELECT * FROM unidades_curriculares")->fetchAll();
                foreach($ucs as $uc) echo "<tr><td>{$uc['nome']}</td></tr>"; ?>
            </table>
        </div>

    <?php elseif($page == 'plano' && $perfil == 'gestor' && isset($_GET['curso_id'])): 
        $curso_id = $_GET['curso_id'];
        $c_name = $pdo->query("SELECT nome FROM cursos WHERE id = $curso_id")->fetchColumn();
    ?>
        <h4 class="mb-4">Plano de Estudos: <?= $c_name ?></h4>
        <div class="card p-4 mb-4">
            <form action="actions/gerir_pedagogico.php" method="POST" class="row g-2">
                <input type="hidden" name="acao" value="add_plano">
                <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                <div class="col-md-6">
                    <select name="uc_id" class="form-select" required>
                        <?php 
                        $ucs = $pdo->query("SELECT * FROM unidades_curriculares")->fetchAll();
                        foreach($ucs as $uc) echo "<option value='{$uc['id']}'>{$uc['nome']}</option>"; 
                        ?>
                    </select>
                </div>
                <div class="col-md-2"><input type="number" name="ano" class="form-control" placeholder="Ano (ex: 1)" required></div>
                <div class="col-md-2"><input type="number" name="semestre" class="form-control" placeholder="Sem. (ex: 2)" required></div>
                <div class="col-md-2"><button class="btn btn-success w-100">Adicionar UC</button></div>
            </form>
        </div>
        <div class="card p-4">
            <table class="table">
                <thead><tr><th>Ano</th><th>Semestre</th><th>Unidade Curricular</th></tr></thead>
                <?php 
                $plano = $pdo->query("SELECT p.ano, p.semestre, u.nome FROM plano_estudos p JOIN unidades_curriculares u ON p.uc_id = u.id WHERE p.curso_id = $curso_id ORDER BY p.ano, p.semestre")->fetchAll();
                foreach($plano as $p) echo "<tr><td>{$p['ano']}º</td><td>{$p['semestre']}º</td><td>{$p['nome']}</td></tr>"; ?>
            </table>
        </div>

    <?php elseif($page == 'matricula_status' && $perfil == 'aluno'): ?>
        <h4>Pedido de Matricula</h4>
        <div class="card p-4 mb-4">
            <form action="actions/gerir_matriculas.php" method="POST" class="d-flex gap-2">
                <select name="curso_id" class="form-select" required>
                    <option value="">Selecione um curso para se matricular...</option>
                    <?php 
                    $cursos = $pdo->query("SELECT * FROM cursos")->fetchAll();
                    foreach($cursos as $c) echo "<option value='{$c['id']}'>{$c['nome']}</option>"; 
                    ?>
                </select>
                <button type="submit" name="pedir_matricula" class="btn btn-primary">Pedir Matricula</button>
            </form>
        </div>
        
        <div class="card p-4">
            <h5>O Meu Historico de Pedidos</h5>
            <table class="table">
                <thead><tr><th>Curso</th><th>Estado</th><th>Observacoes</th></tr></thead>
                <?php
                $stm = $pdo->prepare("SELECT m.estado, m.observacoes, c.nome FROM matriculas m JOIN cursos c ON m.curso_id = c.id WHERE m.aluno_id = ?");
                $stm->execute([$user_id]);
                foreach($stm->fetchAll() as $m) echo "<tr><td>{$m['nome']}</td><td><strong>{$m['estado']}</strong></td><td>{$m['observacoes']}</td></tr>";
                ?>
            </table>
        </div>

    <?php elseif($page == 'validar_pedidos' && in_array($perfil, ['funcionario','gestor'])): ?>
        <h4>Validar Pedidos de Matricula</h4>
        <div class="card p-3 shadow-sm">
            <table class="table table-hover">
                <thead><tr><th>Aluno</th><th>Curso</th><th>Decisao</th></tr></thead>
                <tbody>
                    <?php 
                    $q = $pdo->query("SELECT m.id, u.nome as aluno, c.nome as curso FROM matriculas m JOIN utilizadores u ON m.aluno_id = u.id JOIN cursos c ON m.curso_id = c.id WHERE m.estado = 'Pendente'")->fetchAll();
                    foreach($q as $row): ?>
                        <tr>
                            <td><?= $row['aluno'] ?></td><td><?= $row['curso'] ?></td>
                            <td>
                                <form action="actions/gerir_matriculas.php" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="matricula_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="observacoes" class="form-control form-control-sm" placeholder="Opcional..." style="width:150px">
                                    <button name="decisao" value="Aprovado" class="btn btn-sm btn-success">Aprovar</button>
                                    <button name="decisao" value="Rejeitado" class="btn btn-sm btn-danger">Rejeitar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif($page == 'pautas'): ?>
        <h4>Pautas de Avaliacao</h4>
        <div class="card p-4 shadow-sm mb-4">
            <h5>Criar Nova Pauta</h5>
            <form action="actions/gerir_pautas.php" method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Disciplina (UC)</label>
                        <select name="uc_id" class="form-select" required>
                            <?php $ucs = $pdo->query("SELECT * FROM unidades_curriculares")->fetchAll();
                            foreach($ucs as $u) echo "<option value='{$u['id']}'>{$u['nome']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label>Ano Letivo</label><input type="text" name="ano_letivo" placeholder="ex: 2023/24" class="form-control" required></div>
                    <div class="col-md-3">
                        <label>Epoca</label>
                        <select name="epoca" class="form-select" required><option>Normal</option><option>Recurso</option><option>Especial</option></select>
                    </div>
                    <div class="col-md-2"><label>&nbsp;</label><button name="criar_pauta" class="btn btn-primary d-block w-100">Criar Pauta</button></div>
                </div>
            </form>
        </div>
        
        <div class="card p-4">
            <h5>Pautas Existentes</h5>
            <table class="table">
                <thead><tr><th>UC</th><th>Ano L.</th><th>Epoca</th><th>Acao</th></tr></thead>
                <?php
                $pautas = $pdo->query("SELECT p.id, p.ano_letivo, p.epoca, u.nome FROM pautas p JOIN unidades_curriculares u ON p.uc_id = u.id ORDER BY p.criado_em DESC")->fetchAll();
                foreach($pautas as $p) echo "<tr><td>{$p['nome']}</td><td>{$p['ano_letivo']}</td><td>{$p['epoca']}</td><td><a href='?page=lancar_notas&id={$p['id']}' class='btn btn-sm btn-info text-white'>Ver/Lancar Notas</a></td></tr>";
                ?>
            </table>
        </div>

    <?php elseif($page == 'lancar_notas' && isset($_GET['id'])): 
        $pauta_id = $_GET['id'];
        $info = $pdo->query("SELECT p.*, u.nome FROM pautas p JOIN unidades_curriculares u ON p.uc_id = u.id WHERE p.id = $pauta_id")->fetch();
    ?>
        <h4>Lancar Notas da Pauta</h4>
        <div class="alert alert-secondary"><strong>UC:</strong> <?= $info['nome'] ?> | <strong>Ano:</strong> <?= $info['ano_letivo'] ?> | <strong>Epoca:</strong> <?= $info['epoca'] ?></div>
        
        <div class="card p-4 shadow-sm">
            <form action="actions/gerir_pautas.php" method="POST">
                <input type="hidden" name="pauta_id" value="<?= $pauta_id ?>">
                <table class="table">
                    <thead><tr><th>Aluno</th><th>Nota Final</th></tr></thead>
                    <?php 
                    $notas = $pdo->query("SELECT n.aluno_id, n.nota_final, u.nome FROM notas n JOIN utilizadores u ON n.aluno_id = u.id WHERE n.pauta_id = $pauta_id")->fetchAll();
                    foreach($notas as $n): ?>
                        <tr>
                            <td class="align-middle"><?= $n['nome'] ?></td>
                            <td><input type="number" step="0.1" max="20" min="0" name="notas[<?= $n['aluno_id'] ?>]" class="form-control form-control-sm w-25" value="<?= $n['nota_final'] ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit" name="lancar_notas" class="btn btn-success mt-3">Gravar Notas</button>
            </form>
        </div>

    <?php endif; ?>
</main>
</body>
</html>