@extends('superadmin.layout')
@section('title', 'Auditoría Total del Sistema')
@section('nav-auditoria', 'active')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h3 style="font-weight: 800; color: #1E1A17;">Línea de Tiempo Hospitalaria</h3>
    <a href="{{ route('superadmin.actividadSospechosa') }}" style="background: #C7291C; color: white; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 700; text-decoration:none; font-size:0.9rem;">
        <i class="fas fa-exclamation-triangle"></i> Ver Actividad Sospechosa
    </a>
</div>

<div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #1E1A17; color: white; text-align: left;">
                <th style="padding: 1rem; font-size: 0.8rem;">Fecha/Hora</th>
                <th style="padding: 1rem; font-size: 0.8rem;">Usuario & Rol</th>
                <th style="padding: 1rem; font-size: 0.8rem;">Acción</th>
                <th style="padding: 1rem; font-size: 0.8rem;">Detalles</th>
                <th style="padding: 1rem; font-size: 0.8rem;">IP / Dispositivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr style="border-bottom: 1px solid #E5E7EB; background: {{ isset($log->is_suspicious) && $log->is_suspicious ? '#FFF1F0' : 'white' }}">
                <td style="padding: 1rem; font-size: 0.85rem; color: #736860; white-space: nowrap;">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                <td style="padding: 1rem;">
                    <div style="font-weight: 700; color: #1E1A17;">{{ $log->user_name }}</div>
                    <span style="background: #E5E7EB; color: #1E1A17; padding:0.1rem 0.4rem; border-radius:10px; font-size:0.7rem; font-weight:700;">{{ $log->user_role }}</span>
                </td>
                <td style="padding: 1rem; font-weight: 700; color: {{ str_contains($log->action, 'LOGIN') ? '#2D9E6A' : '#F05A4E' }};">{{ $log->action }}</td>
                <td style="padding: 1rem; font-size: 0.85rem; color: #736860;">{{ $log->details }}</td>
                <td style="padding: 1rem; font-size: 0.8rem;">
                    <div style="font-family: monospace; color: #1E1A17; font-weight:600;">{{ $log->ip_address }}</div>
                    <div style="color: #736860; font-size: 0.7rem;">{{ $log->user_agent ?? 'N/A' }}</div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
