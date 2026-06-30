<?php

namespace App\Services\ML;

class RegressionService
{
    public function linearRegression(array $x, array $y): array
    {
        $n = count($x);
        if ($n == 0) return ['formula' => 'y = 0x + 0', 'predicciones' => []];

        $xMedia = array_sum($x) / $n;
        $yMedia = array_sum($y) / $n;

        $numerador = 0;
        $denominador = 0;

        for ($i = 0; $i < $n; $i++) {
            $numerador += ($x[$i] - $xMedia) * ($y[$i] - $yMedia);
            $denominador += pow($x[$i] - $xMedia, 2);
        }

        $m = $denominador != 0 ? $numerador / $denominador : 0;
        $b = $yMedia - ($m * $xMedia);

        $predicciones = [];
        foreach ($x as $xVal) {
            $predicciones[] = ['x' => $xVal, 'y' => ($m * $xVal) + $b];
        }

        return [
            'pendiente' => round($m, 2),
            'intercepto' => round($b, 2),
            'formula' => "y = " . round($m, 2) . "x + " . round($b, 2),
            'predicciones' => $predicciones
        ];
    }

    public function mse(array $reales, array $predichos): float
    {
        $n = count($reales);
        if ($n == 0) return 0;
        $suma = 0;
        for ($i = 0; $i < $n; $i++) {
            $suma += pow($reales[$i] - $predichos[$i], 2);
        }
        return round($suma / $n, 2);
    }

    public function rmse(array $reales, array $predichos): float
    {
        return round(sqrt($this->mse($reales, $predichos)), 2);
    }

    public function mae(array $reales, array $predichos): float
    {
        $n = count($reales);
        if ($n == 0) return 0;
        $suma = 0;
        for ($i = 0; $i < $n; $i++) {
            $suma += abs($reales[$i] - $predichos[$i]);
        }
        return round($suma / $n, 2);
    }
}
