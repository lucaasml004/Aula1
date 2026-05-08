const express = require('express');
const router = express.Router();
const auth = require('../middleware/authMiddleware');
const User = require('../models/User');
const Course = require('../models/Course');
const UC = require('../models/UC');
const Form = require('../models/Form');

// Rota principal do Dashboard
router.get('/', auth(), async (req, res) => {
    const page = req.query.page || 'home';
    const user_id = req.session.userId;
    const perfil = req.session.perfil;
    const nome = req.session.nome;

    let data = { 
        page, 
        perfil, 
        nome, 
        user_id,
        msg: req.query.msg || null
    };

    try {
        // Carregar dados específicos dependendo da página
        if (page === 'minha_ficha' && perfil === 'aluno') {
            data.ficha = await Form.findOne({ user: user_id }).populate('course');
            data.cursos = await Course.find({ estado: 'Ativo' });
            // Aqui poderíamos carregar também o histórico de matrículas
            data.matriculas = []; // Implementar modelo de Matrícula depois
        } else if (page === 'gestao_fichas' && perfil === 'gestor') {
            data.fichas = await Form.find({ estado: 'Submetida' }).populate('user').populate('course');
        } else if (page === 'cursos' && perfil === 'gestor') {
            data.cursos = await Course.find();
        } else if (page === 'ucs' && perfil === 'gestor') {
            data.ucs = await UC.find();
        } else if (page === 'plano' && perfil === 'gestor' && req.query.curso_id) {
            const StudyPlan = require('../models/StudyPlan');
            data.curso = await Course.findById(req.query.curso_id);
            data.plano = await StudyPlan.find({ course: req.query.curso_id }).populate('uc');
            data.ucs = await UC.find();
        } else if (page === 'validar_pedidos' && ['funcionario', 'gestor'].includes(perfil)) {
            const Enrollment = require('../models/Enrollment');
            data.pedidos = await Enrollment.find({ estado: 'Pendente' }).populate('aluno').populate('course');
        } else if (page === 'pautas') {
            const Pauta = require('../models/Pauta');
            data.pautas = await Pauta.find().populate('uc');
            data.ucs = await UC.find();
        } else if (page === 'lancar_notas' && req.query.id) {
            const Pauta = require('../models/Pauta');
            const Grade = require('../models/Grade');
            data.info = await Pauta.findById(req.query.id).populate('uc');
            data.notas = await Grade.find({ pauta: req.query.id }).populate('aluno');
        }

        res.render('dashboard', data);
    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao carregar dashboard');
    }
});

module.exports = router;
