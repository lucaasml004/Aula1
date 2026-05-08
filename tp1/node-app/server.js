require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
const path = require('path');

const session = require('express-session');
const authRoutes = require('./routes/auth');
const formRoutes = require('./routes/forms');
const dashboardRoutes = require('./routes/dashboard');
const adminRoutes = require('./routes/admin');
const staffRoutes = require('./routes/staff');

const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Configurar Sessões
app.use(session({
    secret: process.env.JWT_SECRET || 'chave_mestra',
    resave: false,
    saveUninitialized: false,
    cookie: { secure: false } // em produção deve ser true com HTTPS
}));

// Configurar EJS como view engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Arquivos estáticos
app.use(express.static(path.join(__dirname, 'public')));
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Conexão ao MongoDB
mongoose.connect(process.env.MONGODB_URI)
    .then(() => console.log('Conectado ao MongoDB com sucesso!'))
    .catch(err => console.error('Erro ao conectar ao MongoDB:', err));

// Rotas
app.use('/auth', authRoutes);
app.use('/forms', formRoutes);
app.use('/dashboard', dashboardRoutes);
app.use('/admin', adminRoutes);
app.use('/staff', staffRoutes);

app.get('/', (req, res) => {
    res.redirect('/login');
});

app.get('/login', (req, res) => {
    res.render('login', { erro: null });
});

app.get('/registar', (req, res) => {
    res.render('registar', { erro: null, sucesso: null });
});

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
    console.log(`Servidor Node.js rodando na porta ${PORT}`);
});
