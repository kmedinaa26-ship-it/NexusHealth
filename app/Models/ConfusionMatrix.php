<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfusionMatrix extends Model
{
    protected $fillable = [
        'model_type', 'tp', 'tn', 'fp', 'fn', 
        'accuracy', 'precision', 'recall', 'f1_score',
        'mse', 'rmse', 'mae', 'evaluated_at'
    ];

    protected $casts = [
        'evaluated_at' => 'date'
    ];
}
