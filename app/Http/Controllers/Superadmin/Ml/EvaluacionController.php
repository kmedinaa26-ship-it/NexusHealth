<?php
namespace App\Http\Controllers\Superadmin\Ml;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluacionController extends Controller
{
    public function index()
    {
        $casos = DB::table('predicciones_clinicas')
            ->leftJoin('resultados_reales', 'predicciones_clinicas.id', '=', 'resultados_reales.prediccion_id')
            ->where('predicciones_clinicas.estado', 'cerrada')
            ->whereNotNull('resultados_reales.resultado_real')
            ->get();

        $umbrales = [0.30, 0.50, 0.70];
        $metricas = [];

        foreach ($umbrales as $umbral) {
            $s = ['vp' => 0, 'vn' => 0, 'fp' => 0, 'fn' => 0];
            foreach ($casos as $caso) {
                $predAlto = $caso->probabilidad >= $umbral;
                $realAlto = $caso->resultado_real === 'fallecio';
                if ($predAlto && $realAlto) $s['vp']++;
                elseif (!$predAlto && !$realAlto) $s['vn']++;
                elseif ($predAlto && !$realAlto) $s['fp']++;
                else $s['fn']++;
            }
            $total = $s['vp'] + $s['vn'] + $s['fp'] + $s['fn'];
            $accuracy = $total > 0 ? round(($s['vp'] + $s['vn']) / $total * 100, 1) : 0;
            $precision = ($s['vp'] + $s['fp']) > 0 ? round($s['vp'] / ($s['vp'] + $s['fp']) * 100, 1) : 0;
            $recall = ($s['vp'] + $s['fn']) > 0 ? round($s['vp'] / ($s['vp'] + $s['fn']) * 100, 1) : 0;
            $f1 = ($precision + $recall) > 0 ? round(2 * $precision * $recall / ($precision + $recall), 1) : 0;
            $metricas[$umbral] = array_merge($s, compact('accuracy', 'precision', 'recall', 'f1'));
        }

        return view('superadmin.ml.evaluar', compact('metricas', 'umbrales'));
    }
}
