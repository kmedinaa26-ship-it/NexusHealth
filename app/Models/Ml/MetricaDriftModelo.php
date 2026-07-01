<?php

namespace App\Models\Ml;

use Illuminate\Database\Eloquent\Model;

class MetricaDriftModelo extends Model
{
    protected $table = 'metricas_drift_modelo';
    protected $fillable = ['modelo_version_id', 'f1_score_actual', 'accuracy_actual', 'cantidad_datos_evaluados', 'drift_detectado', 'fecha_evaluacion'];
    protected $casts = ['fecha_evaluacion' => 'datetime', 'drift_detectado' => 'boolean'];

    public function modelo() { return $this->belongsTo(MlModeloVersion::class, 'modelo_version_id'); }
}
