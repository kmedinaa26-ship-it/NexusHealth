<?php
namespace App\Http\Controllers;

use App\Models\ServiceLog;
use Illuminate\Http\Request;

class SlaDashboardController extends Controller
{
    private const MODULES = [
        'quirofano' => [
            'label' => 'Quirófano', 'event_type' => 'cirugia',
            'color' => '#F59E0B', 'outlier_color' => '#EF4444', 'icon' => 'fa-scalpel-line-dashed'
        ],
        'urgencias' => [
            'label' => 'Urgencias', 'event_type' => 'triage',
            'color' => '#3B82F6', 'outlier_color' => '#EF4444', 'icon' => 'fa-truck-medical'
        ],
        'farmacia' => [
            'label' => 'Farmacia', 'event_type' => 'dispensacion',
            'color' => '#10B981', 'outlier_color' => '#EF4444', 'icon' => 'fa-pills'
        ],
    ];

    public function index(Request $request)
    {
        $module = $request->get('module', 'quirofano');
        if (!isset(self::MODULES[$module])) {
            $module = 'quirofano';
        }
        
        $config = self::MODULES[$module];
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $logs = ServiceLog::module($module)
            ->eventType($config['event_type'])
            ->completed()
            ->dateRange($from, $to)
            ->orderBy('started_at', 'asc')
            ->get();

        $stats = ServiceLog::calculateSlaStats($logs);

        $normalPoints = [];
        $outlierPoints = [];

        foreach ($logs as $log) {
            if (!$log->duration_minutes || !$log->start_hour) {
                continue;
            }
            $point = ['x' => $log->start_hour, 'y' => $log->duration_minutes];
            if ($log->is_outlier) {
                $outlierPoints[] = $point;
            } else {
                $normalPoints[] = $point;
            }
        }

        $outliersTable = $stats['outliers']->map(function ($l) use ($stats) {
            return [
                'id' => $l->id,
                'fecha' => $l->started_at->format('d/m/Y H:i'),
                'duracion' => $l->duration_minutes,
                'z_score' => $l->outlier_z_score,
                'desviacion' => round($l->duration_minutes - $stats['mean'], 1)
            ];
        });

        return view('superadmin.sla-dashboard.index', [
            'module' => $module,
            'config' => $config,
            'modules' => self::MODULES,
            'stats' => $stats,
            'normalPoints' => $normalPoints,
            'outlierPoints' => $outlierPoints,
            'outliersTable' => $outliersTable
        ]);
    }
}
