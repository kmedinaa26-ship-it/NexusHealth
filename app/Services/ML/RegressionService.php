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

    public function multipleRegression(array $x1, array $x2, array $x3, array $y)
    {
        $n = count($y);
        $xtx = $this->mz(4,4);
        $xty = $this->mz(4,1);
        for ($i = 0; $i < $n; $i++) {
            $row = [1, $x1[$i], $x2[$i], $x3[$i]];
            for ($j = 0; $j < 4; $j++) {
                $xty[$j][0] += $row[$j] * $y[$i];
                for ($k = 0; $k < 4; $k++) {
                    $xtx[$j][$k] += $row[$j] * $row[$k];
                }
            }
        }
        $xtxI = $this->inv4($xtx);
        $beta = $this->mz(4,1);
        for ($i = 0; $i < 4; $i++)
            for ($j = 0; $j < 4; $j++)
                $beta[$i][0] += $xtxI[$i][$j] * $xty[$j][0];

        $preds = []; $ssR = 0; $ssT = 0;
        $ym = array_sum($y) / $n;
        for ($i = 0; $i < $n; $i++) {
            $yh = $beta[0][0] + $beta[1][0]*$x1[$i] + $beta[2][0]*$x2[$i] + $beta[3][0]*$x3[$i];
            $preds[] = $yh;
            $ssR += pow($y[$i] - $yh, 2);
            $ssT += pow($y[$i] - $ym, 2);
        }
        return [
            'beta' => ['b0'=>$beta[0][0],'b1'=>$beta[1][0],'b2'=>$beta[2][0],'b3'=>$beta[3][0]],
            'predictions' => $preds,
            'r2' => $ssT > 0 ? 1 - ($ssR/$ssT) : 0,
            'mse' => $this->mse($y, $preds),
            'rmse' => $this->rmse($y, $preds),
            'mae' => $this->mae($y, $preds),
            'n' => $n,
        ];
    }

    private function inv4($m)
    {
        $n = 4; $a = [];
        for ($i = 0; $i < $n; $i++)
            for ($j = 0; $j < $n; $j++) {
                $a[$i][$j] = $m[$i][$j];
                $a[$i][$n+$j] = ($i == $j) ? 1 : 0;
            }
        for ($c = 0; $c < $n; $c++) {
            $mr = $c;
            for ($r = $c+1; $r < $n; $r++)
                if (abs($a[$r][$c]) > abs($a[$mr][$c])) $mr = $r;
            $t = $a[$c]; $a[$c] = $a[$mr]; $a[$mr] = $t;
            if (abs($a[$c][$c]) < 1e-10) { $z = $this->mz($n,$n); for ($i=0;$i<$n;$i++) $z[$i][$i]=1; return $z; }
            $p = $a[$c][$c];
            for ($j = 0; $j < 2*$n; $j++) $a[$c][$j] /= $p;
            for ($r = 0; $r < $n; $r++) {
                if ($r != $c) { $f = $a[$r][$c]; for ($j=0;$j<2*$n;$j++) $a[$r][$j] -= $f*$a[$c][$j]; }
            }
        }
        $inv = [];
        for ($i = 0; $i < $n; $i++) for ($j = 0; $j < $n; $j++) $inv[$i][$j] = $a[$i][$n+$j];
        return $inv;
    }

    private function mz($r, $c)
    {
        $m = [];
        for ($i = 0; $i < $r; $i++) for ($j = 0; $j < $c; $j++) $m[$i][$j] = 0.0;
        return $m;
    }
}
