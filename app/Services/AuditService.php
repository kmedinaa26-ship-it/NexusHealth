<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuditService
{
    public static function log(string $action, string $details, array $data = []): AuditLog
    {
        $request = request();
        $user = Auth::user();

        return AuditLog::create([
            'user_id'       => $user?->id,
            'user_name'     => $user?->name ?? 'Sistema',
            'user_role'     => $user?->getRoleNames()->first() ?? 'N/A',
            'action'        => strtoupper($action),
            'module'        => $data['module'] ?? null,
            'ip_address'    => $request->ip(),
            'details'       => $details,
            'is_suspicious' => $data['is_suspicious'] ?? false,
            'risk_reason'   => $data['risk_reason'] ?? null,
            'risk_level'    => $data['risk_level'] ?? 'bajo',
            'user_agent'    => $request->userAgent(),
            'patient_id'    => $data['patient_id'] ?? null,
            'patient_name'  => $data['patient_name'] ?? null,
            'entity_type'   => $data['entity_type'] ?? null,
            'entity_id'     => $data['entity_id'] ?? null,
            'old_values'    => $data['old_values'] ?? null,
            'new_values'    => $data['new_values'] ?? null,
            'session_id'    => session()->getId(),
            'created_at'    => now(),
        ]);
    }

    public static function login(): void
    {
        self::log('login', 'Inicio de sesión');
    }

    public static function logout(): void
    {
        self::log('logout', 'Cierre de sesión');
    }

    public static function create(string $module, string $label, array $data = []): void
    {
        self::log('create', "Creó {$label}", array_merge(['module' => $module], $data));
    }

    public static function update(string $module, string $label, array $old, array $new, array $data = []): void
    {
        self::log('update', "Editó {$label}", array_merge([
            'module'     => $module,
            'old_values' => $old,
            'new_values' => $new,
        ], $data));
    }

    public static function delete(string $module, string $label, array $data = []): void
    {
        self::log('delete', "Eliminó {$label}", array_merge(['module' => $module], $data));
    }

    public static function export(string $module): void
    {
        self::log('export', "Exportó reporte SLA de {$module}", ['module' => $module]);
    }

    public static function view(string $module, string $label): void
    {
        self::log('view', "Consultó {$label}", ['module' => $module]);
    }

    public static function suspicious(string $action, string $reason, string $level = 'alto'): void
    {
        self::log($action, "Actividad sospechosa: {$reason}", [
            'is_suspicious' => true,
            'risk_reason'   => $reason,
            'risk_level'    => $level,
        ]);
    }
}
