<?php
namespace App\Http\Controllers\Superadmin\Ml;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatasetController extends Controller
{
    public function index()
    {
        $casos = DB::table('predicciones_clinicas')
            ->leftJoin('resultados_reales', 'predicciones_clinicas.id', '=', 'resultados_reales.prediccion_id')
            ->leftJoin('triages', 'predicciones_clinicas.patient_id', '=', 'triages.id')
            ->select('predicciones_clinicas.*', 'resultados_reales.resultado_real', 'resultados_reales.dias_hospitalizacion', 'resultados_reales.costo_real', 'triages.patient_name')
            ->where('predicciones_clinicas.estado', 'cerrada')
            ->orderBy('predicciones_clinicas.updated_at', 'desc')
            ->get();

        $total = $casos->count();
        $aprobados = $casos->filter(fn($c) => $c->aprobado_para_entrenamiento ?? false)->count();

        return view('superadmin.ml.dataset', compact('casos', 'total', 'aprobados'));
    }

    public function toggleAprobado(Request $request)
    {
        $caso = DB::table('predicciones_clinicas')->where('id', $request->id)->first();
        if (!$caso) return response()->json(['error' => 'No encontrado'], 404);

        $nuevoEstado = !($caso->aprobado_para_entrenamiento ?? false);
        DB::table('predicciones_clinicas')->where('id', $request->id)->update(['aprobado_para_entrenamiento' => $nuevoEstado]);

        return response()->json(['aprobado' => $nuevoEstado]);
    }
}
