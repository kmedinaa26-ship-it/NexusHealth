<?php
namespace App\Http\Controllers\Medico;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostosController extends Controller
{
    public function index()
    {
        $costos = DB::table('costos_evento')
            ->where('registrado_por', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('medico.costos.index', compact('costos'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'patient_id' => 'nullable|integer',
            'descripcion' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0',
            'tipo' => 'required|in:insumo,papel,gas_medico,otro',
        ]);

        $costoTotal = $request->cantidad * $request->costo_unitario;

        DB::table('costos_evento')->insert([
            'patient_id' => $request->patient_id,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            'cantidad' => $request->cantidad,
            'costo_unitario' => $request->costo_unitario,
            'costo_total' => $costoTotal,
            'registrado_por' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Costo registrado: $' . number_format($costoTotal, 2));
    }
}
