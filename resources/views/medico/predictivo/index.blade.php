@extends('medico.layout')
@section('title', 'Predicciones IA Activas')
@section('nav-predicciones', 'active')

@section('content')
<h2 style="font-weight:900;color:#9A3412;margin-bottom:1.5rem"><i class="fas fa-brain" style="color:#F97316"></i> Predicciones Activas</h2>

<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #F97316;">
    @if($predicciones->count() > 0)
    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead>
            <tr style="background:#FFF7ED">
                <th style="padding:0.6rem;text-align:left;color:#9A3412;">Caso</th>
                <th style="padding:0.6rem;color:#9A3412;">Paciente</th>
                <th style="padding:0.6rem;color:#9A3412;">Riesgo</th>
                <th style="padding:0.6rem;color:#9A3412;">Prediccion</th>
                <th style="padding:0.6rem;color:#9A3412;">Escenario</th>
                <th style="padding:0.6rem;color:#9A3412;">Estado</th>
                <th style="padding:0.6rem;color:#9A3412;">Fecha</th>
            </tr>
        </thead>
        <tbody>
        @foreach($predicciones as $p)
        <?php
            $datos = json_decode($p->datos_entrada, true) ?: [];
            $riesgoPct = round($p->probabilidad * 100, 1);
            $escenario = $datos['escenario'] ?? 'N/A';
            $fecha = is_object($p->created_at) ? $p->created_at->format('d/m H:i') : substr($p->created_at, 0, 16);
        ?>
        <tr style="border-bottom:1px solid #FFF0E0;">
            <td style="padding:0.5rem;font-weight:700;">#{{ $p->id }}</td>
            <td style="padding:0.5rem;color:#57534E;">{{ $p->patient_id ?? 'N/A' }}</td>
            <td style="padding:0.5rem;">
                <span style="background:{{ $riesgoPct >= 70 ? '#FEE2E2' : ($riesgoPct >= 40 ? '#FFEDD5' : '#F0FDF4') }};color:{{ $riesgoPct >= 70 ? '#DC2626' : ($riesgoPct >= 40 ? '#EA580C' : '#16A34A') }};padding:0.15rem 0.5rem;border-radius:4px;font-size:0.75rem;font-weight:800;">{{ $riesgoPct }}%</span>
            </td>
            <td style="padding:0.5rem;"><span style="background:{{ $p->prediccion === 'alto_riesgo' ? '#FEE2E2' : '#F0FDF4' }};color:{{ $p->prediccion === 'alto_riesgo' ? '#DC2626' : '#16A34A' }};padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{ $p->prediccion === 'alto_riesgo' ? 'Alto Riesgo' : 'Bajo Riesgo' }}</span></td>
            <td style="padding:0.5rem;color:#78716C;font-size:0.8rem;">{{ $escenario }}</td>
            <td style="padding:0.5rem;"><span style="background:#FFF7ED;color:#EA580C;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{ $p->estado }}</span></td>
            <td style="padding:0.5rem;color:#A8A29E;font-size:0.75rem;">{{ $fecha }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#D97706;padding:2rem;font-weight:700;"><i class="fas fa-info-circle"></i> Sin predicciones activas. Usa el Simulador IA para crear una.</p>
    @endif
</div>
@endsection
