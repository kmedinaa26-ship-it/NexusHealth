<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class HonorarioMedico extends Model
{
    protected $table = 'honorarios_medicos';
    protected $fillable = ['patient_id', 'doctor_id', 'prediccion_id', 'concepto', 'monto', 'metodo_pago', 'pagado'];
    protected $casts = ['pagado' => 'boolean'];

    public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
    public function prediccion() { return $this->belongsTo(\App\Models\Ml\PrediccionClinica::class, 'prediccion_id'); }
}
