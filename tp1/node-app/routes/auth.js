const express = require('express');
const router = express.Router();
const User = require('../models/User');

// Rota de Registo
router.post('/register', async (req, res) => {
    try {
        const { nome, email, senha } = req.body;

        // Verificar se e-mail já existe
        let user = await User.findOne({ email });
        if (user) {
            return res.render('registar', { erro: 'Já existe uma conta com este e-mail!', sucesso: '' });
        }

        // Criar novo usuário (o hash da senha é feito no Model User.js)
        user = new User({
            nome,
            email,
            senha,
            perfil: 'aluno'
        });

        await user.save();
        res.render('registar', { sucesso: 'Conta criada com sucesso! Já pode fazer login.', erro: '' });

    } catch (err) {
        console.error(err);
        res.render('registar', { erro: 'Erro ao criar conta.', sucesso: '' });
    }
});

// Rota de Login
router.post('/login', async (req, res) => {
    try {
        const { email, senha } = req.body;

        const user = await User.findOne({ email });
        if (!user || !(await user.comparePassword(senha))) {
            return res.render('login', { erro: 'Credenciais inválidas.' });
        }

        // Definir sessão
        req.session.userId = user._id;
        req.session.nome = user.nome;
        req.session.perfil = user.perfil;

        res.redirect('/dashboard');

    } catch (err) {
        console.error(err);
        res.render('login', { erro: 'Erro no servidor.' });
    }
});

// Logout
router.get('/logout', (req, res) => {
    req.session.destroy();
    res.redirect('/');
});

module.exports = router;
