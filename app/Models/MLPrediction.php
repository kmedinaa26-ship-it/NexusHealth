<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLPrediction extends Model
{
    // Forzamos el nombre exacto de la tabla para evitar que Laravel busque m_l_predictions
    protected $table = 'ml_predictions';

    protected $fillable = [
        'patient_id', 'doctor_id', 'model_type', 'input_data', 'output_data', 'real_outcome', 'is_correct'
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'real_outcome' => 'array',
        'is_correct' => 'boolean'
    ];
}
