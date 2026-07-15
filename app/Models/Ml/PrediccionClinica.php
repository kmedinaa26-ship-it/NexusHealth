<?php

namespace App\Models\Ml;

use Illuminate\Database\Eloquent\Model;

class PrediccionClinica extends Model
{
    protected $table = 'predicciones_clinicas';
    protected $fillable = ['patient_id', 'modelo_version_id', 'datos_entrada', 'probabilidad', 'prediccion', 'score_confianza', 'estado'];
    protected $casts = ['datos_entrada' => 'array'];

    public function modelo() { return $this->belongsTo(MlModeloVersion::class, 'modelo_version_id'); }
    public function explicacion() { return $this->hasMany(ExplicacionPrediccion::class, 'prediccion_id'); }
    public function resultadoReal() { return $this->hasOne(ResultadoReal::class, 'prediccion_id'); }
    public function costos() { return $this->hasMany(\App\Models\Finanzas\CostoEvento::class, 'prediccion_id'); }
    public function honorarios() { return $this->hasMany(\App\Models\Finanzas\HonorarioMedico::class, 'prediccion_id'); }
}
