<?php

namespace App\Http\Controllers;

use App\Models\ServiceLog;
use Illuminate\Http\Request;

class SlaDashboardController extends Controller
{
    private const MODULES = [
        'quirofano' => ['label' => 'Quirófano', 'event_type' => 'cirugia', 'color' => '#F59E0B', 'outlier_color' => '#EF4444', 'icon' => 'fa-scalpel-line-dashed'],
        'urgencias' => ['label' => 'Urgencias', 'event_type' => 'triage', 'color' => '#3B82F6', 'outlier_color' => '#EF4444', 'icon' => 'truck-medical'],
        'farmacia'  => ['label' => 'Farmacia', 'event_type' => 'dispensacion', 'color' => '#10B981', 'outlier_color' => '#EF4444', 'icon' => 'fa-pills'],
    ];

    public function index(Request $request)
    {
        $module = $request->get('module', 'quirofano');
        if (!isset(self::MODULES[$module])) $module = 'quirofano';
        $config = self::MODULES[$module];
        $from = $request->get('from') ? \Carbon\Carbon::parse($request->get('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->get('to') ? \Carbon\Carbon::parse($request->get('to'))->endOfDay() : now()->endOfMonth();

        $logs = ServiceLog::module($module)->eventType($config['event_type'])->completed()->dateRange($from, $to)->orderBy('started_at', 'asc')->get();
        $stats = ServiceLog::calculateSlaStats($logs);
        
        $normalPoints = []; $outlierPoints = [];
        foreach ($logs as $log) {
            if (!$log->duration_minutes || !$log->start_hour) continue;
            $point = ['x' => $log->start_hour, 'y' => $log->duration_minutes];
            if ($log->is_outlier) { $outlierPoints[] = $point; } else { $normalPoints[] = $point; }
        }

        $outliersTable = $stats['outliers']->map(function ($l) use ($stats) {
            return ['id' => $l->id, 'fecha' => $l->started_at->format('d/m/Y H:i'), 'duracion' => $l->duration_minutes, 'z_score' => $l->outlier_z_score, 'desviacion' => round($l->duration_minutes - $stats['mean'], 1)];
        });

        $ranges = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '91-120' => 0, '121-150' => 0, '151+ (Anomalía)' => 0];
        foreach ($logs as $log) {
            $d = $log->duration_minutes;
            if ($d <= 30) $ranges['0-30']++; elseif ($d <= 60) $ranges['31-60']++; elseif ($d <= 90) $ranges['61-90']++; elseif ($d <= 120) $ranges['91-120']++; elseif ($d <= 150) $ranges['121-150']++; else $ranges['151+ (Anomalía)']++;
        }

        $durations = $logs->pluck('duration_minutes')->filter()->sort()->values()->toArray();
        $boxplotData = $this->calculateBoxplot($durations);

        $barData = [];
        foreach (self::MODULES as $key => $mod) {
            $modLogs = ServiceLog::module($key)->eventType($mod['event_type'])->completed()->dateRange($from, $to)->get();
            $modDurations = $modLogs->pluck('duration_minutes')->filter()->toArray();
            $avg = count($modDurations) > 0 ? round(array_sum($modDurations) / count($modDurations), 1) : 0;
            $barData[] = ['module' => $mod['label'], 'avg' => $avg, 'color' => $mod['color'], 'count' => count($modDurations)];
        }

        $boxplotAllModules = [];
        foreach (self::MODULES as $key => $mod) {
            $modLogs = ServiceLog::module($key)->eventType($mod['event_type'])->completed()->dateRange($from, $to)->get();
            $modDurations = $modLogs->pluck('duration_minutes')->filter()->sort()->values()->toArray();
            $boxplotAllModules[] = [
                'label' => $mod['label'],
                'color' => $mod['color'],
                'data' => $this->calculateBoxplot($modDurations)
            ];
        }

        $shifts = ['Matutino (7-14)' => 0, 'Vespertino (15-22)' => 0, 'Nocturno (23-6)' => 0];
        $guardias = ['Guardia Dia (7-19)' => 0, 'Guardia Noche (19-7)' => 0];
        $dayTypes = ['Laboral' => 0, 'Sabado' => 0, 'Domingo' => 0];
        foreach ($logs as $log) {
            $h = $log->start_hour;
            if ($h >= 7 && $h <= 14) $shifts['Matutino (7-14)']++;
            elseif ($h >= 15 && $h <= 22) $shifts['Vespertino (15-22)']++;
            else $shifts['Nocturno (23-6)']++;
            if ($h >= 7 && $h < 19) $guardias['Guardia Dia (7-19)']++;
            else $guardias['Guardia Noche (19-7)']++;
            $dayNum = $log->started_at->format('N');
            if ($dayNum <= 5) $dayTypes['Laboral']++;
            elseif ($dayNum == 6) $dayTypes['Sabado']++;
            else $dayTypes['Domingo']++;
        }
        $boxplotChartData = array_map(function($m) { return [$m['data']['min'], $m['data']['q1'], $m['data']['median'], $m['data']['q3'], $m['data']['max']]; }, $boxplotAllModules);

        $descriptiveTitle = $this->generateDescriptiveTitle($module, $stats, $config);

        $periodStart = now()->startOfMonth()->format('d/m/Y');
        $periodEnd = now()->format('d/m/Y');

        $monthName = now()->locale('es')->format('F Y');
        $chartTitles = [
            'scatter' => [
                'quirofano' => 'Tiempos quirúrgicos — Agosto 2026',
                'urgencias' => 'Tiempos de triage — Agosto 2026',
                'farmacia' => 'Tiempos de dispensación — Agosto 2026'
            ],
            'boxplot' => 'Distribución estadística — Agosto 2026',
            'histogram' => [
                'quirofano' => 'Cirugías por turno — Agosto 2026',
                'urgencias' => 'Triages por turno — Agosto 2026',
                'farmacia' => 'Dispensaciones por turno — Agosto 2026'
            ],
            'bar' => 'Velocidad promedio por área — Agosto 2026'
        ];

        $chartDescriptions = [
            'scatter' => [
                'quirofano' => 'Cada punto es una cirugía registrada. ¿Las cirugías de madrugada duran más que las de la mañana? Los cruces rojos indican tiempos que exceden el límite seguro.',
                'urgencias' => 'Cada punto es un triage clasificado. ¿Hay saturación en horarios nocturnos? Los cruces rojos muestran triages que tardaron más de lo normal.',
                'farmacia' => 'Cada punto es una dispensación de medicamentos. ¿Las recetas se acumulan en algún turno? Los cruces rojos indican entregas excesivamente lentas.'
            ],
            'boxplot' => [
                'quirofano' => '¿La variabilidad en tiempos quirúrgicos es aceptable? Caja grande = procesos inconsistentes. Caja pequeña con puntos lejos = pocas pero severas desviaciones.',
                'urgencias' => '¿El triage es un proceso predecible? Caja compacta significa buen control. Si la caja crece, el proceso se está volviendo errático.',
                'farmacia' => '¿La dispensación es estable? Compara contra los otros módulos para ver si farmacia es el área más controlada del hospital.'
            ],
            'histogram' => [
                'quirofano' => '¿En qué turno se opera más? Si la mayoría de cirugías son de mañana pero los outliers son de madrugada, el turno nocturno necesita refuerzo.',
                'urgencias' => '¿Cuándo llega más pacientes? Si la noche supera a la mañana, el flujo está invertido y hay que ajustar recursos.',
                'farmacia' => '¿Cuándo se dispensa más? Un pico en tarde/noche indica cuello de botella por acumulación de recetas del día.'
            ],
            'bar' => [
                'quirofano' => 'Las cirugías son naturalmente las más largas. Lo relevante es si el promedio sube respecto a meses anteriores.',
                'urgencias' => 'El triage debería ser el más rápido. Si supera a farmacia, hay un problema de clasificación.',
                'farmacia' => 'La dispensación debe ser la más rápida. Si se acerca a urgencias, hay retrasos en la entrega de medicamentos.'
            ]
        ];

        return view('superadmin.sla-dashboard.index', [
            'module' => $module, 'config' => $config, 'modules' => self::MODULES, 'from' => $from, 'to' => $to,
            'stats' => $stats, 'normalPoints' => $normalPoints, 'outlierPoints' => $outlierPoints, 'outliersTable' => $outliersTable,
            'outlierChartData' => $stats['outliers']->map(function($l) use ($stats) {
                return [
                    'label' => $l->started_at->format('d/m H:i'),
                    'duration' => $l->duration_minutes,
                    'zscore' => $l->outlier_z_score,
                    'deviation' => round($l->duration_minutes - $stats['mean'], 1)
                ];
            })->values(),
            'ranges' => $ranges, 'boxplotData' => $boxplotData, 'boxplotAllModules' => $boxplotAllModules, 'boxplotChartData' => $boxplotChartData, 'shifts' => $shifts, 'guardias' => $guardias, 'dayTypes' => $dayTypes, 'barData' => $barData, 'outlierChartData' => $stats['outliers']->map(function($l) use ($stats) { return ['label' => $l->started_at->format('d/m H:i'), 'duration' => $l->duration_minutes, 'zscore' => $l->outlier_z_score, 'deviation' => round($l->duration_minutes - $stats['mean'], 1)]; })->values(), 'descriptiveTitle' => $descriptiveTitle, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd, 'monthName' => $monthName, 'chartTitles' => $chartTitles, 'chartDescriptions' => $chartDescriptions
        ]);
    }

    public function generateMassData()
    {
        $data = [];
        foreach (self::MODULES as $key => $mod) {
            for ($i = 0; $i < 8; $i++) {
                $hour = rand(8, 18);
                $duration = rand(20, 90);
                $start = now()->subDays(rand(0, 30))->setHour($hour)->setMinute(rand(0, 59));
                $end = (clone $start)->addMinutes($duration);
                $data[] = ['module' => $key, 'event_type' => $mod['event_type'], 'user_id' => 1, 'patient_identifier' => 'PAC-ING-'.$key.'-'.$i, 'started_at' => $start, 'ended_at' => $end, 'duration_minutes' => $duration, 'start_hour' => $hour, 'status' => 'completed', 'is_outlier' => false, 'outlier_z_score' => null, 'created_at' => now(), 'updated_at' => now()];
            }
            for ($i = 0; $i < 2; $i++) {
                $hour = rand(1, 5);
                $duration = rand(160, 220);
                $start = now()->subDays(rand(0, 30))->setHour($hour)->setMinute(rand(0, 59));
                $end = (clone $start)->addMinutes($duration);
                $data[] = ['module' => $key, 'event_type' => $mod['event_type'], 'user_id' => 1, 'patient_identifier' => 'PAC-ANO-'.$key.'-'.$i, 'started_at' => $start, 'ended_at' => $end, 'duration_minutes' => $duration, 'start_hour' => $hour, 'status' => 'completed', 'is_outlier' => false, 'outlier_z_score' => null, 'created_at' => now(), 'updated_at' => now()];
            }
        }
        ServiceLog::insert($data);
        return back()->with('success', '¡Lote insertado! Puedes darle varias veces para ver cómo crece.');
    }

    private function calculateBoxplot($data) {
        if (count($data) < 5) return ['min' => 0, 'q1' => 0, 'median' => 0, 'q3' => 0, 'max' => 0];
        $count = count($data);
        $getP = function($arr, $p) use ($count) { $index = ($p / 100) * ($count - 1); $lower = (int) floor($index); $upper = (int) ceil($index); if ($lower === $upper) return $arr[$lower]; return $arr[$lower] + ($index - $lower) * ($arr[$upper] - $arr[$lower]); };
        return ['min' => $data[0], 'q1' => round($getP($data, 25), 1), 'median' => round($getP($data, 50), 1), 'q3' => round($getP($data, 75), 1), 'max' => $data[$count - 1]];
    }

    private function generateDescriptiveTitle($module, $stats, $config) {
        if ($stats['count'] === 0) return "No hay eventos registrados en {$config['label']} este mes.";
        $pctNormal = $stats['count'] > 0 ? round((($stats['count'] - $stats['outlier_count']) / $stats['count']) * 100, 0) : 0;
        return "El {$pctNormal}% de las operaciones en {$config['label']} terminan en menos de {$stats['threshold']} min (Promedio: {$stats['mean']} min).";
    }
        public function report(Request $request)
    {
        $module = $request->get('module', 'all');
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $reportData = [];
        $modules = $module === 'all' ? self::MODULES : [$module => self::MODULES[$module]];

        foreach ($modules as $key => $mod) {
            $logs = ServiceLog::module($key)
                ->eventType($mod['event_type'])
                ->completed()
                ->whereBetween('started_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->orderBy('started_at', 'asc')
                ->get();

            $durations = $logs->pluck('duration_minutes')->filter()->toArray();
            $count = count($durations);
            $mean = $count > 0 ? round(array_sum($durations) / $count, 1) : 0;
            $stdDev = $count > 1 ? round(sqrt(array_sum(array_map(fn($d) => pow($d - $mean, 2), $durations)) / ($count - 1)), 1) : 0;
            $threshold = $stdDev > 0 ? round($mean + 2.5 * $stdDev, 1) : PHP_INT_MAX;

            $outliers = $logs->filter(function ($l) use ($mean, $stdDev) {
                if ($stdDev == 0) return false;
                $z = ($l->duration_minutes - $mean) / $stdDev;
                return $z > 2.5;
            });

            $outliersList = $outliers->map(function ($l) use ($mean, $stdDev) {
                return [
                    'fecha' => $l->started_at->format('d/m/Y H:i'),
                    'duracion' => $l->duration_minutes,
                    'desviacion' => round($l->duration_minutes - $mean, 1),
                    'z_score' => round(($l->duration_minutes - $mean) / ($stdDev > 0 ? $stdDev : 1), 2)
                ];
            });

            $normalPoints = [];
            $outlierPoints = [];
            foreach ($logs as $log) {
                $point = ['x' => $log->start_hour + ($log->started_at->minute / 60), 'y' => $log->duration_minutes];
                if ($log->is_outlier) { $outlierPoints[] = $point; } else { $normalPoints[] = $point; }
            }

            $shifts = ['Madrugada (0-6)' => 0, 'Manana (7-13)' => 0, 'Tarde (14-20)' => 0, 'Noche (21-23)' => 0];
            foreach ($logs as $log) {
                $h = $log->start_hour;
                if ($h <= 6) $shifts['Madrugada (0-6)']++;
                elseif ($h <= 13) $shifts['Manana (7-13)']++;
                elseif ($h <= 20) $shifts['Tarde (14-20)']++;
                else $shifts['Noche (21-23)']++;
            }

            $sortedDurations = collect($durations)->sort()->values()->toArray();
            $cnt = count($sortedDurations);
            if ($cnt < 5) {
                $bp = ['min' => 0, 'q1' => 0, 'median' => 0, 'q3' => 0, 'max' => 0];
            } else {
                $getP = function($arr, $p) use ($cnt) { $i = ($p / 100) * ($cnt - 1); $lo = (int) floor($i); $hi = (int) ceil($i); if ($lo === $hi) return $arr[$lo]; return $arr[$lo] + ($i - $lo) * ($arr[$hi] - $arr[$lo]); };
                $bp = ['min' => $sortedDurations[0], 'q1' => round($getP($sortedDurations, 25), 1), 'median' => round($getP($sortedDurations, 50), 1), 'q3' => round($getP($sortedDurations, 75), 1), 'max' => $sortedDurations[$cnt - 1]];
            }

            $reportData[$key] = [
                'config' => $mod,
                'stats' => ['count' => $count, 'mean' => $mean, 'stdDev' => $stdDev, 'threshold' => $threshold === PHP_INT_MAX ? 'N/A' : $threshold, 'outlier_count' => $outliers->count()],
                'normalPoints' => $normalPoints,
                'outlierPoints' => $outlierPoints,
                'outliersTable' => $outliersList,
                'shifts' => $shifts,
                'boxplot' => $bp,
                'boxplotChartData' => [$bp['min'], $bp['q1'], $bp['median'], $bp['q3'], $bp['max']]
            ];
        }

        // Barras comparativas
        $barData = [];
        foreach ($reportData as $key => $rd) {
            $barData[] = ['module' => $rd['config']['label'], 'avg' => $rd['stats']['mean'], 'color' => $rd['config']['color']];
        }

        // Boxplot comparativo
        $bpAllLabels = [];
        $bpAllColors = [];
        $bpAllData = [];
        foreach ($reportData as $key => $rd) {
            $bpAllLabels[] = $rd['config']['label'];
            $bpAllColors[] = $rd['config']['color'];
            $bpAllData[] = $rd['boxplotChartData'];
        }

        return view('superadmin.sla-dashboard.report', [
            'reportData' => $reportData,
            'barData' => $barData,
            'bpAllLabels' => $bpAllLabels,
            'bpAllColors' => $bpAllColors,
            'bpAllData' => $bpAllData,
            'from' => $from,
            'to' => $to,
            'moduleFilter' => $module,
            'modules' => self::MODULES
        ]);
    }
    public function exportCsv(Request $request)
    {
        $module = $request->get('module', 'quirofano');
        if (!isset(self::MODULES[$module])) $module = 'quirofano';
        $config = self::MODULES[$module];

        $logs = ServiceLog::module($module)
            ->eventType($config['event_type'])
            ->completed()
            ->orderBy('started_at', 'desc')
            ->get();

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body><table border="1">';
        $html .= '<tr><th>ID</th><th>Paciente</th><th>Módulo</th><th>Fecha Inicio</th><th>Duración (min)</th><th>Hora</th><th>Es Outlier</th><th>Z-Score</th></tr>';
        foreach ($logs as $l) {
            $fecha = $l->started_at ? $l->started_at->format('Y-m-d H:i') : '';
            $zScore = $l->outlier_z_score !== null ? number_format($l->outlier_z_score, 2) : '0.00';
            $outlier = $l->is_outlier ? 'SI' : 'NO';
            $html .= "<tr><td>{$l->id}</td><td>{$l->patient_identifier}</td><td>{$l->module}</td><td>{$fecha}</td><td>{$l->duration_minutes}</td><td>{$l->start_hour}</td><td>{$outlier}</td><td>{$zScore}</td></tr>";
        }
        $html .= '</table></body></html>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=sla-{$module}-" . now()->format('Y-m-d') . ".xls");
    }
}
