<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'user_role', 'action', 'module',
        'ip_address', 'details', 'is_suspicious', 'risk_reason',
        'risk_level', 'user_agent', 'patient_id', 'patient_name',
        'entity_type', 'entity_id', 'old_values', 'new_values',
        'session_id', 'created_at',
    ];

    protected $casts = [
        'old_values'    => 'array',
        'new_values'    => 'array',
        'is_suspicious' => 'boolean',
        'created_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    public function scopeByRisk($query, $level)
    {
        return $query->where('risk_level', $level);
    }
}
