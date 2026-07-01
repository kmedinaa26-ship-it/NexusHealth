<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CostoEvento extends Model
{
    protected $table = 'costos_evento';
    protected $fillable = ['patient_id', 'prediccion_id', 'tipo', 'descripcion', 'cantidad', 'costo_unitario', 'costo_total', 'registrado_por'];

    public function prediccion() { return $this->belongsTo(\App\Models\Ml\PrediccionClinica::class, 'prediccion_id'); }
    public function registradoPor() { return $this->belongsTo(User::class, 'registrado_por'); }
}
