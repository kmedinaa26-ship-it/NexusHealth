@extends('medico.layout')
@section('title', 'Mapa de Camas')
@section('nav-camas', 'active')
@section('content')
@php $role = session('doctor_profile', 'Médico C'); @endphp
<div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:1.5rem;">
    <h3 style="font-weight:800;"><i class="fas fa-bed" style="color:#EA580C;"></i> Mapa de Camas del Hospital</h3>
    <p style="font-size:0.85rem; color:#78716C; margin-top:0.25rem;">Estado en tiempo real de todas las camas</p>
</div>

@php
 $pisos = $camas->groupBy('floor');
@endphp

@foreach($pisos as $floor => $camasPiso)
<div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:1rem;">
    <h4 style="font-weight:800; margin-bottom:1rem; color:#EA580C;"><i class="fas fa-layer-group"></i> Piso {{ $floor }}</h4>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:0.75rem;">
        @foreach($camasPiso as $c)
        @php
        $ocupada = $c->status === 'Ocupada';
        $paciente = '';
        if($ocupada) {
            $hosp = $hospitalizaciones->first(fn($h) => $h->bed_id == $c->id);
            if($hosp && $hosp->triage) $paciente = $hosp->triage->patient_name;
        }
        @endphp
        <div style="background:{{ $ocupada ? '#FEF2F2' : '#F0FDF4' }}; border:2px solid {{ $ocupada ? '#FCA5A5' : '#BBF7D0' }}; border-radius:10px; padding:0.75rem; text-align:center;">
            <div style="font-weight:800; font-size:0.9rem;">H{{ $c->room_number }}-C{{ $c->bed_number }}</div>
            <div style="font-size:0.7rem; color:#78716C;">{{ $c->type ?? 'General' }}</div>
            <div style="margin-top:0.5rem;">
                @if($ocupada)
                <span style="background:#DC2626; color:white; padding:0.15rem 0.5rem; border-radius:10px; font-size:0.7rem; font-weight:700;">Ocupada</span>
                <div style="font-size:0.7rem; color:#991B1B; margin-top:0.25rem; font-weight:600;">{{ $paciente }}</div>
                @else
                <span style="background:#2D9E6A; color:white; padding:0.15rem 0.5rem; border-radius:10px; font-size:0.7rem; font-weight:700;">Disponible</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
@endsection
