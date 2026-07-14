<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Model;

class CuentaPaciente extends Model
{
    protected $table = 'cuentas_paciente';
    protected $fillable = ['patient_id', 'folio', 'subtotal_costos', 'subtotal_cobro', 'margen', 'iva', 'total_cobro', 'estado', 'metodo_pago'];

    public function detalles() { return $this->hasMany(CuentaDetalle::class, 'cuenta_id'); }
}
