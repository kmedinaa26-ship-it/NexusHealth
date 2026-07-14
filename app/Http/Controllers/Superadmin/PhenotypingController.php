<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\ML\ClusteringService;
use App\Services\ML\PcaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Http\Request;

class PhenotypingController extends Controller
{
    public function index()
    {
        $totalPacientes = DB::table("triages")->count();
        $conVitales = DB::table("vital_signs")->distinct()->count("triage_id");
        $totalVitales = DB::table("vital_signs")->count();
        $totalRecetas = DB::table("prescriptions")->count();
        $totalEstudios = DB::table("medical_studies")->count();
        $altas = DB::table("triages")->where("status", "Dado de Alta")->count();
        $lastRun = session("phenotyping_last_run");

        $totalAdmisiones = $altas;
        $totalLabs = 0;
        $totalImg = 0;
        $newAdmisions = DB::table("triages")->whereDate("created_at", today())->count();
        $newVitals = DB::table("vital_signs")->whereDate("created_at", today())->count();

        return view("superadmin.phenotyping.index", compact(
            "totalPacientes", "conVitales", "totalVitales", "totalRecetas",
            "totalEstudios", "altas", "lastRun",
            "totalAdmisiones", "totalLabs", "totalImg", "newAdmisions", "newVitals"
        ));
    }

    public function runKmeans(Request $request)
    {
        set_time_limit(60);
        $dataset = $this->buildVectorFromVitals();
        if (isset($dataset["error"])) return response()->json(["error" => $dataset["error"]]);
        if ($dataset["n"] < 15) return response()->json(["error" => "Se necesitan al menos 15 pacientes con 2+ signos vitales."]);

        $k = $request->get("k", 3);
        $service = new ClusteringService();
        $result = $service->kmeans($dataset["data"], $k);
        $sil = $service->silhouetteScore($dataset["data"], $result["labels"]);

        $maxK = min(8, (int)($dataset["n"] / 5));
        if ($maxK < 3) $maxK = 3;
        $elbow = [];
        for ($ki = 2; $ki <= $maxK; $ki++) {
            $res = $service->kmeans($dataset["data"], $ki);
            $s = $service->silhouetteScore($dataset["data"], $res["labels"]);
            $elbow[] = ["k" => $ki, "inertia" => round($res["inertia"], 2), "silhouette" => round($s, 4)];
        }

        $phenotypeNames = ["CRONICO COMPLEJO", "INESTABLE OCULTO", "RESPONDEDOR RAPIDO", "MONITOREO INTENSIVO", "ALTA COMPLEJIDAD", "ESTABLE"];
        $phenotypeColors = ["#DC2626", "#F59E0B", "#059669", "#3B82F6", "#7C3AED", "#EC4899"];

        $clusterOrder = $this->orderPhenotypes($result["centroids"], $k);

        $clusterStats = [];
        $clusterNames = [];
        for ($c = 0; $c < $k; $c++) {
            $pts = array_filter($dataset["patients"], fn($p) => $result["labels"][$p["_idx"]] === $c);
            $pts = array_values($pts);
            $n = count($pts);
            $realIdx = $clusterOrder[$c] < count($phenotypeNames) ? $clusterOrder[$c] : $c;
            $clusterNames[$c] = $phenotypeNames[$realIdx];

            $hours = array_map(fn($p) => $p["hours_stay"] ?? 0, $pts);
            $ages = array_map(fn($p) => $p["age"], $pts);
            $triageDist = [];
            foreach ($pts as $p) {
                $tl = $p["triage_level"];
                $triageDist[$tl] = ($triageDist[$tl] ?? 0) + 1;
            }

            $clusterStats[] = [
                "name" => $phenotypeNames[$realIdx],
                "n" => $n,
                "pct" => round($n / $dataset["n"] * 100, 1),
                "avg_age" => round(array_sum($ages) / max($n, 1), 0),
                "avg_days" => round(array_sum($hours) / max($n, 1) / 24, 1),
                "min_hours" => $n > 0 ? min($hours) : 0,
                "max_hours" => $n > 0 ? max($hours) : 0,
                "triage_dist" => $triageDist,
            ];
        }

        $patientsOut = [];
        foreach ($dataset["patients"] as $i => $p) {
            $cl = $result["labels"][$i];
            $realIdx = $clusterOrder[$cl] < count($phenotypeNames) ? $clusterOrder[$cl] : $cl;
            $p["cluster"] = $cl;
            $p["cluster_name"] = $phenotypeNames[$realIdx];
            $p["cluster_color"] = $phenotypeColors[$realIdx];
            $patientsOut[] = $p;
        }

        session(["phenotyping_last_run" => now()->format("d/m/Y H:i")]);

        return response()->json([
            "silhouette" => round($sil, 4),
            "inertia" => round($result["inertia"], 2),
            "n_iter" => $result["n_iter"],
            "k" => $k,
            "elbow" => $elbow,
            "cluster_stats" => $clusterStats,
            "cluster_names" => $clusterNames,
            "patients" => $patientsOut,
            "steps" => $result["steps"] ?? null,
        ]);
    }

public function runPca(Request $request)
{
    error_log("PCA START");
    set_time_limit(60);
    try {
        $dataset = $this->buildVectorFromVitals();
        if (isset($dataset["error"])) return response()->json(["error" => $dataset["error"]]);
        if ($dataset["n"] < 15) return response()->json(["error" => "Se necesitan al menos 15 pacientes con 2+ signos vitales."]);

        $components = $request->get("components", 3);
        $service = new PcaService();
        error_log("PCA calling fit with n=" . $dataset["n"] . " comp=" . $components);
        $result = $service->fit($dataset["data"], $components);
        error_log("PCA fit done");

        if (isset($result["error"])) return response()->json(["error" => $result["error"]]);

        $componentNames = [];
        for ($i = 0; $i < count($result["explained_variance_pct"]); $i++) {
            $componentNames[] = "PC" . ($i + 1);
        }

        $patientsOut = [];
        foreach ($dataset["patients"] as $i => $p) {
            $p["cluster_projection"] = array_slice($result["projected"][$i], 0, 2);
            $patientsOut[] = $p;
        }

        error_log("PCA returning success");
        return response()->json([
            "component_names" => $componentNames,
            "explained_variance_pct" => $result["explained_variance_pct"],
            "cumulative_variance_pct" => $result["cumulative_variance_pct"],
            "loadings" => $result["loadings"],
            "patients" => $patientsOut,
            "steps" => $result["steps"] ?? null,
        ]);
    } catch (\Throwable $e) {
        error_log("PCA ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
        return response()->json(["error" => $e->getMessage()], 500);
    }
}
    private function buildVectorFromVitals(): array
    {
        $stats = DB::table("vital_signs")
            ->select("triage_id",
                DB::raw("COUNT(*) as tomas"),
                DB::raw("AVG(heart_rate) as avg_fc"),
                DB::raw("STDDEV(heart_rate) as std_fc"),
                DB::raw("AVG(temperature) as avg_temp"),
                DB::raw("STDDEV(temperature) as std_temp"),
                DB::raw("AVG(oxygen_saturation) as avg_spo2"),
                DB::raw("STDDEV(oxygen_saturation) as std_spo2")
            )
            ->groupBy("triage_id")
            ->having("tomas", ">=", 2)
            ->get();

        if ($stats->count() < 15) return ["error" => "Se necesitan al menos 15 pacientes con 2+ signos vitales."];

        $triageIds = $stats->pluck("triage_id")->toArray();
        if (count($triageIds) > 400) {
            shuffle($triageIds);
            $triageIds = array_slice($triageIds, 0, 400);
        }

        $triages = DB::table("triages")
            ->whereIn("id", $triageIds)
            ->select("id", "patient_name", "age", "triage_level", "created_at", "updated_at")
            ->get()->keyBy("id");

        $statsByKey = $stats->keyBy("triage_id");
        $severityMap = ["Rojo" => 5, "Naranja" => 4, "Amarillo" => 3, "Verde" => 2, "Azul" => 1];

        $medCounts = DB::table("prescriptions")
            ->select("triage_id", DB::raw("COUNT(DISTINCT medication_id) as meds"))
            ->whereIn("triage_id", $triageIds)
            ->groupBy("triage_id")
            ->get()->keyBy("triage_id");

        $data = [];
        $patients = [];
        $idx = 0;

        foreach ($triageIds as $tid) {
            $t = $triages[$tid] ?? null;
            $s = $statsByKey[$tid] ?? null;
            if (!$t || !$s) continue;

            $meds = isset($medCounts[$tid]) ? $medCounts[$tid]->meds : 0;

            // Calcular horas de estancia de forma segura
            $hours = 1;
            if (!empty($t->created_at) && !empty($t->updated_at)) {
                try {
                    $start = Date::parse($t->created_at);
                    $end = Date::parse($t->updated_at);
                    $hours = max(1, $start->diffInHours($end));
                } catch (\Exception $e) {
                    $hours = 1;
                }
            }

            $monitoring = $s->tomas / $hours * 24;

            $vector = [
                round($t->age ?? 30, 2),                          // 01 Edad
                round($severityMap[$t->triage_level] ?? 3, 2),     // 02 Severidad
                round($s->avg_fc ?? 80, 2),                       // 03 FC Promedio
                round($s->std_fc ?? 0, 2),                        // 04 Var FC
                round($s->avg_temp ?? 37, 2),                     // 05 Temp Promedio
                round($s->std_temp ?? 0, 2),                      // 06 Var Temp
                round($s->avg_spo2 ?? 95, 2),                     // 07 SpO2 Promedio
                round($s->std_spo2 ?? 0, 2),                      // 08 Var SpO2
            ];

            $data[] = $vector;
            $patients[] = [
                "id" => $t->id,
                "patient_name" => $t->patient_name,
                "age" => $t->age,
                "triage_level" => $t->triage_level,
                "hours_stay" => round($hours),
                "tomas" => $s->tomas,
                "vector" => $vector,
                "_idx" => $idx,
            ];
            $idx++;
        }

        return ["data" => $data, "patients" => $patients, "n" => count($data)];
    }

    private function orderPhenotypes(array $centroids, int $k): array
    {
        $scores = [];
        for ($c = 0; $c < $k; $c++) {
            $scores[$c] = ($centroids[$c][3] ?? 0) + ($centroids[$c][2] ?? 0) - ($centroids[$c][1] ?? 0);
        }
        arsort($scores);
        $order = array_fill(0, $k, 0);
        $rank = 0;
        foreach ($scores as $cluster => $score) {
            $order[$cluster] = $rank;
            $rank++;
        }
        return $order;
    }

    public function elbowMethod(Request $request)
    {
        set_time_limit(90);
        $dataset = $this->buildVectorFromVitals();
        if (isset($dataset["error"])) return response()->json(["error" => $dataset["error"]]);

        $service = new ClusteringService();
        $maxK = min(8, (int)($dataset["n"] / 5));
        if ($maxK < 3) $maxK = 3;

        $results = [];
        for ($k = 2; $k <= $maxK; $k++) {
            $result = $service->kmeans($dataset["data"], $k);
            $sil = $service->silhouetteScore($dataset["data"], $result["labels"]);
            $results[] = ["k" => $k, "inertia" => round($result["inertia"], 2), "silhouette" => round($sil, 4), "n_iter" => $result["n_iter"]];
        }

        return response()->json(["results" => $results, "n_patients" => $dataset["n"]]);
    }
}
