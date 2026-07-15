<?php

namespace App\Http\Controllers\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finanzas\CostoEvento;
use App\Models\Finanzas\HonorarioMedico;

class CostoController extends Controller
{
    public function index($patient_id)
    {
        $costos = CostoEvento::where('patient_id', $patient_id)->get();
        $honorarios = HonorarioMedico::where('patient_id', $patient_id)->get();
        $total = $costos->sum('costo_total') + $honorarios->sum('monto');
        
        return view('medico.costos.index', compact('costos', 'honorarios', 'total', 'patient_id'));
    }

    public function storeCosto(Request $request)
    {
        CostoEvento::create([
            'patient_id' => $request->patient_id,
            'prediccion_id' => $request->prediccion_id,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            'cantidad' => $request->cantidad,
            'costo_unitario' => $request->costo_unitario,
            'costo_total' => $request->cantidad * $request->costo_unitario,
            'registrado_por' => auth()->id()
        ]);

        return back()->with('status', 'Costo de insumo registrado.');
    }

    public function storeHonorario(Request $request)
    {
        HonorarioMedico::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'prediccion_id' => $request->prediccion_id,
            'concepto' => $request->concepto,
            'monto' => $request->monto
        ]);

        return back()->with('status', 'Honorario medico registrado.');
    }
}
