<?php

namespace App\Services\ML;

class ClusteringService
{
    private int $maxIter = 100;
    private int $runs = 10; // Multiple runs to avoid bad initialization

    /**
     * Ejecuta K-Means++ sobre una matriz de datos
     * @param array $data [[val1, val2, ...], ...]
     * @param int $k
     * @return array ['labels' => [], 'centroids' => [], 'inertia' => float, 'n_iter' => int]
     */
    public function kmeans(array $data, int $k): array
    {
        if (count($data) < $k) {
            $k = count($data);
        }

        $bestResult = null;
        $bestInertia = PHP_FLOAT_MAX;

        // Multiple runs to find best initialization
        for ($run = 0; $run < $this->runs; $run++) {
            $centroids = $this->kmeansPlusPlus($data, $k);
            $labels = array_fill(0, count($data), 0);

            for ($iter = 0; $iter < $this->maxIter; $iter++) {
                // Assign each point to nearest centroid
                $newLabels = $this->assignClusters($data, $centroids);

                // Check convergence
                if ($newLabels === $labels) {
                    break;
                }
                $labels = $newLabels;

                // Recalculate centroids
                $centroids = $this->recalculateCentroids($data, $labels, $k);
            }

            $inertia = $this->calculateInertia($data, $labels, $centroids);

            if ($inertia < $bestInertia) {
                $bestInertia = $inertia;
                $bestResult = [
                    'labels' => $labels,
                    'centroids' => $centroids,
                    'inertia' => $inertia,
                    'n_iter' => $iter,
                ];
            }
        }

        return $bestResult;
    }

    /**
     * Calculate inertia (sum of squared distances to assigned centroid)
     */
    private function calculateInertia(array $data, array $labels, array $centroids): float
    {
        $inertia = 0.0;
        $nFeatures = count($data[0]);
        foreach ($data as $i => $point) {
            $c = $centroids[$labels[$i]];
            $dist = 0.0;
            for ($j = 0; $j < $nFeatures; $j++) {
                $dist += ($point[$j] - $c[$j]) ** 2;
            }
            $inertia += $dist;
        }
        return $inertia;
    }

    /**
     * K-Means++ initialization for better centroid selection
     */
    private function kmeansPlusPlus(array $data, int $k): array
    {
        $n = count($data);
        $centroids = [];

        // Pick first centroid randomly
        $centroids[] = $data[array_rand($data)];

        for ($c = 1; $c < $k; $c++) {
            // Calculate distance from each point to nearest centroid
            $distances = [];
            $totalDist = 0.0;

            foreach ($data as $point) {
                $minDist = PHP_FLOAT_MAX;
                foreach ($centroids as $centroid) {
                    $dist = $this->euclideanDistance($point, $centroid);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                    }
                }
                $distances[] = $minDist ** 2; // Square distance for probability
                $totalDist += $minDist ** 2;
            }

            // Weighted random selection
            $rand = mt_rand() / mt_getrandmax() * $totalDist;
            $cumulative = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $cumulative += $distances[$i];
                if ($cumulative >= $rand) {
                    $centroids[] = $data[$i];
                    break;
                }
            }
        }

        return $centroids;
    }

    /**
     * Assign each data point to the nearest centroid
     */
    private function assignClusters(array $data, array $centroids): array
    {
        $labels = [];
        foreach ($data as $point) {
            $minDist = PHP_FLOAT_MAX;
            $bestCluster = 0;
            foreach ($centroids as $idx => $centroid) {
                $dist = $this->euclideanDistance($point, $centroid);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $bestCluster = $idx;
                }
            }
            $labels[] = $bestCluster;
        }
        return $labels;
    }

    /**
     * Recalculate centroids as mean of assigned points
     */
    private function recalculateCentroids(array $data, array $labels, int $k): array
    {
        $nFeatures = count($data[0]);
        $sums = array_fill(0, $k, array_fill(0, $nFeatures, 0.0));
        $counts = array_fill(0, $k, 0);

        foreach ($data as $i => $point) {
            $cluster = $labels[$i];
            $counts[$cluster]++;
            for ($j = 0; $j < $nFeatures; $j++) {
                $sums[$cluster][$j] += $point[$j];
            }
        }

        $centroids = [];
        for ($c = 0; $c < $k; $c++) {
            if ($counts[$c] > 0) {
                $centroid = [];
                for ($j = 0; $j < $nFeatures; $j++) {
                    $centroid[] = $sums[$c][$j] / $counts[$c];
                }
                $centroids[] = $centroid;
            } else {
                // Empty cluster: reinitialize randomly
                $centroids[] = $data[array_rand($data)];
            }
        }

        return $centroids;
    }

    /**
     * Euclidean distance between two vectors
     */
    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;
        $n = count($a);
        for ($i = 0; $i < $n; $i++) {
            $sum += ($a[$i] - $b[$i]) ** 2;
        }
        return sqrt($sum);
    }

    /**
     * Calculate Silhouette Score for cluster quality
     */
    public function silhouetteScore(array $data, array $labels): float
    {
        $n = count($data);
        if ($n < 2) return 0.0;

        $uniqueLabels = array_unique($labels);
        if (count($uniqueLabels) < 2) return 0.0;

        $totalSilhouette = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $cluster = $labels[$i];

            // a(i): Average distance to points in same cluster
            $sameCluster = [];
            foreach ($labels as $j => $l) {
                if ($j !== $i && $l === $cluster) {
                    $sameCluster[] = $j;
                }
            }

            if (empty($sameCluster)) {
                $a = 0.0;
            } else {
                $a = 0.0;
                foreach ($sameCluster as $j) {
                    $a += $this->euclideanDistance($data[$i], $data[$j]);
                }
                $a /= count($sameCluster);
            }

            // b(i): Minimum average distance to points in other clusters
            $b = PHP_FLOAT_MAX;
            foreach ($uniqueLabels as $otherCluster) {
                if ($otherCluster === $cluster) continue;

                $otherPoints = [];
                foreach ($labels as $j => $l) {
                    if ($l === $otherCluster) {
                        $otherPoints[] = $j;
                    }
                }

                if (!empty($otherPoints)) {
                    $avgDist = 0.0;
                    foreach ($otherPoints as $j) {
                        $avgDist += $this->euclideanDistance($data[$i], $data[$j]);
                    }
                    $avgDist /= count($otherPoints);

                    if ($avgDist < $b) {
                        $b = $avgDist;
                    }
                }
            }

            // Silhouette for point i
            $denom = max($a, $b);
            $s = ($denom === 0.0) ? 0.0 : ($b - $a) / $denom;
            $totalSilhouette += $s;
        }

        return $totalSilhouette / $n;
    }

    /**
     * Elbow method: run K-Means for multiple K values
     */
    public function elbowMethod(array $data, int $maxK = 8): array
    {
        $results = [];
        $minK = 2;

        for ($k = $minK; $k <= min($maxK, count($data) - 1); $k++) {
            $result = $this->kmeans($data, $k);
            $silhouette = $this->silhouetteScore($data, $result['labels']);
            $results[] = [
                'k' => $k,
                'inertia' => $result['inertia'],
                'silhouette' => $silhouette,
            ];
        }

        return $results;
    }
}
