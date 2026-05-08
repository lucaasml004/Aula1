const express = require('express');
const router = express.Router();
const auth = require('../middleware/authMiddleware');
const Course = require('../models/Course');
const UC = require('../models/UC');
const Form = require('../models/Form');
const StudyPlan = require('../models/StudyPlan');

// Middleware para garantir que apenas gestores acedam a estas rotas
router.use(auth(['gestor']));

// Adicionar Curso
router.post('/add-curso', async (req, res) => {
    try {
        const { nome_curso } = req.body;
        const curso = new Course({ nome: nome_curso });
        await curso.save();
        res.redirect('/dashboard?page=cursos');
    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao criar curso');
    }
});

// Adicionar UC
router.post('/add-uc', async (req, res) => {
    try {
        const { nome_uc } = req.body;
        const uc = new UC({ nome: nome_uc });
        await uc.save();
        res.redirect('/dashboard?page=ucs');
    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao criar UC');
    }
});

// Adicionar ao Plano de Estudos
router.post('/add-plano', async (req, res) => {
    try {
        const { curso_id, uc_id, ano, semestre } = req.body;
        const plano = new StudyPlan({
            course: curso_id,
            uc: uc_id,
            ano,
            semestre
        });
        await plano.save();
        res.redirect(`/dashboard?page=plano&curso_id=${curso_id}&msg=sucesso`);
    } catch (err) {
        if (err.code === 11000) {
            return res.redirect(`/dashboard?page=plano&curso_id=${req.body.curso_id}&msg=erro_duplicado`);
        }
        res.status(500).send('Erro ao configurar plano');
    }
});

// Validar Ficha de Aluno
router.post('/validar-ficha', async (req, res) => {
    try {
        const { ficha_id, decisao, observacoes } = req.body;
        await Form.findByIdAndUpdate(ficha_id, {
            estado: decisao,
            observacoes,
            validado_por: req.session.userId,
            data_validacao: new Date()
        });
        res.redirect('/dashboard?page=gestao_fichas');
    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao validar ficha');
    }
});

module.exports = router;
