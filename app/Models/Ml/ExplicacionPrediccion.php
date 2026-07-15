<?php

namespace App\Models\Ml;

use Illuminate\Database\Eloquent\Model;

class ExplicacionPrediccion extends Model
{
    protected $table = 'explicacion_prediccion';
    protected $fillable = ['prediccion_id', 'variable', 'peso', 'impacto'];

    public function prediccion() { return $this->belongsTo(PrediccionClinica::class, 'prediccion_id'); }
}
