<?php

namespace App\Models\Ml;

use Illuminate\Database\Eloquent\Model;

class ResultadoReal extends Model
{
    protected $table = 'resultados_reales';
    protected $fillable = ['prediccion_id', 'resultado_real', 'dias_hospitalizacion', 'costo_real', 'fecha_cierre', 'notas_doctor'];
    protected $casts = ['fecha_cierre' => 'date'];

    public function prediccion() { return $this->belongsTo(PrediccionClinica::class, 'prediccion_id'); }
}
