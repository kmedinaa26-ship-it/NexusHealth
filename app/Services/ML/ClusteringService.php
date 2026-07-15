<?php

namespace App\Services\ML;

class ClusteringService
{
    private int $maxIter = 100;
    private int $runs = 10;

    public function kmeans(array $data, int $k): array
    {
        if (count($data) < $k) $k = count($data);
        $bestResult = null;
        $bestInertia = PHP_FLOAT_MAX;
        $bestSteps = null;

        for ($run = 0; $run < $this->runs; $run++) {
            [$centroids, $initSteps] = $this->kmeansPlusPlus($data, $k);
            $labels = array_fill(0, count($data), 0);
            $iterations = [];

            for ($iter = 0; $iter < $this->maxIter; $iter++) {
                [$newLabels, $distMatrix] = $this->assignClusters($data, $centroids);
                if ($newLabels === $labels) { $iterations[] = ['iter'=>$iter,'distances'=>$distMatrix,'assignments'=>$newLabels,'centroids_before'=>$centroids,'centroids_after'=>$centroids,'converged'=>true]; break; }
                $labels = $newLabels;
                $oldCentroids = $centroids;
                $centroids = $this->recalculateCentroids($data, $labels, $k);
                $iterations[] = ['iter'=>$iter,'distances'=>$distMatrix,'assignments'=>$labels,'centroids_before'=>$oldCentroids,'centroids_after'=>$centroids,'converged'=>false];
            }
            $inertia = $this->calculateInertia($data, $labels, $centroids);
            if ($inertia < $bestInertia) {
                $bestInertia = $inertia;
                $bestSteps = ['init'=>$initSteps,'iterations'=>$iterations];
                $bestResult = ['labels'=>$labels,'centroids'=>$centroids,'inertia'=>$inertia,'n_iter'=>$iter];
            }
        }
        $bestResult['steps'] = $bestSteps;
        return $bestResult;
    }

    private function calculateInertia(array $data, array $labels, array $centroids): float
    {
        $inertia = 0.0; $nF = count($data[0]);
        foreach ($data as $i => $point) {
            $c = $centroids[$labels[$i]]; $d = 0.0;
            for ($j = 0; $j < $nF; $j++) $d += ($point[$j] - $c[$j]) ** 2;
            $inertia += $d;
        }
        return $inertia;
    }

    private function kmeansPlusPlus(array $data, int $k): array
    {
        $n = count($data); $centroids = []; $initSteps = [];
        $firstIdx = array_rand($data);
        $centroids[] = $data[$firstIdx];
        $initSteps[] = ['step'=>1,'type'=>'random','point_idx'=>$firstIdx,'coords'=>$data[$firstIdx],'note'=>'Centroide inicial aleatorio'];

        for ($c = 1; $c < $k; $c++) {
            $distances = []; $totalDist = 0.0;
            foreach ($data as $point) {
                $minDist = PHP_FLOAT_MAX;
                foreach ($centroids as $centroid) { $d = $this->euclideanDistance($point, $centroid); if ($d < $minDist) $minDist = $d; }
                $sq = $minDist ** 2; $distances[] = $sq; $totalDist += $sq;
            }
            $rand = mt_rand() / mt_getrandmax() * $totalDist; $cumulative = 0.0; $chosen = 0;
            for ($i = 0; $i < $n; $i++) { $cumulative += $distances[$i]; if ($cumulative >= $rand) { $chosen = $i; break; } }
            $centroids[] = $data[$chosen];
            $initSteps[] = ['step'=>$c+1,'type'=>'kmeans++','point_idx'=>$chosen,'coords'=>$data[$chosen],'total_dist_sq'=>round($totalDist,4),'chosen_prob'=>round($distances[$chosen]/$totalDist*100,2),'note'=>'Seleccionado con probabilidad proporcional a d²'];
        }
        return [$centroids, $initSteps];
    }

    private function assignClusters(array $data, array $centroids): array
    {
        $labels = []; $distMatrix = [];
        foreach ($data as $point) {
            $dists = []; $minDist = PHP_FLOAT_MAX; $best = 0;
            foreach ($centroids as $idx => $centroid) { $d = $this->euclideanDistance($point, $centroid); $dists[] = round($d, 4); if ($d < $minDist) { $minDist = $d; $best = $idx; } }
            $labels[] = $best; $distMatrix[] = $dists;
        }
        return [$labels, $distMatrix];
    }

    private function recalculateCentroids(array $data, array $labels, int $k): array
    {
        $nF = count($data[0]);
        $sums = array_fill(0, $k, array_fill(0, $nF, 0.0));
        $counts = array_fill(0, $k, 0);
        foreach ($data as $i => $point) { $cl = $labels[$i]; $counts[$cl]++; for ($j = 0; $j < $nF; $j++) $sums[$cl][$j] += $point[$j]; }
        $centroids = [];
        for ($c = 0; $c < $k; $c++) {
            if ($counts[$c] > 0) { $ct = []; for ($j = 0; $j < $nF; $j++) $ct[] = round($sums[$c][$j] / $counts[$c], 4); $centroids[] = $ct; }
            else $centroids[] = $data[array_rand($data)];
        }
        return $centroids;
    }

    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0; $n = count($a);
        for ($i = 0; $i < $n; $i++) $sum += ($a[$i] - $b[$i]) ** 2;
        return sqrt($sum);
    }

    public function euclideanDistance2D(float $x1, float $y1, float $x2, float $y2): array
    {
        $dx = $x2 - $x1; $dy = $y2 - $y1;
        $dist = sqrt($dx**2 + $dy**2);
        return ['dx'=>round($dx,4),'dy'=>round($dy,4),'dx2'=>round($dx**2,4),'dy2'=>round($dy**2,4),'sum'=>round($dx**2+$dy**2,4),'sqrt'=>round($dist,4),'formula'=>"d = √(($x2-$x1)² + ($y2-$y1)²) = √($dx² + $dy²) = √".round($dx**2+$dy**2,4)." = ".round($dist,4)];
    }

    public function silhouetteScore(array $data, array $labels): float
    {
        $n = count($data); if ($n < 2) return 0.0;
        $uniqueLabels = array_unique($labels); if (count($uniqueLabels) < 2) return 0.0;
        $total = 0.0; $details = [];
        for ($i = 0; $i < $n; $i++) {
            $cluster = $labels[$i]; $sameCluster = [];
            foreach ($labels as $j => $l) { if ($j !== $i && $l === $cluster) $sameCluster[] = $j; }
            $a = 0.0;
            if (!empty($sameCluster)) { foreach ($sameCluster as $j) $a += $this->euclideanDistance($data[$i], $data[$j]); $a /= count($sameCluster); }
            $b = PHP_FLOAT_MAX; $bCluster = -1;
            foreach ($uniqueLabels as $otherCluster) {
                if ($otherCluster === $cluster) continue;
                $otherPoints = []; foreach ($labels as $j => $l) { if ($l === $otherCluster) $otherPoints[] = $j; }
                if (!empty($otherPoints)) { $avg = 0.0; foreach ($otherPoints as $j) $avg += $this->euclideanDistance($data[$i], $data[$j]); $avg /= count($otherPoints); if ($avg < $b) { $b = $avg; $bCluster = $otherCluster; } }
            }
            $denom = max($a, $b); $s = ($denom === 0.0) ? 0.0 : ($b - $a) / $denom;
            $details[] = ['point'=>$i,'cluster'=>$cluster,'a_i'=>round($a,4),'b_i'=>round($b,4),'b_cluster'=>$bCluster,'s_i'=>round($s,4),'n_same'=>count($sameCluster),'formula'=>"s($i) = (b($i) - a($i)) / max(a($i),b($i)) = (".round($b,2)." - ".round($a,2).") / ".round($denom,2)." = ".round($s,4)];
            $total += $s;
        }
        return $total / $n;
    }

    public function silhouetteDetails(array $data, array $labels): array
    {
        $n = count($data); $uniqueLabels = array_unique($labels); $details = [];
        for ($i = 0; $i < $n; $i++) {
            $cluster = $labels[$i]; $sameCluster = [];
            foreach ($labels as $j => $l) { if ($j !== $i && $l === $cluster) $sameCluster[] = $j; }
            $a = 0.0; $aDists = [];
            if (!empty($sameCluster)) { foreach ($sameCluster as $j) { $d = $this->euclideanDistance($data[$i], $data[$j]); $aDists[] = ['j'=>$j,'d'=>round($d,4)]; $a += $d; } $a /= count($sameCluster); }
            $b = PHP_FLOAT_MAX; $bCluster = -1; $bDists = [];
            foreach ($uniqueLabels as $oc) {
                if ($oc === $cluster) continue;
                $ops = []; foreach ($labels as $j => $l) { if ($l === $oc) $ops[] = $j; }
                if (!empty($ops)) { $avg = 0.0; $ods = []; foreach ($ops as $j) { $d = $this->euclideanDistance($data[$i], $data[$j]); $ods[] = ['j'=>$j,'d'=>round($d,4)]; $avg += $d; } $avg /= count($ops); if ($avg < $b) { $b = $avg; $bCluster = $oc; $bDists = $ods; } }
            }
            $denom = max($a, $b); $s = ($denom === 0.0) ? 0.0 : ($b - $a) / $denom;
            $details[] = ['point'=>$i,'cluster'=>$cluster,'a_i'=>round($a,4),'a_dists'=>$aDists,'b_i'=>round($b,4),'b_cluster'=>$bCluster,'b_dists'=>$bDists,'s_i'=>round($s,4),'denom'=>round($denom,4),'n_same'=>count($sameCluster)];
        }
        return $details;
    }

    public function elbowMethod(array $data, int $maxK = 8): array
    {
        $results = [];
        for ($k = 2; $k <= min($maxK, count($data) - 1); $k++) {
            $result = $this->kmeans($data, $k);
            $sil = $this->silhouetteScore($data, $result['labels']);
            $results[] = ['k'=>$k,'inertia'=>$result['inertia'],'silhouette'=>$sil];
        }
        return $results;
    }
}
