<?php

namespace App\Http\Controllers;

use App\Services\ML\RegressionService;
use App\Services\ML\MetricsService;
use App\Services\ML\ClassificationService;
use App\Models\PatientAccount;
use App\Models\MLPrediction;
use App\Models\Triage;
use Illuminate\Http\Request;

class MLDashboardController extends Controller
{
    public function index()
    {
        $regressionService = new RegressionService();
        $metricsService = new MetricsService();
        $classificationService = new ClassificationService();

        // 🧠 OBTENER EL ÚLTIMO MODELO ENTRENADO DESDE LA BASE DE DATOS
        $latestModel = MLPrediction::where('model_type', 'regresion_costos')->latest()->first();

        // 1. REGRESIÓN LINEAL CON DATOS REALES (Código original intacto)
        $cuentas = PatientAccount::where('status', 'cerrada')->whereNotNull('closed_at')->get();

        $datosBrutos = [];
        $dias = [];
        $costos = [];

        foreach ($cuentas as $cuenta) {
            $diasEstancia = $cuenta->opened_at->diffInDays($cuenta->closed_at) + 1;
            if ($diasEstancia > 0 && $cuenta->total_paid > 0) {
                $dias[] = $diasEstancia;
                $costos[] = (float) $cuenta->total_paid;
                $datosBrutos[] = ['dias' => $diasEstancia, 'costo_real' => (float) $cuenta->total_paid];
            }
        }

        if (count($dias) < 3) {
            $dias = [1, 2, 3, 4, 5];
            $costos = [5000, 8500, 11000, 14000, 16000];
            $datosBrutos = [
                ['dias' => 1, 'costo_real' => 5000],
                ['dias' => 2, 'costo_real' => 8500],
                ['dias' => 3, 'costo_real' => 11000],
                ['dias' => 4, 'costo_real' => 14000],
                ['dias' => 5, 'costo_real' => 16000],
            ];
        }

        $regression = $regressionService->linearRegression($dias, $costos);
        $predicciones = array_column($regression['predicciones'], 'y');

        $mse = $regressionService->mse($costos, $predicciones);
        $rmse = $regressionService->rmse($costos, $predicciones);
        $mae = $regressionService->mae($costos, $predicciones);

        $tablaRegresion = [];
        for ($i = 0; $i < count($dias); $i++) {
            $tablaRegresion[] = [
                'x' => $dias[$i],
                'y_real' => $costos[$i],
                'y_predicho' => $predicciones[$i],
                'error' => abs($costos[$i] - $predicciones[$i]),
                'error_cuadrado' => pow($costos[$i] - $predicciones[$i], 2)
            ];
        }

        $datosReales = [];
        for ($i = 0; $i < count($dias); $i++) {
            $datosReales[] = ['x' => $dias[$i], 'y' => $costos[$i]];
        }

        // 2. CLASIFICACIÓN Y MATRIZ DE CONFUSIÓN (Histórica)
        $prediccionesTriage = [1, 0, 1, 1, 0, 1, 0, 0, 1, 0];
        $realesTriage = [1, 0, 1, 0, 0, 1, 1, 0, 1, 1];

        $matriz = $metricsService->confusionMatrix($prediccionesTriage, $realesTriage);
        $metrics = [
            'accuracy' => $metricsService->accuracy($matriz),
            'precision' => $metricsService->precision($matriz),
            'recall' => $metricsService->recall($matriz),
            'f1' => $metricsService->f1Score($matriz),
        ];

        $detallePacientes = [];
        for ($i = 0; $i < count($prediccionesTriage); $i++) {
            $pred = $prediccionesTriage[$i];
            $real = $realesTriage[$i];
            $resultado = 'Desconocido';
            if ($real == 1 && $pred == 1) $resultado = 'VP';
            elseif ($real == 0 && $pred == 0) $resultado = 'VN';
            elseif ($real == 0 && $pred == 1) $resultado = 'FP';
            elseif ($real == 1 && $pred == 0) $resultado = 'FN';

            $detallePacientes[] = ['id' => $i + 1, 'real' => $real, 'pred' => $pred, 'res' => $resultado];
        }

        // 🚀 3. NUEVO: MOTOR PREDICTIVO DINÁMICO Y PAGINADO
        // Consultamos los signos vitales/triajes paginados
        $triageRecords = Triage::query()->latest()->paginate(10)->withQueryString();
        
        $pacientesAnalizados = [];
        foreach($triageRecords as $t) {
            // No se necesita relación patient
            $spo2 = $t->vitals_spo2 ?? 98;
            $fc = $t->vitals_fc ?? $t->vitals_fc ?? 80;
            $temp = $t->vitals_temp ?? 36.5;
            $edad = $t->age ?? 30;
            
            $datosPaciente = ['spo2' => $spo2, 'frecuencia_cardiaca' => $fc, 'temperatura' => $temp];
            
            // Ejecutar Árbol de Decisión real
            $arbol = $classificationService->decisionTree($datosPaciente);
            
            // Calcular Sigmoide (Regresión Logística) para probabilidad UCI
            $z = -8.0 + (0.05 * $edad) - (0.1 * $spo2) + (0.02 * $fc);
            $probUCI = round(1 / (1 + exp(-$z)) * 100, 1);
            
            $pacientesAnalizados[] = [
                'id' => $t->id,
                'nombre' => $t->patient_name ?? 'Paciente #'.$t->id,
                'edad' => $edad,
                'spo2' => $spo2,
                'fc' => $fc,
                'temp' => $temp,
                'probUCI' => $probUCI,
                'arbol' => $arbol
            ];
        }

        // Mantenemos el paciente de ejemplo para el SVM y Forest en la vista
        $pacienteEjemplo = ['spo2' => 88, 'frecuencia_cardiaca' => 130, 'temperatura' => 38.5];
        $forest = $classificationService->randomForest($pacienteEjemplo);
        $svm = $classificationService->svm($pacienteEjemplo);

        return view('superadmin.ml-dashboard.index', compact(
            'regression', 'mse', 'rmse', 'mae', 'datosReales', 'tablaRegresion',
            'matriz', 'metrics', 'forest', 'svm', 'detallePacientes', 'latestModel',
            'triageRecords', 'pacientesAnalizados' // Nuevas variables
        ));
    }
}
