<?php

namespace App\Services\ML;

class ClassificationService
{
    public function decisionTree($paciente)
    {
        if ($paciente['spo2'] < 90) {
            if ($paciente['frecuencia_cardiaca'] > 120) {
                return ['nivel' => 1, 'label' => 'Crítico'];
            } else {
                return ['nivel' => 2, 'label' => 'Emergencia'];
            }
        } else {
            if ($paciente['temperatura'] > 39) {
                return ['nivel' => 3, 'label' => 'Urgente'];
            } else {
                return ['nivel' => 4, 'label' => 'Menos Urgente'];
            }
        }
    }

    public function randomForest($paciente)
    {
        $predicciones = [];
        for ($i = 0; $i < 10; $i++) {
            $predicciones[] = $this->decisionTree($paciente)['nivel'];
        }
        $votos = array_count_values($predicciones);
        arsort($votos);
        
        return [
            'nivel' => array_key_first($votos),
            'confianza' => round((reset($votos) / 10) * 100, 2) . '%'
        ];
    }

    public function svm($paciente)
    {
        $w1 = 0.6; $w2 = -0.8; $b = 30;
        $decision = ($w1 * $paciente['frecuencia_cardiaca']) + ($w2 * $paciente['spo2']) + $b;
        
        return [
            'clase' => $decision > 0 ? 'ALTO RIESGO' : 'BAJO RIESGO',
            'margen' => abs(round($decision, 2))
        ];
    }
}
