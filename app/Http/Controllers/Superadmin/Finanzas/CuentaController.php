<?php

namespace App\Http\Controllers\Superadmin\Finanzas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finanzas\CuentaPaciente;
use App\Models\Finanzas\CuentaDetalle;
use App\Models\Finanzas\CostoEvento;
use App\Models\Finanzas\HonorarioMedico;

class CuentaController extends Controller
{
    public function index()
    {
        $cuentas = CuentaPaciente::latest()->paginate(15);
        return view('superadmin.finanzas.cuentas.index', compact('cuentas'));
    }

    public function generarCuenta(Request $request, $patient_id)
    {
        $costos = CostoEvento::where('patient_id', $patient_id)->get();
        $honorarios = HonorarioMedico::where('patient_id', $patient_id)->get();
        
        $subtotalCostos = $costos->sum('costo_total') + $honorarios->sum('monto');
        $subtotalCobro = $subtotalCostos * 2.0; // Ejemplo: 100% de margen
        $iva = $subtotalCobro * 0.16;
        $total = $subtotalCobro + $iva;
        $margen = $subtotalCobro - $subtotalCostos;

        $cuenta = CuentaPaciente::create([
            'patient_id' => $patient_id,
            'folio' => 'CUENTA-' . date('Ymd') . '-' . rand(1000, 9999),
            'subtotal_costos' => $subtotalCostos,
            'subtotal_cobro' => $subtotalCobro,
            'margen' => $margen,
            'iva' => $iva,
            'total_cobro' => $total
        ]);

        // Aqui se iterarian $costos y $honorarios para llenar cuenta_detalles

        return redirect()->route('superadmin.finanzas.ver', $cuenta->id)->with('status', 'Cuenta de cobro generada.');
    }

    public function show($id)
    {
        $cuenta = CuentaPaciente::findOrFail($id);
        $detalles = CuentaDetalle::where('cuenta_id', $id)->get();
        return view('superadmin.finanzas.cuentas.show', compact('cuenta', 'detalles'));
    }
}
