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
        $from = now()->startOfMonth(); $to = now()->endOfMonth();

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

        $descriptiveTitle = $this->generateDescriptiveTitle($module, $stats, $config);

        return view('superadmin.sla-dashboard.index', [
            'module' => $module, 'config' => $config, 'modules' => self::MODULES,
            'stats' => $stats, 'normalPoints' => $normalPoints, 'outlierPoints' => $outlierPoints, 'outliersTable' => $outliersTable,
            'ranges' => $ranges, 'boxplotData' => $boxplotData, 'barData' => $barData, 'descriptiveTitle' => $descriptiveTitle
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

        $csv = "ID,Paciente,Modulo,Fecha Inicio,Duracion Min,Hora,Es Outlier,Z-Score\n";
        foreach ($logs as $l) {
            $csv .= "{$l->id},{$l->patient_identifier},{$l->module},{$l->started_at},{$l->duration_minutes},{$l->start_hour}," . ($l->is_outlier ? 'SI' : 'NO') . "," . ($l->outlier_z_score ?? 'N/A') . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=sla-{$module}-" . now()->format('Y-m-d') . ".csv");
    }
}
