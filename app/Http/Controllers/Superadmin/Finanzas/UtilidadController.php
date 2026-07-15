<?php
namespace App\Http\Controllers\Superadmin\Finanzas;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UtilidadController extends Controller
{
    public function index()
    {
        // Costos reales (de tabla costos_evento)
        $costosInsumos = DB::table('costos_evento')->sum('costo_total') ?? 0;

        // Cobros (de tabla payments)
        $cobros = DB::table('payments')->sum('amount') ?? 0;

        // Costos ML vs Costo Real (de resultados_reales)
        $prediccionesCerradas = DB::table('predicciones_clinicas')
            ->leftJoin('resultados_reales', 'predicciones_clinicas.id', '=', 'resultados_reales.prediccion_id')
            ->where('predicciones_clinicas.estado', 'cerrada')
            ->whereNotNull('resultados_reales.costo_real')
            ->select('predicciones_clinicas.id', 'predicciones_clinicas.datos_entrada', 'resultados_reales.costo_real')
            ->get();

        $costoEstimadoML = 0;
        $costoReal = 0;
        $diferencia = 0;
        $casosConCosto = 0;

        foreach ($prediccionesCerradas as $p) {
            $datos = json_decode($p->datos_entrada, true) ?: [];
            $estimado = $datos['costo_estimado'] ?? 0;
            $real = floatval($p->costo_real);

            $costoEstimadoML += $estimado;
            $costoReal += $real;
            $diferencia += abs($estimado - $real);
            $casosConCosto++;
        }

        $precisionCosto = $costoEstimadoML > 0 ? round((1 - ($diferencia / $costoEstimadoML)) * 100, 1) : 0;

        // Utilidad bruta
        $utilidad = $cobros - $costosInsumos;
        $margen = $cobros > 0 ? round(($utilidad / $cobros) * 100, 1) : 0;

        return view('superadmin.finanzas.utilidad', compact(
            'costosInsumos', 'cobros', 'utilidad', 'margen',
            'costoEstimadoML', 'costoReal', 'diferencia', 'precisionCosto', 'casosConCosto'
        ));
    }
}
