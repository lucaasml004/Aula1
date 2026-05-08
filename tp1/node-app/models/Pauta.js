const mongoose = require('mongoose');

const pautaSchema = new mongoose.Schema({
    uc: { type: mongoose.Schema.Types.ObjectId, ref: 'UC', required: true },
    ano_letivo: { type: String, required: true },
    epoca: { 
        type: String, 
        enum: ['Normal', 'Recurso', 'Especial'], 
        required: true 
    },
    criado_em: { type: Date, default: Date.now }
});

module.exports = mongoose.model('Pauta', pautaSchema);
