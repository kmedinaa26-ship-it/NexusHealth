<?php
namespace App\Services\ML;

use App\Models\Triage;
use App\Services\ML\RegressionService;
use App\Services\ML\ClassificationService;
use App\Services\ML\MetricsService;

class IAMedicaService
{
    protected $regression;
    protected $classification;
    protected $metrics;

    public function __construct()
    {
        $this->regression     = new RegressionService();
        $this->classification = new ClassificationService();
        $this->metrics        = new MetricsService();
    }

    public function getDataset(): array
    {
        $triages = Triage::whereNotNull('vitals_fc')
            ->whereNotNull('vitals_spo2')
            ->whereNotNull('vitals_temp')
            ->whereNotNull('age')
            ->latest()
            ->take(200)
            ->get();

        $data = [];
        foreach ($triages as $t) {
            $data[] = [
                'id'      => $t->id,
                'nombre'  => $t->patient_name ?? 'Paciente #'.$t->id,
                'edad'    => (int)($t->age ?? 30),
                'fc'      => (float)($t->vitals_fc ?? 80),
                'spo2'    => (float)($t->vitals_spo2 ?? 98),
                'temp'    => (float)($t->vitals_temp ?? 36.5),
                'ta'      => (float)($t->vitals_ta ?? 120),
                'nivel'   => $t->triage_level ?? 'Verde',
                'critico' => in_array($t->triage_level, ['Rojo','Naranja']) ? 1 : 0,
            ];
        }
        return $data;
    }

    public function limpiarDatos(array $data): array
    {
        return array_values(array_filter($data, function($p) {
            return $p['fc']   >= 40  && $p['fc']   <= 200
                && $p['spo2'] >= 70  && $p['spo2'] <= 100
                && $p['temp'] >= 35  && $p['temp'] <= 42
                && $p['edad'] >= 0   && $p['edad'] <= 120;
        }));
    }

    public function dividirDatos(array $data): array
    {
        $n     = count($data);
        $corte = (int)($n * 0.8);
        return [
            'train'   => array_slice($data, 0, $corte),
            'test'    => array_slice($data, $corte),
            'total'   => $n,
            'n_train' => $corte,
            'n_test'  => $n - $corte,
        ];
    }

    public function modelo1_RegresionLogistica(array $train, array $test): array
    {
        $b0 = -15.0; $b1 = 0.15; $b2 = -0.04; $b3 = 0.0;

        $predicciones = [];
        $reales       = [];

        foreach ($test as $p) {
            $z              = $b0 + ($b1 * $p['fc']) + ($b2 * $p['spo2']);
            $prob           = round(1 / (1 + exp(-$z)), 4);
            $predicciones[] = $prob >= 0.5 ? 1 : 0;
            $reales[]       = $p['critico'];
        }

        $matriz = $this->metrics->confusionMatrix($predicciones, $reales);

        return [
            'nombre'       => 'Regresion Logistica',
            'icono'        => 'fas fa-chart-line',
            'color'        => '#6366F1',
            'descripcion'  => 'Predice probabilidad de triage critico usando funcion Sigmoide.',
            'formula'      => 'P(critico) = 1 / (1 + e^-z)',
            'z_formula'    => 'z = -15.0 + 0.15*FC - 0.04*SpO2',
            'coeficientes' => ['B0' => $b0, 'B1 FC' => $b1, 'B2 SpO2' => $b2],
            'tipo'         => 'clasificacion',
            'matriz'       => $matriz,
            'accuracy'     => $this->metrics->accuracy($matriz),
            'precision'    => $this->metrics->precision($matriz),
            'recall'       => $this->metrics->recall($matriz),
            'f1'           => $this->metrics->f1Score($matriz),
            'n_test'       => count($test),
            'ventajas'     => ['Facil interpretacion','Rapido','Ideal para binario'],
            'desventajas'  => ['Limitado para clases complejas'],
        ];
    }

    public function modelo2_ArbolDecision(array $train, array $test): array
    {
        $totalTrain = count($train);
        $criticos   = count(array_filter($train, fn($p) => $p['critico'] == 1));
        $noCriticos = $totalTrain - $criticos;
        $pA         = $totalTrain > 0 ? $criticos / $totalTrain   : 0;
        $pB         = $totalTrain > 0 ? $noCriticos / $totalTrain : 0;
        $gini       = round(1 - (pow($pA, 2) + pow($pB, 2)), 4);

        $entropia = 0;
        if ($pA > 0) $entropia -= $pA * log($pA, 2);
        if ($pB > 0) $entropia -= $pB * log($pB, 2);
        $entropia = round($entropia, 4);

        $predicciones = [];
        $reales       = [];

        foreach ($test as $p) {
            $resultado      = $this->classification->decisionTree([
                'spo2'                => $p['spo2'],
                'frecuencia_cardiaca' => $p['fc'],
                'temperatura'         => $p['temp'],
            ]);
            $predicciones[] = $resultado['nivel'] <= 2 ? 1 : 0;
            $reales[]       = $p['critico'];
        }

        $matriz = $this->metrics->confusionMatrix($predicciones, $reales);

        return [
            'nombre'           => 'Arbol de Decision',
            'icono'            => 'fas fa-sitemap',
            'color'            => '#10B981',
            'descripcion'      => 'Clasifica nivel de triage mediante reglas jerarquicas.',
            'tipo'             => 'clasificacion',
            'gini'             => $gini,
            'entropia'         => $entropia,
            'formula_gini'     => 'Gini = 1 - (Pa^2 + Pb^2) = 1 - ('.round($pA,2).'^2 + '.round($pB,2).'^2)',
            'formula_entropia' => 'H = -Sum Pi * log2(Pi)',
            'nodo_raiz'        => 'SpO2 < 90%',
            'nodos_internos'   => ['FC > 120 lpm', 'Temp > 38 C'],
            'hojas'            => ['UCI Inmediata','Observacion','Control Febril','Estable'],
            'matriz'           => $matriz,
            'accuracy'         => $this->metrics->accuracy($matriz),
            'precision'        => $this->metrics->precision($matriz),
            'recall'           => $this->metrics->recall($matriz),
            'f1'               => $this->metrics->f1Score($matriz),
            'n_test'           => count($test),
            'ventajas'         => ['Facil interpretacion','Visualizable','Maneja categoricos'],
            'desventajas'      => ['Puede sobreajustar','Sensible a cambios pequenos'],
        ];
    }

    public function modelo3_RandomForest(array $train, array $test): array
    {
        $arboles = [
            ['spo2_th' => 90, 'fc_th' => 120, 'temp_th' => 38.0, 'nombre' => 'Arbol 1 (SpO2 + FC)'],
            ['spo2_th' => 92, 'fc_th' => 110, 'temp_th' => 38.5, 'nombre' => 'Arbol 2 (SpO2 + Temp)'],
            ['spo2_th' => 88, 'fc_th' => 130, 'temp_th' => 39.0, 'nombre' => 'Arbol 3 (FC + Temp)'],
        ];

        $predicciones = [];
        $reales       = [];

        foreach ($test as $p) {
            $votos = [];
            foreach ($arboles as $a) {
                $votos[] = ($p['spo2'] < $a['spo2_th'] || $p['fc'] > $a['fc_th'] || $p['temp'] > $a['temp_th']) ? 1 : 0;
            }
            $predicciones[] = array_sum($votos) >= 2 ? 1 : 0;
            $reales[]       = $p['critico'];
        }

        $matriz = $this->metrics->confusionMatrix($predicciones, $reales);

        return [
            'nombre'      => 'Random Forest',
            'icono'       => 'fas fa-tree',
            'color'       => '#F59E0B',
            'descripcion' => 'Ensemble de 3 arboles — gana la clase con mas votos.',
            'tipo'        => 'clasificacion',
            'n_arboles'   => 3,
            'arboles'     => $arboles,
            'importancia' => ['SpO2' => 42, 'FC' => 31, 'Temp' => 18, 'Edad' => 9],
            'matriz'      => $matriz,
            'accuracy'    => $this->metrics->accuracy($matriz),
            'precision'   => $this->metrics->precision($matriz),
            'recall'      => $this->metrics->recall($matriz),
            'f1'          => $this->metrics->f1Score($matriz),
            'n_test'      => count($test),
            'ventajas'    => ['Mayor precision','Menor sobreajuste','Feature importance'],
            'desventajas' => ['Mas complejo','Menos interpretable'],
        ];
    }

    public function modelo4_SVM(array $train, array $test): array
    {
        $vectoresSoporte = [];
        foreach ($train as $p) {
            $decision = (0.6 * $p['fc']) + (-0.8 * $p['spo2']) + 30;
            if (abs($decision) <= 15) {
                $vectoresSoporte[] = [
                    'nombre'   => $p['nombre'],
                    'fc'       => $p['fc'],
                    'spo2'     => $p['spo2'],
                    'decision' => round($decision, 2),
                ];
                if (count($vectoresSoporte) >= 3) break;
            }
        }

        $predicciones = [];
        $reales       = [];

        foreach ($test as $p) {
            $res            = $this->classification->svm([
                'frecuencia_cardiaca' => $p['fc'],
                'spo2'                => $p['spo2'],
            ]);
            $predicciones[] = $res['clase'] === 'ALTO RIESGO' ? 1 : 0;
            $reales[]       = $p['critico'];
        }

        $matriz = $this->metrics->confusionMatrix($predicciones, $reales);

        return [
            'nombre'           => 'SVM',
            'icono'            => 'fas fa-bullseye',
            'color'            => '#EF4444',
            'descripcion'      => 'Encuentra el hiperplano optimo que separa pacientes criticos.',
            'tipo'             => 'clasificacion',
            'kernel'           => 'Lineal',
            'hiperplano'       => '0.6*FC - 0.8*SpO2 + 30 = 0',
            'w1'               => 0.6,
            'w2'               => -0.8,
            'b_svm'            => 30,
            'vectores_soporte' => $vectoresSoporte,
            'tipos_kernel'     => ['Lineal','Polinomial','RBF','Sigmoide'],
            'matriz'           => $matriz,
            'accuracy'         => $this->metrics->accuracy($matriz),
            'precision'        => $this->metrics->precision($matriz),
            'recall'           => $this->metrics->recall($matriz),
            'f1'               => $this->metrics->f1Score($matriz),
            'n_test'           => count($test),
            'ventajas'         => ['Efectivo en alta dimension','Robusto a outliers'],
            'desventajas'      => ['Lento en datasets grandes','Dificil interpretacion'],
        ];
    }

    public function modelo5_RegresionLineal(array $train, array $test): array
    {
        // Regresion multiple: calcular coeficientes manualmente (OLS simplificado)
        // Y = b0 + b1*FC + b2*Temp + b3*Edad
        $n = count($train);
        $sumFC=$sumT=$sumE=$sumY=$sumFC2=$sumT2=$sumE2=$sumFCY=$sumTY=$sumEY=0;
        foreach($train as $p){
            $sumFC+=$p['fc']; $sumT+=$p['temp']; $sumE+=$p['edad']; $sumY+=$p['spo2'];
            $sumFC2+=$p['fc']*$p['fc']; $sumT2+=$p['temp']*$p['temp']; $sumE2+=$p['edad']*$p['edad'];
            $sumFCY+=$p['fc']*$p['spo2']; $sumTY+=$p['temp']*$p['spo2']; $sumEY+=$p['edad']*$p['spo2'];
        }
        $mFC  = $sumFC/$n; $mT=$sumT/$n; $mE=$sumE/$n; $mY=$sumY/$n;
        $b1   = $sumFC2-$n*$mFC*$mFC > 0 ? round(($sumFCY-$n*$mFC*$mY)/($sumFC2-$n*$mFC*$mFC),4) : -0.21;
        $b2   = $sumT2-$n*$mT*$mT   > 0 ? round(($sumTY-$n*$mT*$mY)/($sumT2-$n*$mT*$mT),4)    : -0.15;
        $b3   = $sumE2-$n*$mE*$mE   > 0 ? round(($sumEY-$n*$mE*$mY)/($sumE2-$n*$mE*$mE),4)    : -0.05;
        $b0   = round($mY - $b1*$mFC - $b2*$mT - $b3*$mE, 4);
        $m    = $b1; $b = $b0;

        $realesArr = [];
        $predArr   = [];
        $tabla     = [];

        foreach ($test as $p) {
            $pred        = round($b0 + ($b1*$p['fc']) + ($b2*$p['temp']) + ($b3*$p['edad']), 1);
            $realesArr[] = $p['spo2'];
            $predArr[]   = $pred;
            $tabla[]     = [
                'nombre'    => $p['nombre'],
                'fc'        => $p['fc'],
                'spo2_real' => $p['spo2'],
                'spo2_pred' => $pred,
                'error'     => round(abs($p['spo2'] - $pred), 2),
            ];
        }

        $mse  = $this->regression->mse($realesArr, $predArr);
        $rmse = $this->regression->rmse($realesArr, $predArr);
        $mae  = $this->regression->mae($realesArr, $predArr);

        $yMedia = count($realesArr) > 0 ? array_sum($realesArr) / count($realesArr) : 0;
        $ssTot  = array_sum(array_map(fn($y) => pow($y - $yMedia, 2), $realesArr));
        $ssRes  = array_sum(array_map(fn($y, $yh) => pow($y - $yh, 2), $realesArr, $predArr));
        $r2     = $ssTot > 0 ? round(1 - ($ssRes / $ssTot), 4) : 0;

        return [
            'nombre'         => 'Regresion Lineal',
            'icono'          => 'fas fa-chart-bar',
            'color'          => '#8B5CF6',
            'descripcion'    => 'Predice SpO2 esperado en funcion de la FC del paciente.',
            'tipo'           => 'regresion',
            'formula'        => "y = {$b0} + {$b1}*FC + {$b2}*Temp + {$b3}*Edad",
            'pendiente'      => $b1,
            'intercepto'     => $b0,
            'interpretacion' => "Reg. Multiple: FC, Temp y Edad predicen SpO2. b1(FC)={$b1} b2(Temp)={$b2} b3(Edad)={$b3}",
            'mse'            => $mse,
            'rmse'           => $rmse,
            'mae'            => $mae,
            'r2'             => $r2,
            'tabla'          => array_slice($tabla, 0, 8),
            'n_test'         => count($test),
            'ventajas'       => ['Simple e interpretable','Rapido','Predice valores continuos'],
            'desventajas'    => ['Solo relaciones lineales','Sensible a outliers'],
        ];
    }

    public function ejecutarPipeline(): array
    {
        $raw    = $this->getDataset();
        $limpio = $this->limpiarDatos($raw);
        $split  = $this->dividirDatos($limpio);
        $train  = $split['train'];
        $test   = $split['test'];

        if (count($test) < 3) {
            return $this->datosDemo();
        }

        $modelos = [
            $this->modelo1_RegresionLogistica($train, $test),
            $this->modelo2_ArbolDecision($train, $test),
            $this->modelo3_RandomForest($train, $test),
            $this->modelo4_SVM($train, $test),
            $this->modelo5_RegresionLineal($train, $test),
        ];

        $mejorAcc    = 0;
        $mejorNombre = '';
        foreach ($modelos as $m) {
            if ($m['tipo'] === 'clasificacion' && $m['accuracy'] > $mejorAcc) {
                $mejorAcc    = $m['accuracy'];
                $mejorNombre = $m['nombre'];
            }
        }

        return [
            'pipeline' => [
                'raw_total'    => count($raw),
                'limpio_total' => count($limpio),
                'eliminados'   => count($raw) - count($limpio),
                'n_train'      => $split['n_train'],
                'n_test'       => $split['n_test'],
            ],
            'modelos'         => $modelos,
            'mejor_modelo'    => $mejorNombre,
            'mejor_accuracy'  => $mejorAcc,
            'dataset_muestra' => array_slice($limpio, 0, 6),
            'fuente_datos'    => 'BD Real (triages)',
        ];
    }

    private function datosDemo(): array
    {
        return [
            'pipeline' => [
                'raw_total' => 10, 'limpio_total' => 10,
                'eliminados' => 0, 'n_train' => 8, 'n_test' => 2,
            ],
            'modelos' => [
                ['nombre'=>'Regresion Logistica','icono'=>'fas fa-chart-line','color'=>'#6366F1','accuracy'=>80,'precision'=>83,'recall'=>83,'f1'=>83,'tipo'=>'clasificacion','formula'=>'P(critico) = 1/(1+e^-z)','z_formula'=>'z = -8.0 + 0.05*Edad - 0.1*SpO2 + 0.02*FC','descripcion'=>'Predice probabilidad de triage critico.','matriz'=>['TP'=>5,'TN'=>3,'FP'=>1,'FN'=>1,'total'=>10],'coeficientes'=>['B0'=>-8,'B1 Edad'=>0.05,'B2 SpO2'=>-0.1,'B3 FC'=>0.02],'n_test'=>10,'ventajas'=>['Facil interpretacion','Rapido'],'desventajas'=>['Limitado binario']],
                ['nombre'=>'Arbol de Decision','icono'=>'fas fa-sitemap','color'=>'#10B981','accuracy'=>78,'precision'=>80,'recall'=>83,'f1'=>81,'tipo'=>'clasificacion','descripcion'=>'Clasifica por reglas jerarquicas.','gini'=>0.32,'entropia'=>0.72,'formula_gini'=>'Gini = 1-(0.8^2+0.2^2)','formula_entropia'=>'H = -Sum Pi*log2(Pi)','nodo_raiz'=>'SpO2 < 90%','nodos_internos'=>['FC > 120','Temp > 38C'],'hojas'=>['UCI','Obs.','Febril','Estable'],'matriz'=>['TP'=>5,'TN'=>3,'FP'=>1,'FN'=>1,'total'=>10],'n_test'=>10,'ventajas'=>['Visualizable'],'desventajas'=>['Sobreajuste']],
                ['nombre'=>'Random Forest','icono'=>'fas fa-tree','color'=>'#F59E0B','accuracy'=>85,'precision'=>86,'recall'=>83,'f1'=>84,'tipo'=>'clasificacion','descripcion'=>'Ensemble de 3 arboles votando.','n_arboles'=>3,'importancia'=>['SpO2'=>42,'FC'=>31,'Temp'=>18,'Edad'=>9],'matriz'=>['TP'=>5,'TN'=>4,'FP'=>0,'FN'=>1,'total'=>10],'n_test'=>10,'ventajas'=>['Mayor precision'],'desventajas'=>['Mas complejo']],
                ['nombre'=>'SVM','icono'=>'fas fa-bullseye','color'=>'#EF4444','accuracy'=>82,'precision'=>83,'recall'=>83,'f1'=>83,'tipo'=>'clasificacion','descripcion'=>'Hiperplano optimo separando clases.','kernel'=>'Lineal','hiperplano'=>'0.6*FC - 0.8*SpO2 + 30 = 0','tipos_kernel'=>['Lineal','Polinomial','RBF','Sigmoide'],'matriz'=>['TP'=>5,'TN'=>3,'FP'=>1,'FN'=>1,'total'=>10],'n_test'=>10,'ventajas'=>['Robusto a outliers'],'desventajas'=>['Lento en grandes datasets']],
                ['nombre'=>'Regresion Lineal','icono'=>'fas fa-chart-bar','color'=>'#8B5CF6','tipo'=>'regresion','descripcion'=>'Predice SpO2 en funcion de FC.','formula'=>'y = -0.05x + 102.5','pendiente'=>-0.05,'intercepto'=>102.5,'mse'=>9.25,'rmse'=>3.04,'mae'=>2.75,'r2'=>0.68,'tabla'=>[['nombre'=>'Paciente A','fc'=>80,'spo2_real'=>98,'spo2_pred'=>98.5,'error'=>0.5],['nombre'=>'Paciente B','fc'=>110,'spo2_real'=>92,'spo2_pred'=>93.0,'error'=>1.0]],'n_test'=>10,'ventajas'=>['Simple'],'desventajas'=>['Solo lineal']],
            ],
            'mejor_modelo'    => 'Random Forest',
            'mejor_accuracy'  => 85,
            'dataset_muestra' => [],
            'fuente_datos'    => 'Datos Demo',
        ];
    }
}
