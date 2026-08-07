<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLog extends Model {
    protected $fillable = ['module','event_type','user_id','patient_identifier','started_at','ended_at','duration_minutes','start_hour','status','is_outlier','outlier_z_score','notes'];
    protected $casts = ['started_at'=>'datetime','ended_at'=>'datetime','is_outlier'=>'boolean','duration_minutes'=>'integer','start_hour'=>'integer'];
    
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function scopeModule($q, $m) { return $q->where('module', $m); }
    public function scopeEventType($q, $t) { return $q->where('event_type', $t); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
    public function scopeDateRange($q, $from, $to) { return $q->whereBetween('started_at', [$from, $to]); }

    /**
     * MÉTODO NUEVO: Atajo para registrar eventos desde cualquier controlador.
     * Calcula la duración y la hora automáticamente.
     */
    public static function logFromEvent($module, $eventType, $startedAt, $endedAt, $userId = null, $patientId = null) {
        $duration = $startedAt->diffInMinutes($endedAt);
        self::create([
            'module'             => $module,
            'event_type'         => $eventType,
            'user_id'            => $userId,
            'patient_identifier' => $patientId,
            'started_at'         => $startedAt,
            'ended_at'           => $endedAt,
            'duration_minutes'   => $duration,
            'start_hour'         => (int) $startedAt->format('H'),
            'status'             => 'completed'
        ]);
    }

    public static function calculateSlaStats($logs): array {
        $durations = $logs->pluck('duration_minutes')->filter()->values()->toArray();
        if (count($durations) < 2) {
            return ['count'=>count($durations),'mean'=>$durations[0]??0,'std_dev'=>0,'min'=>$durations[0]??0,'max'=>$durations[0]??0,'median'=>$durations[0]??0,'threshold'=>PHP_INT_MAX,'outlier_count'=>0,'outliers'=>collect()];
        }
        $count = count($durations); 
        $mean = array_sum($durations)/$count;
        $sumSq = 0; 
        foreach($durations as $d) $sumSq += pow($d - $mean, 2);
        $stdDev = sqrt($sumSq / ($count - 1));
        $threshold = $mean + (2 * $stdDev);
        $outliers = collect();
        
        foreach($logs as $log) {
            if($log->duration_minutes && $log->duration_minutes > $threshold) {
                $z = $stdDev > 0 ? round(($log->duration_minutes - $mean)/$stdDev, 2) : 0;
                $log->update(['is_outlier'=>true,'outlier_z_score'=>$z]); 
                $outliers->push($log);
            } elseif($log->is_outlier) { 
                $log->update(['is_outlier'=>false,'outlier_z_score'=>null]); 
            }
        }
        sort($durations);
        $mid = floor($count/2);
        $median = ($count%2==0) ? ($durations[$mid-1]+$durations[$mid])/2 : $durations[$mid];
        
        return [
            'count'=>$count, 'mean'=>round($mean,1), 'std_dev'=>round($stdDev,1), 
            'min'=>min($durations), 'max'=>max($durations), 'median'=>round($median,1), 
            'threshold'=>round($threshold,1), 'outlier_count'=>$outliers->count(), 'outliers'=>$outliers
        ];
    }
}
