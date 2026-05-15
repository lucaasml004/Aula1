const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const Form = require('../models/Form');
const Enrollment = require('../models/Enrollment');
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
        
        // Limpeza de dados: se curso_id for vazio, removemos para evitar erro de cast do MongoDB
        const dadosFicha = {
            user: user_id,
            nome_aluno,
            turma,
            data_nascimento: data_nascimento || null,
            bi,
            estado: estado_novo
        };

        if (curso_id && curso_id !== "") {
            dadosFicha.course = curso_id;
        }

        console.log('Dados a gravar:', dadosFicha);

        if (req.file) {
            dadosFicha.foto = req.file.filename;
        }

        if (ficha) {
            console.log('A atualizar ficha existente:', ficha._id);
            await Form.findByIdAndUpdate(ficha._id, dadosFicha);
        } else {
            console.log('A criar nova ficha...');
            ficha = new Form(dadosFicha);
            await ficha.save();
        }
        
        console.log('✅ Ficha guardada com sucesso!');

        // --- NOVA LÓGICA: Se a ficha foi submetida, também efetuamos o pedido de matrícula ---
        if (estado_novo === 'Submetida' && curso_id) {
            console.log('Verificando matrícula para curso:', curso_id);
            const matriculaExistente = await Enrollment.findOne({
                aluno: user_id,
                course: curso_id,
                estado: { $ne: 'Rejeitado' }
            });

            if (!matriculaExistente) {
                console.log('Criando novo pedido de matrícula...');
                const novaMatricula = new Enrollment({
                    aluno: user_id,
                    course: curso_id,
                    estado: 'Pendente'
                });
                await novaMatricula.save();
                console.log('✅ Pedido de matrícula criado!');
            } else {
                console.log('Já existe uma matrícula ativa ou pendente para este curso.');
            }
        }

        res.redirect('/dashboard?page=minha_ficha&msg=sucesso');

    } catch (err) {
        console.error(err);
        res.status(500).send('Erro ao submeter ficha.');
    }
});

module.exports = router;
