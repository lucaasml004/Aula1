const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const Form = require('../models/Form');
const auth = require('../middleware/authMiddleware');

// Configuração do Multer para Uploads
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        cb(null, 'uploads/');
    },
    filename: (req, file, cb) => {
        cb(null, Date.now() + "_" + path.basename(file.originalname));
    }
});

const upload = multer({ 
    storage: storage,
    limits: { fileSize: 2 * 1024 * 1024 }, // 2MB
    fileFilter: (req, file, cb) => {
        const filetypes = /jpeg|jpg|png/;
        const extname = filetypes.test(path.extname(file.originalname).toLowerCase());
        const mimetype = filetypes.test(file.mimetype);
        if (mimetype && extname) return cb(null, true);
        cb(new Error('Apenas imagens (jpg, jpeg, png) são permitidas!'));
    }
});

// Rota para submeter ficha
router.post('/submeter', auth(['aluno']), upload.single('foto'), async (req, res) => {
    try {
        const { curso_id, nome_aluno, turma, data_nascimento, bi, acao } = req.body;
        const estado_novo = (acao === 'Submetida') ? 'Submetida' : 'Rascunho';
        const user_id = req.session.userId;

        let ficha = await Form.findOne({ user: user_id });

        const dadosFicha = {
            user: user_id,
            course: curso_id, // Note: No MongoDB usaremos o ID do documento Course
            nome_aluno,
            turma,
            data_nascimento,
            bi,
            estado: estado_novo
        };

        if (req.file) {
            dadosFicha.foto = req.file.filename;
        }

        if (ficha) {
            // Update
            await Form.findByIdAndUpdate(ficha._id, dadosFicha);
        } else {
            // Create
            ficha = new Form(dadosFicha);
            await ficha.save();
        }

        res.redirect('/dashboard?page=minha_ficha&msg=sucesso');

    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao submeter ficha.');
    }
});

module.exports = router;
