<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\ML\ClusteringService;
use App\Services\ML\PcaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PhenotypingController extends Controller
{
    public function index()
    {
        $totalPacientes = DB::table('patients')->count();
        $totalAdmisiones = DB::table('admissions')->where('status', 'discharged')->count();
        $totalVitales = DB::table('vital_signs')->count();
        $totalRecetas = DB::table('prescriptions')->count();
        $totalLabs = DB::table('lab_studies')->count();
        $totalImg = DB::table('imaging_studies')->count();

        // Datos nuevos desde última ejecución
        $lastRun = session('phenotyping_last_run');
        $newAdmisions = 0;
        $newVitals = 0;
        if ($lastRun) {
            $newAdmisions = DB::table('admissions')
                ->where('status', 'discharged')
                ->where('updated_at', '>', $lastRun)
                ->count();
            $newVitals = DB::table('vital_signs')
                ->where('created_at', '>', $lastRun)
                ->count();
        }

        return view('superadmin.phenotyping.index', compact(
            'totalPacientes', 'totalAdmisiones', 'totalVitales',
            'totalRecetas', 'totalLabs', 'totalImg',
            'newAdmisions', 'newVitals', 'lastRun'
        ));
    }

    public function buildDataset()
    {
        $admissions = DB::table('admissions')
            ->join('patients', 'admissions.patient_id', '=', 'patients.id')
            ->select(
                'admissions.id as admission_id',
                'admissions.patient_id',
                'patients.name as patient_name',
                'patients.age',
                'patients.triage_level',
                'patients.symptoms',
                'admissions.reason as diagnosis',
                'admissions.created_at as admission_date',
                'admissions.updated_at as discharge_date',
                'admissions.bed_id'
            )
            ->where('admissions.status', 'discharged')
            ->get();

        $variableNames = [
            'Edad', 'Severidad entrada', 'Horas estancia',
            'Variabilidad FC (DE)', 'Variabilidad Temp (DE)',
            'Medicamentos distintos', 'Intensidad monitoreo', 'Carga diagnóstica'
        ];

        $dataset = [];
        foreach ($admissions as $adm) {
            $age = (float) $adm->age;
            $severityMap = ['verde' => 1, 'amarillo' => 2, 'rojo' => 3, 'negro' => 4];
            $severity = (float) ($severityMap[$adm->triage_level] ?? 1);
            $hoursStay = max(1, abs(Carbon::parse($adm->created_at)->diffInHours(Carbon::parse($adm->updated_at))));

            $fcValues = DB::table('vital_signs')
                ->where('patient_id', $adm->patient_id)
                ->whereBetween('created_at', [$adm->admission_date, $adm->discharge_date])
                ->pluck('heart_rate')->map(fn($v) => (float) $v)->toArray();
            $fcStd = $this->calculateStd($fcValues);

            $tempValues = DB::table('vital_signs')
                ->where('patient_id', $adm->patient_id)
                ->whereBetween('created_at', [$adm->admission_date, $adm->discharge_date])
                ->pluck('temperature')->map(fn($v) => (float) $v)->toArray();
            $tempStd = $this->calculateStd($tempValues);

            $nMeds = DB::table('prescriptions')
                ->where('patient_id', $adm->patient_id)
                ->distinct()->count('medicamento');
            $nVitals = DB::table('vital_signs')
                ->where('patient_id', $adm->patient_id)
                ->whereBetween('created_at', [$adm->admission_date, $adm->discharge_date])
                ->count();
            $nLabs = DB::table('lab_studies')->where('patient_id', $adm->patient_id)->count();
            $nImg = DB::table('imaging_studies')->where('patient_id', $adm->patient_id)->count();

            $dataset[] = [
                'admission_id' => $adm->admission_id,
                'patient_id' => $adm->patient_id,
                'patient_name' => $adm->patient_name,
                'age' => $age,
                'triage_level' => $adm->triage_level,
                'diagnosis' => $adm->diagnosis,
                'hours_stay' => $hoursStay,
                'vector' => [$age, $severity, $hoursStay, $fcStd, $tempStd, (float)$nMeds, (float)$nVitals, (float)($nLabs + $nImg)],
            ];
        }

        return response()->json(['success' => true, 'n_patients' => count($dataset), 'variable_names' => $variableNames, 'dataset' => $dataset]);
    }

    public function runKmeans(Request $request)
    {
        $k = $request->input('k', 4);
        $dataset = $this->getDatasetVector();
        if (count($dataset) < $k) return response()->json(['error' => 'Insuficientes datos'], 400);

        $clustering = new ClusteringService();
        $elbow = $clustering->elbowMethod($dataset, 8);
        $result = $clustering->kmeans($dataset, $k);
        $silhouette = $clustering->silhouetteScore($dataset, $result['labels']);

        // Marcar última ejecución
        session(['phenotyping_last_run' => now()->toDateTimeString()]);

        $patientData = $this->getPatientData();
        $clusterNames = $this->interpretClusters($result['centroids']);
        $clustered = [];
        foreach ($patientData as $i => $p) {
            $clustered[] = [
                'patient_name' => $p['patient_name'], 'age' => $p['age'],
                'triage_level' => $p['triage_level'], 'diagnosis' => $p['diagnosis'],
                'hours_stay' => $p['hours_stay'], 'cluster' => $result['labels'][$i],
                'cluster_name' => $clusterNames[$result['labels'][$i]], 'vector' => $p['vector'],
            ];
        }
        $clusterStats = $this->calculateClusterStats($clustered, $k);

        return response()->json([
            'success' => true, 'k' => $k, 'labels' => $result['labels'],
            'centroids' => $result['centroids'], 'inertia' => round($result['inertia'], 2),
            'silhouette' => round($silhouette, 4), 'n_iter' => $result['n_iter'],
            'elbow' => $elbow, 'cluster_names' => $clusterNames,
            'patients' => $clustered, 'cluster_stats' => $clusterStats,
        ]);
    }

    public function runPca(Request $request)
    {
        $nComponents = $request->input('components', 3);
        $dataset = $this->getDatasetVector();
        if (count($dataset) < 5) return response()->json(['error' => 'Insuficientes datos'], 400);

        $pca = new PcaService();
        $result = $pca->fit($dataset, $nComponents);
        if (isset($result['error'])) return response()->json(['error' => $result['error']], 400);

        session(['phenotyping_last_run' => now()->toDateTimeString()]);

        $patientData = $this->getPatientData();
        $patientsPca = [];
        foreach ($patientData as $i => $p) {
            $patientsPca[] = [
                'patient_name' => $p['patient_name'], 'age' => $p['age'],
                'triage_level' => $p['triage_level'], 'cluster_projection' => $result['projected'][$i],
            ];
        }
        $componentNames = $this->interpretComponents($result['loadings']);

        return response()->json([
            'success' => true, 'explained_variance_pct' => $result['explained_variance_pct'],
            'cumulative_variance_pct' => $result['cumulative_variance_pct'],
            'eigenvalues' => $result['eigenvalues'], 'loadings' => $result['loadings'],
            'component_names' => $componentNames, 'projected' => $result['projected'],
            'patients' => $patientsPca, 'n_samples' => $result['n_samples'],
            'n_features' => $result['n_features'],
        ]);
    }

    // ==========================================
    // SIMULADOR DE FLUJO HOSPITALARIO
    // Esto simula lo que pasaría en un hospital real
    // ==========================================
    public function simulateFlow(Request $request)
    {
        $count = $request->input('count', 5);

        $nombres = [
            'Juan Pérez Ruiz', 'María López García', 'Carlos Hernández', 'Ana Martínez Sánchez',
            'Pedro Rodríguez Díaz', 'Laura Flores Moreno', 'Miguel Torres Jiménez',
            'Rosa Ramírez Castro', 'Fernando Mendoza Vargas', 'Patricia Guzmán Morales',
            'Roberto Sánchez Ortiz', 'Gabriela Medina Delgado', 'Alberto Paredes Luna',
            'Diana Estrada Vega', 'Rafael Campos Salazar', 'Isabel Reyes Ibarra',
            'Eduardo Fuentes Palacios', 'Silvia Rangel Montoya', 'Óscar Barrios Tapia',
            'Andrea Villanueva Zaragoza', 'Diego Huerta Paredes', 'Verónica Soto Aguilar',
            'Marco Ayala Cervantes', 'Leticia Duarte Espinoza', 'Héctor Gallegos Lara',
            'Natalia Ochoa Miranda', 'Ricardo Peña Herrera'
        ];
        $diagnosticos = [
            'Neumonía adquirida en comunidad', 'Insuficiencia cardíaca aguda',
            'Infarto agudo al miocardio', 'Apendicitis aguda',
            'EPOC agudizada', 'Infección urinaria complicada', 'Pancreatitis aguda',
            'Colecistitis aguda', 'Fractura de cadera', 'Crisis hipertensiva',
            'Diabetes descompensada', 'Tromboembolia pulmonar', 'Neumotórax'
        ];
        $medicamentos = [
            'Amoxicilina 500mg', 'Omeprazol 20mg', 'Metformina 850mg',
            'Enalapril 10mg', 'Amlodipino 5mg', 'Ceftriaxona 1g IV',
            'Furosemida 40mg IV', 'Heparina 5000UI SC', 'Metoprolol 50mg',
            'Salbutamol neb', 'Ibuprofeno 400mg', 'Paracetamol 500mg',
            'Insulina Glargina', 'Vancomicina 1g IV', 'Meropenem 1g IV'
        ];
        $labNames = ['Biometría hemática', 'Química sanguínea', 'Perfil hepático', 'Gasometría arterial'];
        $imgNames = ['Rayos X de tórax', 'TAC de tórax', 'Ecografía abdominal'];
        $triageLevels = ['verde', 'amarillo', 'rojo', 'negro'];
        $triageWeights = [0.35, 0.35, 0.20, 0.10]; // más verdes y amarillos como en la realidad
        $staffIds = DB::table('staff')->pluck('id')->toArray();
        if (empty($staffIds)) $staffIds = [1, 2, 3];
        $bedIds = DB::table('beds')->pluck('id')->toArray();
        if (empty($bedIds)) $bedIds = [1, 2, 3];

        $simulados = 0;
        $errores = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                // 1. ELEGIR TRIAGE (ponderado realista)
                $rand = mt_rand() / mt_getrandmax();
                $cumul = 0;
                $triage = 'verde';
                foreach ($triageLevels as $idx => $level) {
                    $cumul += $triageWeights[$idx];
                    if ($rand < $cumul) { $triage = $level; break; }
                }
                $age = $triage === 'verde' ? rand(18, 45) : ($triage === 'amarillo' ? rand(25, 65) : rand(40, 85));
                $nombre = $nombres[array_rand($nombres)] . ' ' . rand(1, 99);
                $diagnostico = $diagnosticos[array_rand($diagnosticos)];

                // 2. CREAR PACIENTE
                $patientId = DB::table('patients')->insertGetId([
                    'name' => $nombre,
                    'status' => 'discharged',
                    'blood_type' => ['O+','A+','B+','O-','A-'][array_rand(['O+','A+','B+','O-','A-'])],
                    'is_verified' => true,
                    'trust_score' => rand(60, 100),
                    'triage_level' => $triage,
                    'symptoms' => $diagnostico,
                    'entered_at' => now(),
                    'er_status' => 'processed',
                    'age' => $age,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. CALCULAR ESTANCIA según triage (como en la vida real)
                if ($triage === 'verde') {
                    $hours = rand(6, 36); // 0.25 - 1.5 días
                } elseif ($triage === 'amarillo') {
                    $hours = rand(24, 120); // 1 - 5 días
                } elseif ($triage === 'rojo') {
                    $hours = rand(72, 360); // 3 - 15 días
                } else { // negro
                    $hours = rand(168, 600); // 7 - 25 días
                }
                $admissionDate = now()->subHours($hours);
                $dischargeDate = now()->subMinutes(rand(0, 60));
                $bedId = $bedIds[array_rand($bedIds)];
                $doctorId = $staffIds[array_rand($staffIds)];
                $nurseId = $staffIds[array_rand($staffIds)];

                // 4. CREAR ADMISIÓN
                $admissionId = DB::table('admissions')->insertGetId([
                    'patient_id' => $patientId,
                    'bed_id' => $bedId,
                    'staff_id' => $nurseId,
                    'assigned_doctor_id' => $doctorId,
                    'reason' => $diagnostico,
                    'status' => 'discharged',
                    'created_at' => $admissionDate,
                    'updated_at' => $dischargeDate,
                ]);

                // 5. SIGNOS VITALES durante la estancia (la variable CLAVE)
                $nVitals = $triage === 'verde' ? rand(2, 5) : ($triage === 'amarillo' ? rand(4, 12) : rand(8, 30));
                $fcMean = $triage === 'verde' ? rand(70, 85) : ($triage === 'amarillo' ? rand(75, 95) : rand(85, 120));
                $fcStd = $triage === 'verde' ? rand(2, 6) : ($triage === 'amarillo' ? rand(5, 15) : rand(12, 35));
                $tempMean = $triage === 'verde' ? 36.6 : ($triage === 'negro' ? rand(375, 392)/10 : rand(370, 385)/10);
                $tempStd = $triage === 'verde' ? rand(1, 4)/10 : rand(5, 25)/10;

                for ($v = 0; $v < $nVitals; $v++) {
                    $vitalTime = $admissionDate->copy()->addHours(round(($hours / max($nVitals, 1)) * $v));
                    $fc = max(40, min(200, round($fcMean + (mt_rand()/mt_getrandmax() - 0.5) * 2 * $fcStd)));
                    $temp = max(34.0, min(42.0, round($tempMean + (mt_rand()/mt_getrandmax() - 0.5) * 2 * $tempStd, 1)));
                    $sys = max(80, min(200, round(120 + (mt_rand()/mt_getrandmax() - 0.5) * 30)));
                    $dia = max(40, min(120, round(80 + (mt_rand()/mt_getrandmax() - 0.5) * 20)));

                    DB::table('vital_signs')->insert([
                        'patient_id' => $patientId,
                        'staff_id' => $nurseId,
                        'temperature' => $temp,
                        'heart_rate' => $fc,
                        'blood_pressure' => "$sys/$dia",
                        'created_at' => $vitalTime,
                        'updated_at' => $vitalTime,
                    ]);
                }

                // 6. RECETAS
                $nMeds = $triage === 'verde' ? rand(1, 3) : ($triage === 'amarillo' ? rand(2, 6) : rand(5, 12));
                $usedMeds = [];
                for ($m = 0; $m < $nMeds; $m++) {
                    $med = $medicamentos[array_rand($medicamentos)];
                    while (in_array($med, $usedMeds)) $med = $medicamentos[array_rand($medicamentos)];
                    $usedMeds[] = $med;
                    DB::table('prescriptions')->insert([
                        'patient_id' => $patientId,
                        'medico_c_id' => $doctorId,
                        'medicamento' => $med,
                        'dosis' => ['Cada 8hrs', 'Cada 12hrs', 'Cada 24hrs', 'Cada 6hrs'][array_rand(['Cada 8hrs', 'Cada 12hrs', 'Cada 24hrs', 'Cada 6hrs'])],
                        'status' => 'dispensed',
                        'in_cuadro_basico' => rand(0, 1),
                        'created_at' => $admissionDate->copy()->addMinutes(rand(10, 120)),
                        'updated_at' => now(),
                    ]);
                }

                // 7. ESTUDIOS
                $nLabs = $triage === 'verde' ? rand(0, 2) : rand(2, 6);
                for ($l = 0; $l < $nLabs; $l++) {
                    DB::table('lab_studies')->insert([
                        'patient_id' => $patientId,
                        'study_name' => $labNames[array_rand($labNames)],
                        'results' => json_encode(['hemoglobina' => rand(8, 16), 'leucocitos' => rand(4000, 15000)]),
                        'status' => 'completed',
                        'created_at' => $admissionDate->copy()->addHours(rand(1, max(1, (int)($hours * 0.3)))),
                        'updated_at' => now(),
                    ]);
                }
                $nImg = $triage === 'verde' ? rand(0, 1) : rand(0, 3);
                for ($im = 0; $im < $nImg; $im++) {
                    DB::table('imaging_studies')->insert([
                        'patient_id' => $patientId,
                        'study_name' => $imgNames[array_rand($imgNames)],
                        'results' => 'Hallazgos compatibles',
                        'status' => 'completed',
                        'created_at' => $admissionDate->copy()->addHours(rand(2, max(2, (int)($hours * 0.5)))),
                        'updated_at' => now(),
                    ]);
                }

                $simulados++;
            } catch (\Throwable $e) {
                $errores[] = "Paciente $i: " . $e->getMessage();
            }
        }

        // Estadísticas actualizadas
        $total = DB::table('admissions')->where('status', 'discharged')->count();

        return response()->json([
            'success' => true,
            'simulados' => $simulados,
            'errores' => $errores,
            'total_disponibles' => $total,
            'mensaje' => "Se simularon $simulados flujos hospitalarios completos. Ejecuta K-Means para descubrir nuevos patrones."
        ]);
    }

    // ==========================================
    // HELPERS
    // ==========================================
    private function getDatasetVector(): array
    {
        $admissions = DB::table('admissions')
            ->join('patients', 'admissions.patient_id', '=', 'patients.id')
            ->select('admissions.id as admission_id', 'admissions.patient_id', 'patients.age', 'patients.triage_level', 'admissions.created_at', 'admissions.updated_at')
            ->where('admissions.status', 'discharged')
            ->get();

        $vectors = [];
        foreach ($admissions as $adm) {
            $severityMap = ['verde' => 1, 'amarillo' => 2, 'rojo' => 3, 'negro' => 4];
            $hoursStay = max(1, abs(Carbon::parse($adm->created_at)->diffInHours(Carbon::parse($adm->updated_at))));
            $fcValues = DB::table('vital_signs')->where('patient_id', $adm->patient_id)->whereBetween('created_at', [$adm->created_at, $adm->updated_at])->pluck('heart_rate')->map(fn($v) => (float) $v)->toArray();
            $tempValues = DB::table('vital_signs')->where('patient_id', $adm->patient_id)->whereBetween('created_at', [$adm->created_at, $adm->updated_at])->pluck('temperature')->map(fn($v) => (float) $v)->toArray();
            $nMeds = DB::table('prescriptions')->where('patient_id', $adm->patient_id)->distinct()->count('medicamento');
            $nVitals = DB::table('vital_signs')->where('patient_id', $adm->patient_id)->whereBetween('created_at', [$adm->created_at, $adm->updated_at])->count();
            $nLabs = DB::table('lab_studies')->where('patient_id', $adm->patient_id)->count();
            $nImg = DB::table('imaging_studies')->where('patient_id', $adm->patient_id)->count();

            $vectors[] = [
                (float)$adm->age,
                (float)($severityMap[$adm->triage_level] ?? 1),
                (float)$hoursStay,
                $this->calculateStd($fcValues),
                $this->calculateStd($tempValues),
                (float)$nMeds,
                (float)$nVitals,
                (float)($nLabs + $nImg),
            ];
        }
        return $vectors;
    }

    private function getPatientData(): array
    {
        $admissions = DB::table('admissions')
            ->join('patients', 'admissions.patient_id', '=', 'patients.id')
            ->select('admissions.patient_id', 'patients.name as patient_name', 'patients.age', 'patients.triage_level', 'admissions.reason as diagnosis', 'admissions.created_at', 'admissions.updated_at')
            ->where('admissions.status', 'discharged')
            ->get();

        $data = [];
        foreach ($admissions as $adm) {
            $severityMap = ['verde' => 1, 'amarillo' => 2, 'rojo' => 3, 'negro' => 4];
            $hoursStay = max(1, abs(Carbon::parse($adm->created_at)->diffInHours(Carbon::parse($adm->updated_at))));
            $fcValues = DB::table('vital_signs')->where('patient_id', $adm->patient_id)->whereBetween('created_at', [$adm->created_at, $adm->updated_at])->pluck('heart_rate')->map(fn($v) => (float) $v)->toArray();
            $tempValues = DB::table('vital_signs')->where('patient_id', $adm->patient_id)->whereBetween('created_at', [$adm->created_at, $adm->updated_at])->pluck('temperature')->map(fn($v) => (float) $v)->toArray();
            $nMeds = DB::table('prescriptions')->where('patient_id', $adm->patient_id)->distinct()->count('medicamento');
            $nVitals = DB::table('vital_signs')->where('patient_id', $adm->patient_id)->whereBetween('created_at', [$adm->created_at, $adm->updated_at])->count();
            $nLabs = DB::table('lab_studies')->where('patient_id', $adm->patient_id)->count();
            $nImg = DB::table('imaging_studies')->where('patient_id', $adm->patient_id)->count();

            $data[] = [
                'patient_name' => $adm->patient_name, 'age' => (float)$adm->age,
                'triage_level' => $adm->triage_level, 'diagnosis' => $adm->diagnosis,
                'hours_stay' => $hoursStay,
                'vector' => [(float)$adm->age, (float)($severityMap[$adm->triage_level] ?? 1), (float)$hoursStay, $this->calculateStd($fcValues), $this->calculateStd($tempValues), (float)$nMeds, (float)$nVitals, (float)($nLabs + $nImg)],
            ];
        }
        return $data;
    }

    private function calculateStd(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0.0;
        $mean = array_sum($values) / $n;
        $variance = 0.0;
        foreach ($values as $v) $variance += ($v - $mean) ** 2;
        return sqrt($variance / ($n - 1));
    }

    private function interpretClusters(array $centroids): array
    {
        $names = [];
        foreach ($centroids as $c) {
            $score = 0;
            $score += $c[2] > 200 ? 2 : ($c[2] > 72 ? 1 : 0);
            $score += $c[3] > 15 ? 2 : ($c[3] > 8 ? 1 : 0);
            $score += $c[5] > 6 ? 2 : ($c[5] > 3 ? 1 : 0);
            $score += $c[6] > 15 ? 2 : ($c[6] > 8 ? 1 : 0);
            $score += $c[7] > 8 ? 2 : ($c[7] > 4 ? 1 : 0);
            if ($score >= 8) $names[] = 'CRÓNICO COMPLEJO';
            elseif ($score <= 2) $names[] = 'RESPONDEDOR RÁPIDO';
            elseif ($c[3] > 15 && $c[4] > 1.0) $names[] = 'INESTABLE OCULTO';
            else $names[] = 'ESTABLE LARGO';
        }
        return $names;
    }

    private function interpretComponents(array $loadings): array
    {
        $varNames = ['Edad', 'Severidad', 'Horas estancia', 'Var. FC', 'Var. Temp', 'Medicamentos', 'Monitoreo', 'Carga diag.'];
        $names = [];
        foreach ($loadings as $comp) {
            $maxIdx = array_search(max(array_map('abs', $comp)), array_map('abs', $comp));
            if ($maxIdx === 3 || $maxIdx === 4) $name = 'INESTABILIDAD FISIOLÓGICA';
            elseif ($maxIdx === 2) $name = 'USO DE RECURSOS';
            elseif ($maxIdx === 5 || $maxIdx === 7) $name = 'COMPLEJIDAD TERAPÉUTICA';
            elseif ($maxIdx === 0) $name = 'PERFIL DEMOGRÁFICO';
            elseif ($maxIdx === 1) $name = 'SEVERIDAD DE ENTRADA';
            elseif ($maxIdx === 6) $name = 'INTENSIDAD DE MONITOREO';
            else $name = $varNames[$maxIdx];
            $names[] = $name;
        }
        return $names;
    }

    private function calculateClusterStats(array $clustered, int $k): array
    {
        $stats = [];
        for ($c = 0; $c < $k; $c++) {
            $patients = array_values(array_filter($clustered, fn($p) => $p['cluster'] === $c));
            $n = count($patients);
            $ages = array_column($patients, 'age');
            $hours = array_column($patients, 'hours_stay');
            $stats[] = [
                'cluster' => $c, 'name' => $patients[0]['cluster_name'] ?? 'Desconocido',
                'n' => $n, 'pct' => round(($n / max(count($clustered), 1)) * 100, 1),
                'avg_age' => round(array_sum($ages) / max($n, 1), 1),
                'avg_hours' => round(array_sum($hours) / max($n, 1), 1),
                'avg_days' => round(array_sum($hours) / max($n, 1) / 24, 1),
                'min_hours' => min($hours), 'max_hours' => max($hours),
                'triage_dist' => $this->countValues(array_column($patients, 'triage_level')),
            ];
        }
        return $stats;
    }

    private function countValues(array $arr): array
    {
        $counts = [];
        foreach ($arr as $v) $counts[$v] = ($counts[$v] ?? 0) + 1;
        return $counts;
    }
}
