const mongoose = require('mongoose');

const studyPlanSchema = new mongoose.Schema({
    course: { type: mongoose.Schema.Types.ObjectId, ref: 'Course', required: true },
    uc: { type: mongoose.Schema.Types.ObjectId, ref: 'UC', required: true },
    ano: { type: Number, required: true },
    semestre: { type: Number, required: true, enum: [1, 2] }
});

// Garantir que não existam duplicados (mesma UC no mesmo curso)
studyPlanSchema.index({ course: 1, uc: 1 }, { unique: true });

module.exports = mongoose.model('StudyPlan', studyPlanSchema);
