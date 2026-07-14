@extends('superadmin.layout')
@section('title', 'Explicabilidad Caso #' . ($prediccion->id ?? ''))
@section('nav-ml-explicacion', 'active')

@section('content')
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <div>
            <h3 style="font-weight:900;color:#1E1A17;"><i class="fas fa-search-plus" style="color:#7C3AED"></i> Explicabilidad - Caso #{{ $prediccion->id ?? '' }}</h3>
            @if($prediccion)
            <p style="font-size:0.8rem;color:#78716C;">Paciente: <strong>{{ $prediccion->patient_id ?? 'N/A' }}</strong> | Riesgo: <strong style="color:#DC2626;">{{ round($prediccion->probabilidad * 100, 1) }}%</strong> | Prediccion: <strong>{{ $prediccion->prediccion }}</strong></p>
            @endif
        </div>
        <a href="{{ route('superadmin.ml.explicacion') }}" style="padding:0.4rem 0.8rem;background:#F5F3FF;color:#7C3AED;border:1px solid #DDD6FE;border-radius:6px;font-size:0.8rem;font-weight:700;text-decoration:none;"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
    @if($variables->count() > 0)
    @php $maxPeso = $variables->first()->peso; @endphp
    @foreach($variables as $v)
    <?php $pct = $maxPeso > 0 ? ($v->peso / $maxPeso * 100) : 0; $pctLabel = round($v->peso * 100, 1); ?>
    <div style="margin-bottom:1rem;">
        <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:0.2rem;">
            <span style="font-weight:700;color:#1E1A17;">{{ $v->variable }}</span>
            <span style="font-weight:800;color:#7C3AED;">{{ $pctLabel }}% <span style="color:#A8A29E;font-weight:600;">({{ $v->impacto }})</span></span>
        </div>
        <div style="height:10px;background:#F3F4F6;border-radius:5px;overflow:hidden;">
            <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#7C3AED,#A78BFA);border-radius:5px;"></div>
        </div>
    </div>
    @endforeach
    @else
    <div style="background:#F5F3FF;border-radius:10px;padding:2rem;text-align:center;">
        <i class="fas fa-info-circle" style="font-size:1.5rem;color:#7C3AED;margin-bottom:0.5rem;"></i>
        <p style="font-weight:700;color:#5B21B6;">Sin datos de explicabilidad para este caso</p>
        <p style="font-size:0.8rem;color:#78716C;">Los datos de explicabilidad se guardan al crear una prediccion desde el simulador.</p>
    </div>
    @endif
</div>
@endsection
