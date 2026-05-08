const mongoose = require('mongoose');

const gradeSchema = new mongoose.Schema({
    pauta: { type: mongoose.Schema.Types.ObjectId, ref: 'Pauta', required: true },
    aluno: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    nota_final: { type: Number, min: 0, max: 20, default: null }
});

// Um aluno só pode ter uma nota por pauta
gradeSchema.index({ pauta: 1, aluno: 1 }, { unique: true });

module.exports = mongoose.model('Grade', gradeSchema);
