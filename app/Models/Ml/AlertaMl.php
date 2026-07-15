<?php

namespace App\Models\Ml;

use Illuminate\Database\Eloquent\Model;

class AlertaMl extends Model
{
    protected $table = 'alertas_ml';
    protected $fillable = ['modelo_version_id', 'patient_id', 'tipo', 'mensaje', 'leida'];
    protected $casts = ['leida' => 'boolean'];

    public function modelo() { return $this->belongsTo(MlModeloVersion::class, 'modelo_version_id'); }
}
