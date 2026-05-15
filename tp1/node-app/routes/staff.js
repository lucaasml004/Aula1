const express = require('express');
const router = express.Router();
const auth = require('../middleware/authMiddleware');
const Enrollment = require('../models/Enrollment');
const Pauta = require('../models/Pauta');
const Grade = require('../models/Grade');
const StudyPlan = require('../models/StudyPlan');

// Middleware para funcionários e gestores
router.use(auth(['funcionario', 'gestor']));

// Validar Matrícula
router.post('/validar-matricula', async (req, res) => {
    try {
        const { matricula_id, decisao, observacoes } = req.body;
        console.log('Validando matrícula:', matricula_id, 'Decisão:', decisao);
        await Enrollment.findByIdAndUpdate(matricula_id, {
            estado: decisao,
            observacoes,
            responsavel: req.session.userId,
            data_decisao: new Date()
        });
        console.log('✅ Matrícula validada!');
        res.redirect('/dashboard?page=validar_pedidos');
    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao validar matrícula');
    }
});

// Criar Pauta
router.post('/criar-pauta', async (req, res) => {
    try {
        const { uc_id, ano_letivo, epoca } = req.body;
        
        // 1. Criar a Pauta
        const pauta = new Pauta({ uc: uc_id, ano_letivo, epoca });
        await pauta.save();

        // 2. Procurar alunos matriculados e aprovados nos cursos que têm esta UC no plano
        // Primeiro, encontrar os cursos que têm esta UC no plano de estudos
        const planos = await StudyPlan.find({ uc: uc_id });
        const cursoIds = planos.map(p => p.course);

        // Encontrar alunos com matrículas aprovadas nestes cursos
        const matriculas = await Enrollment.find({ 
            course: { $in: cursoIds }, 
            estado: 'Aprovado' 
        });
        
        const alunoIds = [...new Set(matriculas.map(m => m.aluno.toString()))];

        // 3. Criar entradas de notas vazias para estes alunos
        const gradePromises = alunoIds.map(alunoId => {
            return new Grade({ pauta: pauta._id, aluno: alunoId }).save().catch(e => null); // Ignora duplicados
        });

        await Promise.all(gradePromises);

        res.redirect(`/dashboard?page=lancar_notas&id=${pauta._id}`);
    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao criar pauta');
    }
});

// Lançar Notas
router.post('/lancar-notas', async (req, res) => {
    try {
        const { pauta_id, notas } = req.body; // 'notas' é um objeto { alunoId: nota }
        console.log('Lançando notas para pauta:', pauta_id);
        console.log('Notas recebidas:', notas);
        
        const updatePromises = Object.entries(notas).map(([alunoId, nota]) => {
            console.log(`Atualizando nota do aluno ${alunoId}: ${nota}`);
            return Grade.findOneAndUpdate(
                { pauta: pauta_id, aluno: alunoId },
                { nota_final: nota === '' ? null : nota },
                { upsert: true }
            );
        });

        await Promise.all(updatePromises);
        console.log('✅ Todas as notas foram processadas!');
        res.redirect('/dashboard?page=pautas');
    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao lançar notas');
    }
});

module.exports = router;
