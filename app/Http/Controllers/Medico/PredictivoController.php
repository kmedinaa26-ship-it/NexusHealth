<?php
namespace App\Http\Controllers\Medico;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PredictivoController extends Controller
{
    public function simular()
    {
        return view('medico.predictivo.simulador');
    }

    public function index()
    {
        $predicciones = DB::table('predicciones_clinicas')
            ->where('doctor_id', auth()->id())
            ->where('estado', 'activa')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('medico.predictivo.index', compact('predicciones'));
    }

    public function crear(Request $request)
    {
        $request->validate([
            'patient_id' => 'nullable|integer',
            'riesgo_mortalidad' => 'required|numeric|min:0|max:100',
            'dias_estimados' => 'required|integer',
            'costo_estimado' => 'required|numeric',
            'escenario' => 'required|string',
            'parametros' => 'required|array',
        ]);

        $versionActiva = DB::table('ml_modelos_versiones')
            ->where('estado', 'activo')
            ->orderBy('version', 'desc')
            ->first();

        if (!$versionActiva) {
            $maxVer = DB::table('ml_modelos_versiones')->max('version') ?? 0;
            $versionId = DB::table('ml_modelos_versiones')->insertGetId([
                'nombre' => 'modelo_v' . ($maxVer + 1),
                'algoritmo' => 'regresion_logistica',
                'ruta_archivo' => 'models/default_model.pkl',
                'metrica_f1' => 0.7500,
                'metrica_accuracy' => 0.8000,
                'estado' => 'activo',
                'version' => $maxVer + 1,
                'trained_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $versionId = $versionActiva->id;
        }

        $riesgo = $request->riesgo_mortalidad / 100;
        $datos = $request->parametros;
        $datos['escenario'] = $request->escenario;
        $datos['dias_estimados'] = $request->dias_estimados;
        $datos['costo_estimado'] = $request->costo_estimado;

        $id = DB::table('predicciones_clinicas')->insertGetId([
            'patient_id' => $request->patient_id,
            'doctor_id' => auth()->id(),
            'modelo_version_id' => $versionId,
            'datos_entrada' => json_encode($datos),
            'probabilidad' => $riesgo,
            'prediccion' => $riesgo >= 0.5 ? 'alto_riesgo' : 'bajo_riesgo',
            'score_confianza' => $riesgo,
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Guardar explicabilidad con variables JSON completo en cada fila
        if ($request->explicabilidad) {
            $varsJson = json_encode($request->explicabilidad);
            foreach ($request->explicabilidad as $var) {
                DB::table('explicacion_prediccion')->insert([
                    'prediccion_id' => $id,
                    'variables' => $varsJson,
                    'variable' => $var['nombre'],
                    'peso' => $var['valor'] / 100,
                    'impacto' => $var['valor'] >= 15 ? 'alto' : ($var['valor'] >= 8 ? 'medio' : 'bajo'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function resultados()
    {
        $pendientes = DB::table('predicciones_clinicas')
            ->where('doctor_id', auth()->id())
            ->where('estado', 'activa')
            ->orderBy('created_at', 'desc')
            ->get();

        $cerrados = DB::table('predicciones_clinicas')
            ->leftJoin('resultados_reales', 'predicciones_clinicas.id', '=', 'resultados_reales.prediccion_id')
            ->select('predicciones_clinicas.*', 'resultados_reales.resultado_real', 'resultados_reales.dias_hospitalizacion', 'resultados_reales.costo_real')
            ->where('predicciones_clinicas.doctor_id', auth()->id())
            ->where('predicciones_clinicas.estado', 'cerrada')
            ->orderBy('predicciones_clinicas.updated_at', 'desc')
            ->get();

        return view('medico.predictivo.resultados', compact('pendientes', 'cerrados'));
    }

    public function guardarResultado(Request $request)
    {
        $request->validate([
            'prediccion_id' => 'required|exists:predicciones_clinicas,id',
            'resultado_real' => 'required|in:fallecio,vivo',
            'dias_reales' => 'required|integer',
            'costo_real' => 'nullable|numeric',
        ]);

        DB::table('resultados_reales')->insert([
            'prediccion_id' => $request->prediccion_id,
            'resultado_real' => $request->resultado_real,
            'dias_hospitalizacion' => $request->dias_reales,
            'costo_real' => $request->costo_real,
            'fecha_cierre' => now()->toDateString(),
            'notas_doctor' => 'Cerrado desde modulo predictivo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('predicciones_clinicas')
            ->where('id', $request->prediccion_id)
            ->update(['estado' => 'cerrada', 'updated_at' => now()]);

        return back()->with('success', 'Resultado registrado. Este caso ahora alimenta el modelo ML.');
    }

    public function graficas()
    {
        return view('medico.predictivo.graficas');
    }
}
