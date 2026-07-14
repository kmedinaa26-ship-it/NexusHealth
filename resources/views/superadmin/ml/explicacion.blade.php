@extends('superadmin.layout')
@section('title', 'Explicabilidad ML')
@section('nav-ml-explicacion', 'active')

@section('content')
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:0.5rem;"><i class="fas fa-search-plus" style="color:#7C3AED"></i> Explicabilidad por Caso</h3>
    <p style="font-size:0.8rem;color:#78716C;margin-bottom:1.5rem;">Selecciona un caso para ver el desglose de variables (Feature Importance)</p>
    @if($predicciones->count() > 0)
    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead><tr style="background:#F5F3FF"><th style="padding:0.6rem;text-align:left;color:#7C3AED;">Caso</th><th style="padding:0.6rem;color:#7C3AED;">Paciente</th><th style="padding:0.6rem;color:#7C3AED;">Riesgo</th><th style="padding:0.6rem;color:#7C3AED;">Prediccion</th><th style="padding:0.6rem;color:#7C3AED;">Estado</th><th style="padding:0.6rem;color:#7C3AED;">Accion</th></tr></thead>
        <tbody>
        @foreach($predicciones as $p)
        <tr style="border-bottom:1px solid #F3F4F6;">
            <td style="padding:0.5rem;font-weight:700;">#{{ $p->id }}</td>
            <td style="padding:0.5rem;color:#57534E;">{{ $p->patient_id ?? 'N/A' }}</td>
            <td style="padding:0.5rem;font-weight:800;color:{{ $p->probabilidad >= 0.5 ? '#DC2626' : '#16A34A' }};">{{ round($p->probabilidad * 100, 1) }}%</td>
            <td style="padding:0.5rem;"><span style="background:{{ $p->prediccion === 'alto_riesgo' ? '#FEE2E2' : '#F0FDF4' }};color:{{ $p->prediccion === 'alto_riesgo' ? '#DC2626' : '#16A34A' }};padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{ $p->prediccion }}</span></td>
            <td style="padding:0.5rem;"><span style="background:#FFF7ED;color:#EA580C;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{ $p->estado }}</span></td>
            <td style="padding:0.5rem;"><a href="{{ route('superadmin.ml.explicacion.show', $p->id) }}" style="padding:0.3rem 0.8rem;background:#7C3AED;color:white;border-radius:6px;font-size:0.75rem;font-weight:700;text-decoration:none;"><i class="fas fa-search"></i> Ver</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#78716C;padding:2rem;">Sin predicciones</p>
    @endif
</div>
@endsection
