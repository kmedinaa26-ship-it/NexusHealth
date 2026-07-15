<?php

namespace App\Services\ML;

class MetricsService
{
    public function confusionMatrix($predicciones, $reales)
    {
        $tp = $tn = $fp = $fn = 0;

        for ($i = 0; $i < count($predicciones); $i++) {
            if (isset($reales[$i]) && $reales[$i] == 1 && $predicciones[$i] == 1) $tp++;
            if (isset($reales[$i]) && $reales[$i] == 0 && $predicciones[$i] == 0) $tn++;
            if (isset($reales[$i]) && $reales[$i] == 0 && $predicciones[$i] == 1) $fp++;
            if (isset($reales[$i]) && $reales[$i] == 1 && $predicciones[$i] == 0) $fn++;
        }

        return [
            'TP' => $tp, 'TN' => $tn, 'FP' => $fp, 'FN' => $fn,
            'total' => $tp + $tn + $fp + $fn,
        ];
    }

    public function accuracy($matriz): float
    {
        $total = $matriz['total'];
        return $total == 0 ? 0 : round((($matriz['TP'] + $matriz['TN']) / $total) * 100, 2);
    }

    public function precision($matriz): float
    {
        $denom = $matriz['TP'] + $matriz['FP'];
        return $denom == 0 ? 0 : round(($matriz['TP'] / $denom) * 100, 2);
    }

    public function recall($matriz): float
    {
        $denom = $matriz['TP'] + $matriz['FN'];
        return $denom == 0 ? 0 : round(($matriz['TP'] / $denom) * 100, 2);
    }

    public function f1Score($matriz): float
    {
        $p = $this->precision($matriz);
        $r = $this->recall($matriz);
        return ($p + $r) == 0 ? 0 : round(2 * ($p * $r) / ($p + $r), 2);
    }
}
