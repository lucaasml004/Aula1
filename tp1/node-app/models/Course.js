const mongoose = require('mongoose');

const courseSchema = new mongoose.Schema({
    nome: { type: String, required: true },
    estado: { 
        type: String, 
        enum: ['Ativo', 'Inativo'], 
        default: 'Ativo' 
    }
});

module.exports = mongoose.model('Course', courseSchema);
