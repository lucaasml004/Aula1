const mongoose = require('mongoose');

const enrollmentSchema = new mongoose.Schema({
    aluno: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    course: { type: mongoose.Schema.Types.ObjectId, ref: 'Course', required: true },
    estado: { 
        type: String, 
        enum: ['Pendente', 'Aprovado', 'Rejeitado'], 
        default: 'Pendente' 
    },
    observacoes: { type: String },
    responsavel: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    data_decisao: { type: Date },
    criado_em: { type: Date, default: Date.now }
});

module.exports = mongoose.model('Enrollment', enrollmentSchema);
