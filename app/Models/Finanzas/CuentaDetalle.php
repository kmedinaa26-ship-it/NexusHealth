<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Model;

class CuentaDetalle extends Model
{
    protected $table = 'cuentas_detalles';
    protected $fillable = ['cuenta_id', 'tipo_referencia', 'referencia_id', 'descripcion', 'costo_real', 'precio_cobro', 'subtotal'];

    public function cuenta() { return $this->belongsTo(CuentaPaciente::class, 'cuenta_id'); }
}
