<?php
// === PROTEÇÃO INICIAL E ROTAS ===
// Verifica se o user tem permissão (Sessão Ligada). Se não tiver, é atirado para a página de Login.
require_once 'config.php';
session_start();

if (!isset($_SESSION['perfil'])) { header("Location: index.php"); exit; }

$perfil = $_SESSION['perfil'];
$user_id = $_SESSION['user_id'];
$page = $_GET['page'] ?? 'home';

// Permite destruir a sessão quando carregam no botão "Sair"
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>IPCA | Portal Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="sidebar d-flex flex-column shadow">
    <div class="d-flex align-items-center justify-content-center mb-4 pb-3 border-bottom border-secondary">
        <i class="fa-solid fa-graduation-cap fa-2x text-primary me-2"></i>
        <h4 class="mb-0 fw-bold text-white">IPCA Portal</h4>
    </div>
    
    <div class="text-center mb-4 pb-3 border-bottom border-secondary">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nome']) ?>&background=random&color=fff&rounded=true" class="rounded-circle mb-3 shadow-md" width="70" height="70">
        <div class="fw-bold mb-1 text-white"><?= htmlspecialchars($_SESSION['nome']) ?></div>
        <span class="badge bg-primary text-uppercase"><?= $perfil ?></span>
    </div>
    
    <ul class="nav flex-column mb-auto">
        <a class="nav-link <?= $page=='home'?'active':'' ?>" href="?page=home"><i class="fa-solid fa-house mb-1 me-2 text-center"></i> Início</a>
        
        <?php 
        // Apenas ALUNOS conseguem ver o botão de tratar da sua "Ficha e Matrícula"
        if($perfil == 'aluno'): 
        ?>
            <a class="nav-link <?= $page=='minha_ficha'?'active':'' ?>" href="?page=minha_ficha"><i class="fa-solid fa-user-graduate mb-1 me-2 text-center"></i> Ficha e Matrícula</a>
        <?php endif; ?>

        <?php 
        // Apenas GESTORES conseguem criar Cursos, Unidades Curriculares e Validar Documentos Documentais
        if($perfil == 'gestor'): 
        ?>
            <a class="nav-link <?= $page=='cursos'?'active':'' ?>" href="?page=cursos"><i class="fa-solid fa-book-open mb-1 me-2 text-center"></i> Cursos</a>
            <a class="nav-link <?= $page=='ucs'?'active':'' ?>" href="?page=ucs"><i class="fa-solid fa-layer-group mb-1 me-2 text-center"></i> UCs</a>
            <a class="nav-link <?= $page=='gestao_fichas'?'active':'' ?>" href="?page=gestao_fichas"><i class="fa-solid fa-id-card-clip mb-1 me-2 text-center"></i> Validar Fichas</a>
        <?php endif; ?>

        <?php 
        // FUNCIONARIOS (Secretaria) e GESTORES lidam com as turmas e notas
        if($perfil == 'funcionario' || $perfil == 'gestor'): 
        ?>
            <a class="nav-link <?= $page=='validar_pedidos'?'active':'' ?>" href="?page=validar_pedidos"><i class="fa-solid fa-clipboard-check mb-1 me-2 text-center"></i> Validar Matrículas</a>
            <a class="nav-link <?= $page=='pautas'||$page=='lancar_notas'?'active':'' ?>" href="?page=pautas"><i class="fa-solid fa-table-list mb-1 me-2 text-center"></i> Pautas & Notas</a>
        <?php endif; ?>
    </ul>

    <div class="mt-4 pt-3 border-top border-secondary">
        <button id="btn-theme-toggle" class="btn btn-outline-secondary w-100 d-flex justify-content-center align-items-center gap-2 mb-3">
            <i class="fa-solid fa-moon"></i> <span>Tema</span>
        </button>
        <a class="nav-link text-danger w-100 d-flex justify-content-center align-items-center bg-transparent border border-danger p-2" href="?logout=1" style="border-radius:10px;">
            <i class="fa-solid fa-arrow-right-from-bracket m-0 me-2" style="width:auto"></i> Terminar Sessão
        </a>
    </div>
</div>

<main class="main">
    <header class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
        <h3 class="mb-0 fw-bold">Gestão Académica</h3>
        <div class="text-muted d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 shadow-sm" style="background-color: var(--card-bg) !important;">
            <i class="fa-regular fa-calendar text-primary"></i>
            <span class="fw-medium text-main"><?= date('d / m / Y') ?></span>
        </div>
    </header>

    <?php 
    // --- ESTADO 1: O ECRÃ INICIAL DE BOAS VINDAS
    if($page == 'home'): 
    ?>
        <div class="card p-5 text-center shadow-lg border-0" style="background: linear-gradient(135deg, var(--card-bg) 0%, rgba(59, 130, 246, 0.05) 100%);">
            <i class="fa-solid fa-school fa-4x text-primary mb-4"></i>
            <h2 class="mb-3">Bem-vindo ao Sistema do IPCA</h2>
            <p class="text-muted fs-5">Aceda aos menus laterais para gerir as suas funcionalidades.</p>
        </div>

    <?php 
    // --- ESTADO 2: O ALUNO EDITA A SUA FICHA 
    // Carrega os dados se o aluno já a gravou em "Rascunho" anteriormente, para pré-preencher
    elseif($page == 'minha_ficha' && $perfil == 'aluno'): 
        $stmt = $pdo->prepare("SELECT * FROM fichas_aluno WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $ficha = $stmt->fetch();
        $isSubmetida = ($ficha && $ficha['estado'] != 'Rascunho'); // Bloqueia edições caso já tenha entregue o documento oficial
    ?>
        <div class="card p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">A Minha Ficha de Aluno</h4>
                <?php if($ficha): ?>
                    <span class="badge <?= $ficha['estado']=='Validada'?'bg-success':($ficha['estado']=='Submetida'?'bg-warning text-dark':'bg-secondary') ?> px-3 py-2 fs-6">
                        <?= $ficha['estado'] ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <?php if(isset($_GET['msg']) && $_GET['msg']=='sucesso') echo "<div class='alert alert-success'><i class='fa-solid fa-check-circle me-2'></i>Operação realizada com sucesso!</div>"; ?>
            
            <?php if($ficha && $ficha['observacoes']): ?>
                <div class="alert alert-info border-start border-4 border-info shadow-sm">
                    <strong><i class="fa-solid fa-comment-dots me-2"></i>Observações do Gestor:</strong>
                    <p class="mb-0 mt-1 ms-4"><?= htmlspecialchars($ficha['observacoes']) ?></p>
                </div>
            <?php endif; ?>

            <form action="actions/submeter_ficha.php" method="POST" enctype="multipart/form-data">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" name="nome_aluno" class="form-control" value="<?= $ficha['nome_aluno'] ?? htmlspecialchars($_SESSION['nome']) ?>" required <?= $isSubmetida?'readonly':'' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Curso Pretendido</label>
                        <select name="curso_id" class="form-select" required <?= $isSubmetida?'disabled':'' ?>>
                            <option value="">Selecione o curso...</option>
                            <?php 
                            $cursos = $pdo->query("SELECT * FROM cursos")->fetchAll();
                            foreach($cursos as $c) {
                                $sel = ($ficha && $ficha['curso_id']==$c['id']) ? 'selected' : '';
                                echo "<option value='{$c['id']}' $sel>{$c['nome']}</option>";
                            }
                            ?>
                        </select>
                        <?php if($isSubmetida && isset($ficha['curso_id'])) echo "<input type='hidden' name='curso_id' value='{$ficha['curso_id']}'>"; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Turma</label>
                        <input type="text" name="turma" class="form-control" value="<?= htmlspecialchars($ficha['turma'] ?? '') ?>" required <?= $isSubmetida?'readonly':'' ?> placeholder="ex: T2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" class="form-control" value="<?= $ficha['data_nascimento'] ?? '' ?>" required <?= $isSubmetida?'readonly':'' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nº de BI / CC</label>
                        <input type="text" name="bi" class="form-control" value="<?= htmlspecialchars($ficha['bi'] ?? '') ?>" required <?= $isSubmetida?'readonly':'' ?> placeholder="XXXXXXXX">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label d-block">Fotografia (JPG/PNG)</label>
                        <?php if($ficha && $ficha['foto']): ?>
                            <div class="d-flex align-items-center gap-3 mb-3 bg-light p-3 rounded" style="background-color: var(--table-hover) !important;">
                                <img src="uploads/<?= $ficha['foto'] ?>" class="rounded shadow-sm object-fit-cover" width="80" height="80">
                                <div><a href="uploads/<?= $ficha['foto'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-image me-1"></i> Ver Foto Atual</a></div>
                            </div>
                        <?php endif; ?>
                        <?php if(!$isSubmetida): ?>
                            <input type="file" name="foto" accept=".jpg,.png,.jpeg" class="form-control">
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if(!$isSubmetida): ?>
                    <div class="d-flex gap-3">
                        <button type="submit" name="acao" value="Rascunho" class="btn btn-secondary px-4"><i class="fa-solid fa-floppy-disk me-2"></i> Guardar Rascunho</button>
                        <button type="submit" name="acao" value="Submetida" class="btn btn-primary px-4"><i class="fa-solid fa-paper-plane me-2"></i> Submeter Ficha e Efetuar Matrícula</button>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="card p-0 mt-4 shadow-sm overflow-hidden">
            <div class="card-header bg-transparent mb-0"><h5 class="mb-0 m-2">O Meu Histórico de Matrículas</h5></div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead><tr><th class="ps-4">Curso</th><th>Estado do Pedido</th><th>Observações / Justificação</th></tr></thead>
                    <tbody>
                        <?php
                        $stm = $pdo->prepare("SELECT m.estado, m.observacoes, c.nome FROM matriculas m JOIN cursos c ON m.curso_id = c.id WHERE m.aluno_id = ? ORDER BY m.id DESC");
                        $stm->execute([$user_id]);
                        $mats = $stm->fetchAll();
                        if(count($mats) == 0) echo "<tr><td colspan='3' class='text-center py-4 text-muted'>Ainda não efetuou nenhum pedido de matrícula.</td></tr>";
                        foreach($mats as $m) {
                            $badge = 'bg-secondary';
                            if($m['estado'] == 'Aprovado') $badge = 'bg-success';
                            if($m['estado'] == 'Rejeitado') $badge = 'bg-danger';
                            if($m['estado'] == 'Pendente') $badge = 'bg-warning text-dark';
                            
                            echo "<tr>
                                    <td class='ps-4 fw-medium text-primary'>{$m['nome']}</td>
                                    <td><span class='badge {$badge} px-3 py-2'>{$m['estado']}</span></td>
                                    <td class='text-muted small'>".($m['observacoes'] ? htmlspecialchars($m['observacoes']) : '-')."</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php 
    // --- ESTADO 3: O GESTOR DESPACHA FICHAS
    // Exibe apenas as fichas dos estudantes cujo estado é 'Submetida' para aprovação
    elseif($page == 'gestao_fichas' && $perfil == 'gestor'): 
    ?>
        <h4 class="mb-4">Validar Fichas de Alunos</h4>
        <div class="row">
        <?php 
        $stmt = $pdo->query("SELECT f.*, c.nome as curso FROM fichas_aluno f LEFT JOIN cursos c ON f.curso_id = c.id WHERE f.estado = 'Submetida'");
        $fichas = $stmt->fetchAll();
        if(count($fichas) == 0): ?>
            <div class="col-12"><div class="alert alert-success"><i class="fa-solid fa-check-double me-2"></i>Não existem fichas pendentes.</div></div>
        <?php else:
        foreach($fichas as $f): ?>
            <div class="col-md-6 col-lg-12 mb-4">
                <div class="card p-4 shadow-sm h-100">
                    <div class="row align-items-center h-100">
                        <div class="col-auto">
                            <?php if($f['foto']): ?>
                                <img src="uploads/<?= htmlspecialchars($f['foto']) ?>" class="rounded-circle shadow-sm object-fit-cover border border-3 border-light" width="80" height="80">
                            <?php else: ?>
                                <div class="bg-secondary bg-opacity-10 text-center rounded-circle d-flex justify-content-center align-items-center" style="width:80px; height:80px;">
                                    <i class="fa-solid fa-user fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <h5 class="mb-1 fw-bold"><?= htmlspecialchars($f['nome_aluno']) ?></h5>
                            <div class="text-primary fw-medium mb-1"><i class="fa-solid fa-graduation-cap me-1"></i> <?= htmlspecialchars($f['curso']) ?></div>
                            <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i> Turma: <?= htmlspecialchars($f['turma']) ?> | <i class="fa-solid fa-id-card me-1"></i> BI: <?= htmlspecialchars($f['bi']) ?></small>
                        </div>
                        <div class="col-md-6 ms-auto">
                            <form action="actions/validar_ficha.php" method="POST" class="d-flex flex-column gap-2 bg-light p-3 rounded" style="background-color: var(--table-hover) !important;">
                                <input type="hidden" name="ficha_id" value="<?= $f['id'] ?>">
                                <label class="small fw-bold">Decisão do Gestor:</label>
                                <input type="text" name="observacoes" class="form-control" placeholder="Adicione observações..." required>
                                <div class="d-flex gap-2 mt-2">
                                    <button name="decisao" value="Aprovada" class="btn btn-success flex-grow-1"><i class="fa-solid fa-check me-2"></i> Aprovar</button>
                                    <button name="decisao" value="Rejeitada" class="btn btn-danger flex-grow-1"><i class="fa-solid fa-xmark me-2"></i> Rejeitar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
        </div>

    <?php 
    // --- ESTADO 4: CRIAR CURSOS NOVOS E VER TABELAS DE CURSOS EXISTENTES
    elseif($page == 'cursos' && $perfil == 'gestor'): 
    ?>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card p-4 h-100 shadow-sm border-top border-4 border-primary">
                    <h5 class="mb-4">Adicionar Novo Curso</h5>
                    <form action="actions/gerir_pedagogico.php" method="POST" class="d-flex flex-column gap-3">
                        <input type="hidden" name="acao" value="add_curso">
                        <div>
                            <label class="form-label">Designação do Curso</label>
                            <input type="text" name="nome_curso" class="form-control" placeholder="Ex: Engenharia Informática" required>
                        </div>
                        <button class="btn btn-success mt-2"><i class="fa-solid fa-plus me-2"></i> Guardar Curso</button>
                    </form>
                </div>
            </div>
            <div class="col-md-8 mb-4">
                <div class="card p-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-header bg-transparent mb-0"><h5 class="mb-0 m-2">Cursos Registados</h5></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php $cursos = $pdo->query("SELECT * FROM cursos")->fetchAll();
                                foreach($cursos as $c): ?>
                                    <tr>
                                        <td class="ps-4 py-3 fw-medium"><i class="fa-solid fa-book text-muted me-3"></i> <?= htmlspecialchars($c['nome']) ?></td>
                                        <td class="text-end pe-4 py-3"><a href="?page=plano&curso_id=<?= $c['id'] ?>" class="btn btn-sm btn-primary px-3 rounded-pill"><i class="fa-solid fa-list-check me-1"></i> Plano de Estudos</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php 
    // --- ESTADO 5: CRIAR DISCIPLINAS (Unidades Curriculares)
    elseif($page == 'ucs' && $perfil == 'gestor'): 
    ?>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card p-4 h-100 shadow-sm border-top border-4 border-info">
                    <h5 class="mb-4">Adicionar Nova Disciplina (UC)</h5>
                    <form action="actions/gerir_pedagogico.php" method="POST" class="d-flex flex-column gap-3">
                        <input type="hidden" name="acao" value="add_uc">
                        <div>
                            <label class="form-label">Nome da UC</label>
                            <input type="text" name="nome_uc" class="form-control" placeholder="Ex: Matemática Aplicada" required>
                        </div>
                        <button class="btn btn-info text-white mt-2"><i class="fa-solid fa-plus me-2"></i> Guardar UC</button>
                    </form>
                </div>
            </div>
            <div class="col-md-8 mb-4">
                <div class="card p-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-header bg-transparent mb-0"><h5 class="mb-0 m-2">Unidades Curriculares</h5></div>
                    <div class="table-responsive" style="max-height: 500px; overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <?php $ucs = $pdo->query("SELECT * FROM unidades_curriculares")->fetchAll();
                                foreach($ucs as $uc) echo "<tr><td class='ps-4 py-3'><i class='fa-solid fa-layer-group text-muted me-3'></i> " . htmlspecialchars($uc['nome']) . "</td></tr>"; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php 
    // --- ESTADO 6: PLANO DE ESTUDOS ASSOCIADO A CADA CURSO
    elseif($page == 'plano' && $perfil == 'gestor' && isset($_GET['curso_id'])): 
        $curso_id = $_GET['curso_id'];
        $c_name = $pdo->query("SELECT nome FROM cursos WHERE id = " . (int)$curso_id)->fetchColumn();
    ?>
        <div class="d-flex align-items-center mb-4 gap-3">
            <a href="?page=cursos" class="btn btn-outline-secondary rounded-circle" style="width:40px;height:40px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-arrow-left"></i></a>
            <h4 class="mb-0">Plano de Estudos: <span class="text-primary"><?= htmlspecialchars($c_name) ?></span></h4>
        </div>
        
        <div class="card p-4 mb-4 shadow-sm bg-light" style="background-color: var(--table-hover) !important;">
            <h5 class="mb-3">Inserir Unidade Curricular no Plano</h5>
            <form action="actions/gerir_pedagogico.php" method="POST" class="row g-3 align-items-end">
                <input type="hidden" name="acao" value="add_plano">
                <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                <div class="col-md-5">
                    <label class="form-label">Unidade Curricular</label>
                    <select name="uc_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php 
                        $ucs = $pdo->query("SELECT * FROM unidades_curriculares")->fetchAll();
                        foreach($ucs as $uc) echo "<option value='{$uc['id']}'>{$uc['nome']}</option>"; 
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ano</label>
                    <input type="number" name="ano" class="form-control" placeholder="Ex: 1" min="1" max="5" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Semestre</label>
                    <input type="number" name="semestre" class="form-control" placeholder="1 ou 2" min="1" max="2" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-success w-100 py-2"><i class="fa-solid fa-plus me-2"></i> Adicionar ao Plano</button>
                </div>
            </form>
        </div>
        
        <div class="card p-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th class="ps-4">Ano</th><th>Semestre</th><th>Unidade Curricular</th></tr></thead>
                    <tbody>
                        <?php 
                        $plano = $pdo->query("SELECT p.ano, p.semestre, u.nome FROM plano_estudos p JOIN unidades_curriculares u ON p.uc_id = u.id WHERE p.curso_id = $curso_id ORDER BY p.ano, p.semestre")->fetchAll();
                        if(count($plano) == 0) echo "<tr><td colspan='3' class='text-center py-4 text-muted'>Nenhuma UC adicionada ao plano de estudos.</td></tr>";
                        foreach($plano as $p) echo "<tr><td class='ps-4 fw-bold'>{$p['ano']}º Ano</td><td>{$p['semestre']}º Semestre</td><td><i class='fa-solid fa-book-open-reader text-muted me-2'></i> {$p['nome']}</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php 
    // --- ESTADO 8: SECRETARIADO VÊ MATRÍCULAS E DESPACHA
    elseif($page == 'validar_pedidos' && in_array($perfil, ['funcionario','gestor'])): 
    ?>
        <h4 class="mb-4">Validar Pedidos de Matrícula</h4>
        <div class="card p-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th class="ps-4">Aluno / Requerente</th><th>Curso Pretendido</th><th>Ação / Decisão do Funcionário</th></tr></thead>
                    <tbody>
                        <?php 
                        $q = $pdo->query("SELECT m.id, u.nome as aluno, u.email as mail, c.nome as curso FROM matriculas m JOIN utilizadores u ON m.aluno_id = u.id JOIN cursos c ON m.curso_id = c.id WHERE m.estado = 'Pendente'")->fetchAll();
                        if(count($q) == 0): ?>
                            <tr><td colspan='3' class='text-center py-5 text-muted'><i class="fa-solid fa-inbox fa-3x mb-3 text-light"></i><br>Não há pedidos pendentes.</td></tr>
                        <?php else:
                        foreach($q as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= htmlspecialchars($row['aluno']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($row['mail']) ?></div>
                                </td>
                                <td class="fw-medium text-primary"><i class="fa-solid fa-graduation-cap me-2"></i> <?= htmlspecialchars($row['curso']) ?></td>
                                <td>
                                    <form action="actions/gerir_matriculas.php" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="matricula_id" value="<?= $row['id'] ?>">
                                        <input type="text" name="observacoes" class="form-control form-control-sm" placeholder="Opcional..." style="max-width:200px">
                                        <button name="decisao" value="Aprovado" class="btn btn-sm btn-success px-3"><i class="fa-solid fa-check me-1"></i> Aprovar</button>
                                        <button name="decisao" value="Rejeitado" class="btn btn-sm btn-danger px-3"><i class="fa-solid fa-xmark me-1"></i> Rejeitar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php 
    // --- ESTADO 9: FUNCIONÁRIO ABRE UMA NOVA PAUTA (Geração em Lote)
    elseif($page == 'pautas'): 
    ?>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card p-4 shadow-sm border-top border-4 border-primary">
                    <h5 class="mb-4"><i class="fa-solid fa-file-invoice me-2 text-primary"></i> Criar Nova Pauta</h5>
                    <form action="actions/gerir_pautas.php" method="POST">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Disciplina (UC)</label>
                                <select name="uc_id" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <?php $ucs = $pdo->query("SELECT * FROM unidades_curriculares")->fetchAll();
                                    foreach($ucs as $u) echo "<option value='{$u['id']}'>{$u['nome']}</option>"; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Ano Letivo</label>
                                <input type="text" name="ano_letivo" placeholder="ex: 2023/24" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Época</label>
                                <select name="epoca" class="form-select" required>
                                    <option>Normal</option>
                                    <option>Recurso</option>
                                    <option>Especial</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button name="criar_pauta" class="btn btn-primary d-block w-100 py-2"><i class="fa-solid fa-plus me-1"></i> Gerar Pauta</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="card p-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-transparent mb-0"><h5 class="mb-0 m-2">Arquivo de Pautas</h5></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th class="ps-4">Unidade Curricular</th><th>Ano Letivo</th><th>Época</th><th class="text-end pe-4">Ação</th></tr></thead>
                            <tbody>
                                <?php
                                $pautas = $pdo->query("SELECT p.id, p.ano_letivo, p.epoca, u.nome FROM pautas p JOIN unidades_curriculares u ON p.uc_id = u.id ORDER BY p.id DESC")->fetchAll();
                                if(count($pautas) == 0): ?>
                                    <tr><td colspan='4' class='text-center py-4 text-muted'>Nenhuma pauta criada.</td></tr>
                                <?php else:
                                foreach($pautas as $p) {
                                    $e_badge = $p['epoca']=='Normal' ? 'bg-info text-dark' : ($p['epoca']=='Recurso' ? 'bg-warning text-dark' : 'bg-danger');
                                    echo "<tr>
                                            <td class='ps-4 fw-bold'>{$p['nome']}</td>
                                            <td>{$p['ano_letivo']}</td>
                                            <td><span class='badge {$e_badge}'>{$p['epoca']}</span></td>
                                            <td class='text-end pe-4'><a href='?page=lancar_notas&id={$p['id']}' class='btn btn-sm btn-outline-primary rounded-pill px-3'><i class='fa-solid fa-pen-to-square me-1'></i> Ver / Lançar Notas</a></td>
                                          </tr>";
                                } endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php 
    // --- ESTADO 10: O LANÇAMENTO DE NOTAS NA PAUTA X (Geração do Array para a Matriz de Pontuação)
    elseif($page == 'lancar_notas' && isset($_GET['id'])): 
        $pauta_id = (int)$_GET['id'];
        $info = $pdo->query("SELECT p.*, u.nome FROM pautas p JOIN unidades_curriculares u ON p.uc_id = u.id WHERE p.id = $pauta_id")->fetch();
    ?>
        <div class="d-flex align-items-center mb-4 gap-3">
            <a href="?page=pautas" class="btn btn-outline-secondary rounded-circle" style="width:40px;height:40px;padding:0;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-arrow-left"></i></a>
            <h4 class="mb-0">Lançamento de Notas</h4>
        </div>
        
        <div class="card bg-primary bg-opacity-10 border-0 p-4 mb-4 shadow-sm" style="background-color: var(--table-hover) !important;">
            <div class="row">
                <div class="col-md-4"><div class="text-muted small mb-1">Unidade Curricular</div><div class="fw-bold fs-5 text-primary"><?= htmlspecialchars($info['nome']) ?></div></div>
                <div class="col-md-4"><div class="text-muted small mb-1">Ano Letivo</div><div class="fw-bold fs-5"><?= htmlspecialchars($info['ano_letivo']) ?></div></div>
                <div class="col-md-4"><div class="text-muted small mb-1">Época Avaliação</div><div class="fw-bold fs-5"><span class="badge bg-secondary"><?= htmlspecialchars($info['epoca']) ?></span></div></div>
            </div>
        </div>
        
        <div class="card p-0 shadow-sm overflow-hidden">
            <form action="actions/gerir_pautas.php" method="POST">
                <input type="hidden" name="pauta_id" value="<?= $pauta_id ?>">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th class="ps-4">Aluno</th><th width="30%">Classificação Final (0-20)</th></tr></thead>
                        <tbody>
                            <?php 
                            $notas = $pdo->query("SELECT n.aluno_id, n.nota_final, u.nome, u.email FROM notas n JOIN utilizadores u ON n.aluno_id = u.id WHERE n.pauta_id = $pauta_id ORDER BY u.nome")->fetchAll();
                            if(count($notas) == 0): ?>
                                <tr><td colspan='2' class='text-center py-4 text-muted'>Nenhum aluno inscrito nesta pauta ou não listado.</td></tr>
                            <?php else:
                            foreach($notas as $n): 
                                $nota = $n['nota_final'];
                                $cor = '';
                                if($nota !== null && $nota !== '') {
                                    $cor = $nota < 9.5 ? 'border-danger text-danger bg-danger bg-opacity-10' : 'border-success text-success bg-success bg-opacity-10';
                                }
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?= htmlspecialchars($n['nome']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($n['email']) ?></div>
                                    </td>
                                    <td>
                                        <div class="input-group" style="max-width: 150px;">
                                            <input type="number" step="0.1" max="20" min="0" name="notas[<?= $n['aluno_id'] ?>]" class="form-control form-control-sm text-center fw-bold fs-5 <?= $cor ?>" value="<?= $nota ?>">
                                            <span class="input-group-text bg-transparent text-muted small">val</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(count($notas) > 0): ?>
                <div class="card-footer bg-transparent p-4 text-end border-top">
                    <button type="submit" name="lancar_notas" class="btn btn-primary px-5 py-2 fw-bold text-uppercase shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i> Gravar Pauta</button>
                </div>
                <?php endif; ?>
            </form>
        </div>

    <?php endif; ?>
</main>

<script src="assets/js/theme.js"></script>
</body>
</html>