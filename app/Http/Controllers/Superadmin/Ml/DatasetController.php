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
            ->leftJoin('users', 'predicciones_clinicas.doctor_id', '=', 'users.id')
            ->select('predicciones_clinicas.*', 'resultados_reales.resultado_real', 'resultados_reales.dias_hospitalizacion', 'resultados_reales.costo_real', 'users.name as doctor_name')
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
        DB::table('predicciones_clinicas')->where('id', $request->id)->update([
            'aprobado_para_entrenamiento' => $nuevoEstado,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'aprobado' => $nuevoEstado]);
    }

    public function exportarCSV()
    {
        $casos = DB::table('predicciones_clinicas')
            ->leftJoin('resultados_reales', 'predicciones_clinicas.id', '=', 'resultados_reales.prediccion_id')
            ->where('predicciones_clinicas.estado', 'cerrada')
            ->where('predicciones_clinicas.aprobado_para_entrenamiento', 1)
            ->get();

        if ($casos->isEmpty()) {
            return redirect()->back()->with('error', 'No hay casos aprobados para exportar');
        }

        $filename = 'dataset_ml_' . now()->format('Ymd_His') . '.csv';
        $headers = ['caso_id', 'patient_id', 'doctor_id', 'probabilidad', 'prediccion', 'resultado_real', 'dias_estimados', 'dias_reales', 'costo_estimado', 'costo_real'];

        $csv = fopen('php://temp', 'w');
        fputcsv($csv, $headers);
        foreach ($casos as $c) {
            $datos = json_decode($c->datos_entrada, true) ?: [];
            fputcsv($csv, [
                $c->id,
                $c->patient_id,
                $c->doctor_id,
                $c->probabilidad,
                $c->prediccion,
                $c->resultado_real ?? '',
                $datos['dias_estimados'] ?? '',
                $c->dias_hospitalizacion ?? '',
                $datos['costo_estimado'] ?? '',
                $c->costo_real ?? '',
            ]);
        }
        fclose($csv);

        return response(stream_get_contents($csv), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
