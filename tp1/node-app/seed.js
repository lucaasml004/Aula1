require('dotenv').config();
const mongoose = require('mongoose');
const User = require('./models/User');
const Course = require('./models/Course');
const UC = require('./models/UC');

const seed = async () => {
    try {
        await mongoose.connect(process.env.MONGODB_URI);
        console.log('Ligado ao MongoDB para seeding...');

        // Limpar dados antigos (opcional - cuidado)
        // await User.deleteMany({});
        // await Course.deleteMany({});
        // await UC.deleteMany({});

        // 1. Criar Utilizadores
        const users = [
            { nome: 'Gestor Pedagógico', email: 'gestor@ipca.pt', senha: '123456', perfil: 'gestor' },
            { nome: 'Funcionário SA', email: 'func@ipca.pt', senha: '123456', perfil: 'funcionario' },
            { nome: 'João Aluno', email: 'aluno@ipca.pt', senha: '123456', perfil: 'aluno' }
        ];

        for (let u of users) {
            const exists = await User.findOne({ email: u.email });
            if (!exists) {
                const newUser = new User(u);
                await newUser.save();
                console.log(`Utilizador ${u.nome} criado.`);
            }
        }

        // 2. Criar Cursos e UCs exemplo
        const cursos = ['Engenharia Informática', 'Design Gráfico'];
        for (let c of cursos) {
            const exists = await Course.findOne({ nome: c });
            if (!exists) {
                await new Course({ nome: c }).save();
                console.log(`Curso ${c} criado.`);
            }
        }

        const ucs = ['Programação Web', 'Bases de Dados', 'Matemática'];
        for (let u of ucs) {
            const exists = await UC.findOne({ nome: u });
            if (!exists) {
                await new UC({ nome: u }).save();
                console.log(`UC ${u} criada.`);
            }
        }

        console.log('Seed concluído com sucesso!');
        process.exit();

    } catch (err) {
        console.error('Erro no seed:', err);
        process.exit(1);
    }
};

seed();
