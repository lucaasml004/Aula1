// ==========================================
// MODELO DE UTILIZADOR (MONGODB SCHEMA)
// ==========================================
// Raciocínio: No MongoDB, os dados não têm tabelas fixas, por isso usamos 
// um "Schema" para definir que campos um utilizador deve ter.

const mongoose = require('mongoose');
const bcrypt = require('bcryptjs'); // Biblioteca para encriptar passwords

const userSchema = new mongoose.Schema({
    nome: { type: String, required: true },
    email: { type: String, required: true, unique: true }, // unique garante que não há emails repetidos
    senha: { type: String, required: true },
    perfil: { 
        type: String, 
        enum: ['aluno', 'funcionario', 'gestor'], // Apenas estes 3 cargos são permitidos
        required: true 
    },
    criado_em: { type: Date, default: Date.now }
});

// --- SEGURANÇA: HASH AUTOMÁTICO ---
// Raciocínio: Sempre que gravamos um utilizador ("save"), o código intercetiva 
// a password e transforma-a num Hash ilegível antes de a enviar para a Cloud.
userSchema.pre('save', async function(next) {
    if (!this.isModified('senha')) return next();
    this.senha = await bcrypt.hash(this.senha, 10);
    next();
});

// Método utilitário para verificar se a password escrita no login está correta
userSchema.methods.comparePassword = async function(candidatePassword) {
    return await bcrypt.compare(candidatePassword, this.senha);
};

module.exports = mongoose.model('User', userSchema);
