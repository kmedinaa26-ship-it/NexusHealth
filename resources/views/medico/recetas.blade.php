@extends('medico.layout')
@section('title', 'Recetas Médicas')
@section('nav-recetas', 'active')

@section('content')
@php $isA = $role === 'Médico A'; $isC = $role === 'Médico C'; @endphp

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
    <!-- Formulario de receta -->
    <div style="background:white; padding:1.25rem; border-radius:10px; border:1px solid #E7E5E4;">
        <h4 style="font-weight:800; margin-bottom:1rem; color:#EA580C;"><i class="fas fa-prescription"></i> Nueva Receta</h4>
        <form method="POST" action="{{ route('medico.storeReceta') }}">
            @csrf
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; font-weight:700; color:#78716C;">Paciente</label>
                <select name="triage_id" style="width:100%; padding:0.6rem; border:1px solid #E7E5E4; border-radius:6px; font-size:0.85rem;" required>
                    <option value="">Seleccionar paciente</option>
                    @foreach($pacientes as $p)
                    <option value="{{ $p->id }}">{{ $p->patient_name }} ({{ $p->triage_level }})</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; font-weight:700; color:#78716C;">Medicamento</label>
                <select name="medicamento_id" style="width:100%; padding:0.6rem; border:1px solid #E7E5E4; border-radius:6px; font-size:0.85rem;" required>
                    <option value="">Seleccionar</option>
                    @foreach($medicamentos as $m)
                    <option value="{{ $m->id }}" @if($m->required_level === 'A') style="color:#DC2626;font-weight:700" @endif>
                        {{ $m->name }} {{ $m->presentation }} (Stock: {{ $m->stock }})
                        @if($m->required_level === 'A') 🔒@endif
                    </option>
                    @endforeach
                </select>
                @if($isC)<p style="font-size:0.65rem; color:#EA580C; margin-top:0.25rem;">⚠ Solo medicamentos básicos disponibles</p>@endif
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.7rem; font-weight:700; color:#78716C;">Dosis</label>
                    <input type="text" name="dosis" style="width:100%; padding:0.5rem; border:1px solid #E7E5E4; border-radius:6px; font-size:0.8rem;" placeholder="500mg" required>
                </div>
                <div>
                    <label style="font-size:0.7rem; font-weight:700; color:#78716C;">Frecuencia</label>
                    <select name="frecuencia" style="width:100%; padding:0.5rem; border:1px solid #E7E5E4; border-radius:6px; font-size:0.8rem;" required>
                        <option>Cada 8 horas</option><option>Cada 12 horas</option><option>Cada 24 horas</option>
                        <option>Cada 6 horas</option><option>Cada 4 horas</option><option>Única dosis</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:0.7rem; font-weight:700; color:#78716C;">Duración</label>
                    <input type="text" name="duracion" style="width:100%; padding:0.5rem; border:1px solid #E7E5E4; border-radius:6px; font-size:0.8rem;" placeholder="7 días" required>
                </div>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.7rem; font-weight:700; color:#78716C;">Indicaciones</label>
                <textarea name="indicaciones" rows="2" style="width:100%; padding:0.5rem; border:1px solid #E7E5E4; border-radius:6px; font-size:0.8rem;" placeholder="Tomar con alimentos..."></textarea>
            </div>
            <button type="submit" style="width:100%; padding:0.6rem; background:#EA580C; color:white; border:none; border-radius:6px; font-weight:800; cursor:pointer; font-size:0.85rem;">
                <i class="fas fa-paper-plane"></i> Enviar a Farmacia
            </button>
        </form>
    </div>

    <!-- Mis recetas recientes -->
    <div style="background:white; padding:1.25rem; border-radius:10px; border:1px solid #E7E5E4;">
        <h4 style="font-weight:800; margin-bottom:1rem; color:#EA580C;"><i class="fas fa-history"></i> Mis Recetas Recientes</h4>
        @if($misRecetas->isEmpty())
        <p style="color:#A8A29E; text-align:center; padding:1.5rem; font-size:0.85rem;">Sin recetas emitidas</p>
        @else
        @foreach($misRecetas as $r)
        @php
        $med = \App\Models\Medication::find($r->medication_id);
        $triage = \App\Models\Triage::find($r->triage_id);
        @endphp
        <div style="padding:0.6rem; border-radius:6px; margin-bottom:0.4rem; border-left:3px solid {{ $r->status === 'Pendiente' ? '#F97316' : '#2D9E6A' }}; background:#FFF7ED;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-weight:700; font-size:0.8rem;">{{ $med->name ?? 'Medicamento' }}</span>
                <span style="background:{{ $r->status === 'Pendiente' ? '#FFF7ED' : '#F0FDF4' }}; color:{{ $r->status === 'Pendiente' ? '#EA580C' : '#166534' }}; padding:0.1rem 0.4rem; border-radius:8px; font-size:0.6rem; font-weight:700;">{{ $r->status }}</span>
            </div>
            <div style="font-size:0.7rem; color:#57534E;">{{ $triage->patient_name ?? 'Paciente' }} - {{ $r->dosis }} / {{ $r->frecuencia }} / {{ $r->duracion }}</div>
            <div style="font-size:0.6rem; color:#A8A29E;">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</div>
        </div>
        @endforeach
        @endif
    </div>
</div>
@endsection
