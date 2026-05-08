const mongoose = require('mongoose');

const formSchema = new mongoose.Schema({
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    course: { type: mongoose.Schema.Types.ObjectId, ref: 'Course' },
    nome_aluno: { type: String, required: true },
    turma: { type: String },
    data_nascimento: { type: Date },
    bi: { type: String, required: true },
    foto: { type: String },
    estado: { 
        type: String, 
        enum: ['Rascunho', 'Submetida', 'Aprovada', 'Rejeitada'], 
        default: 'Rascunho' 
    },
    observacoes: { type: String },
    validado_por: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    data_validacao: { type: Date },
    criado_em: { type: Date, default: Date.now }
});

module.exports = mongoose.model('Form', formSchema);
