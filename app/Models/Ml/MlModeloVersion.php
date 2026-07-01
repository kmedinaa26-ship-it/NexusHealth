<?php

namespace App\Models\Ml;

use Illuminate\Database\Eloquent\Model;

class MlModeloVersion extends Model
{
    protected $table = 'ml_modelos_versiones';
    protected $fillable = ['nombre', 'algoritmo', 'ruta_archivo', 'metrica_f1', 'metrica_accuracy', 'estado', 'version', 'trained_at'];
    protected $casts = ['trained_at' => 'datetime'];

    public function predicciones() { return $this->hasMany(PrediccionClinica::class, 'modelo_version_id'); }
    public function drifts() { return $this->hasMany(MetricaDriftModelo::class, 'modelo_version_id'); }
    public function alertas() { return $this->hasMany(AlertaMl::class, 'modelo_version_id'); }
}
