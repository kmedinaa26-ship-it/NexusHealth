<?php

namespace App\Services\ML;

class PcaService
{
    public function fit(array $data, int $nComponents = 3): array
    {
        $nSamples = count($data);
        $nFeatures = count($data[0]);

        if ($nSamples < 2 || $nFeatures < 2) {
            return ['error' => 'Insuficientes datos para PCA'];
        }

        $standardized = $this->standardize($data);
        $covMatrix = $this->covarianceMatrix($standardized);
        $eigen = $this->eigenDecomposition($covMatrix);

        arsort($eigen['values']);
        $sortedIndices = array_keys($eigen['values']);
        $sortedValues = array_values($eigen['values']);

        $actualComponents = min($nComponents, $nFeatures);
        $components = [];
        for ($i = 0; $i < $actualComponents; $i++) {
            $components[] = $eigen['vectors'][$sortedIndices[$i]];
        }

        $projected = $this->project($standardized, $components);

        // CALCULAR VARIANZA DESDE LOS DATOS PROYECTADOS (más robusto)
        $variances = [];
        for ($i = 0; $i < $actualComponents; $i++) {
            $col = array_column($projected, $i);
            $mean = array_sum($col) / $nSamples;
            $sumSq = 0;
            foreach ($col as $val) {
                $sumSq += ($val - $mean) ** 2;
            }
            $variances[] = $sumSq / max($nSamples - 1, 1);
        }
        $totalVar = array_sum($variances);
        $explainedVariance = [];
        $cumulativeVariance = [];
        $cumSum = 0;
        foreach ($variances as $var) {
            $pct = $totalVar > 0 ? ($var / $totalVar) * 100 : 0;
            $explainedVariance[] = round($pct, 2);
            $cumSum += $pct;
            $cumulativeVariance[] = round($cumSum, 2);
        }

        $loadings = $this->calculateLoadings($components);

        return [
            'components' => $components,
            'projected' => $projected,
            'explained_variance_pct' => $explainedVariance,
            'cumulative_variance_pct' => $cumulativeVariance,
            'eigenvalues' => array_map(fn($v) => round($v, 6), $sortedValues),
            'loadings' => $loadings,
            'n_samples' => $nSamples,
            'n_features' => $nFeatures,
        ];
    }

    private function standardize(array $data): array
    {
        $nFeatures = count($data[0]);
        $stds = $this->getStdDevs($data);
        $means = $this->getMeans($data);
        $standardized = [];
        foreach ($data as $row) {
            $newRow = [];
            for ($j = 0; $j < $nFeatures; $j++) {
                $std = $stds[$j] ?? 1e-8;
                $newRow[] = ($row[$j] - $means[$j]) / $std;
            }
            $standardized[] = $newRow;
        }
        return $standardized;
    }

    private function getMeans(array $data): array
    {
        $nFeatures = count($data[0]);
        $n = count($data);
        $means = array_fill(0, $nFeatures, 0.0);
        foreach ($data as $row) {
            for ($j = 0; $j < $nFeatures; $j++) {
                $means[$j] += $row[$j];
            }
        }
        return array_map(fn($m) => $m / $n, $means);
    }

    private function getStdDevs(array $data): array
    {
        $nFeatures = count($data[0]);
        $n = count($data);
        $means = $this->getMeans($data);
        $vars = array_fill(0, $nFeatures, 0.0);
        foreach ($data as $row) {
            for ($j = 0; $j < $nFeatures; $j++) {
                $vars[$j] += ($row[$j] - $means[$j]) ** 2;
            }
        }
        return array_map(fn($v) => sqrt($v / max($n - 1, 1)), $vars);
    }

    private function covarianceMatrix(array $stdData): array
    {
        $n = count($stdData);
        $p = count($stdData[0]);
        $cov = [];
        for ($i = 0; $i < $p; $i++) {
            $cov[$i] = [];
            for ($j = 0; $j < $p; $j++) {
                $sum = 0.0;
                for ($k = 0; $k < $n; $k++) {
                    $sum += $stdData[$k][$i] * $stdData[$k][$j];
                }
                $cov[$i][$j] = $sum / max($n - 1, 1);
            }
        }
        return $cov;
    }

    private function eigenDecomposition(array $matrix): array
    {
        $n = count($matrix);
        $A = $matrix;
        $Q = $this->identityMatrix($n);
        $maxIter = 300;
        $tolerance = 1e-10;

        for ($iter = 0; $iter < $maxIter; $iter++) {
            [$q, $r] = $this->qrDecompose($A);
            $A = $this->matMul($r, $q);
            $Q = $this->matMul($Q, $q);
            $offDiag = 0.0;
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    if ($i !== $j) $offDiag += abs($A[$i][$j]);
                }
            }
            if ($offDiag < $tolerance) break;
        }

        $values = [];
        $vectors = [];
        for ($i = 0; $i < $n; $i++) {
            $values[$i] = $A[$i][$i];
            $vectors[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $vectors[$i][] = $Q[$j][$i];
            }
        }
        return ['values' => $values, 'vectors' => $vectors];
    }

    private function qrDecompose(array $A): array
    {
        $n = count($A);
        $Q = [];
        $R = array_fill(0, $n, array_fill(0, $n, 0.0));
        $cols = [];
        for ($j = 0; $j < $n; $j++) {
            $cols[$j] = [];
            for ($i = 0; $i < $n; $i++) {
                $cols[$j][] = $A[$i][$j];
            }
        }
        for ($j = 0; $j < $n; $j++) {
            $v = $cols[$j];
            for ($i = 0; $i < $j; $i++) {
                $dot = $this->dotProduct($cols[$j], $Q[$i]);
                $R[$i][$j] = $dot;
                $v = $this->vecSub($v, $this->vecScale($Q[$i], $dot));
            }
            $norm = $this->vecNorm($v);
            $R[$j][$j] = $norm;
            if ($norm < 1e-10) {
                $Q[$j] = array_fill(0, $n, 0.0);
                $Q[$j][$j] = 1.0;
            } else {
                $Q[$j] = $this->vecScale($v, 1.0 / $norm);
            }
        }
        return [$Q, $R];
    }

    private function project(array $stdData, array $components): array
    {
        $projected = [];
        $nComp = count($components);
        foreach ($stdData as $row) {
            $point = [];
            for ($c = 0; $c < $nComp; $c++) {
                $val = 0.0;
                for ($j = 0; $j < count($row); $j++) {
                    $val += $row[$j] * $components[$c][$j];
                }
                $point[] = round($val, 4);
            }
            $projected[] = $point;
        }
        return $projected;
    }

    private function calculateLoadings(array $components): array
    {
        $loadings = [];
        foreach ($components as $comp) {
            $loadings[] = array_map(fn($v) => round($v, 4), $comp);
        }
        return $loadings;
    }

    private function identityMatrix(int $n): array
    {
        $I = [];
        for ($i = 0; $i < $n; $i++) {
            $I[$i] = array_fill(0, $n, 0.0);
            $I[$i][$i] = 1.0;
        }
        return $I;
    }

    private function matMul(array $A, array $B): array
    {
        $n = count($A);
        $m = count($B[0]);
        $p = count($B);
        $C = array_fill(0, $n, array_fill(0, $m, 0.0));
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $m; $j++) {
                for ($k = 0; $k < $p; $k++) {
                    $C[$i][$j] += $A[$i][$k] * $B[$k][$j];
                }
            }
        }
        return $C;
    }

    private function dotProduct(array $a, array $b): float
    {
        $sum = 0.0;
        $n = min(count($a), count($b));
        for ($i = 0; $i < $n; $i++) $sum += $a[$i] * $b[$i];
        return $sum;
    }

    private function vecSub(array $a, array $b): array
    {
        $result = [];
        $n = min(count($a), count($b));
        for ($i = 0; $i < $n; $i++) $result[] = $a[$i] - $b[$i];
        return $result;
    }

    private function vecScale(array $v, float $s): array
    {
        return array_map(fn($x) => $x * $s, $v);
    }

    private function vecNorm(array $v): float
    {
        return sqrt($this->dotProduct($v, $v));
    }
}
