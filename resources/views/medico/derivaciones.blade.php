@extends('medico.layout')
@section('title', 'Derivaciones')
@section('nav-derivaciones', 'active')
@section('content')
<div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); margin-bottom:1.5rem;">
    <h3 style="font-weight:800;"><i class="fas fa-ambulance" style="color:#DC2626;"></i> Derivaciones</h3>
</div>
<div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); margin-bottom:1.5rem;">
    <form method="POST" action="{{ route('medico.storeDerivacion') }}">
        @csrf
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div>
                <label style="font-size:0.8rem; font-weight:700; color:#64748B;">Paciente</label>
                <select name="triage_id" style="width:100%; padding:0.75rem; border:1px solid #E2E8F0; border-radius:8px;" required>
                    <option value="">Seleccionar</option>
                    @foreach($pacientes as $p)<option value="{{ $p->id }}">{{ $p->patient_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label style="font-size:0.8rem; font-weight:700; color:#64748B;">Hospital Destino</label>
                <input type="text" name="hospital_destino" style="width:100%; padding:0.75rem; border:1px solid #E2E8F0; border-radius:8px;" required placeholder="Hospital General...">
            </div>
        </div>
        <div style="margin-bottom:1rem;">
            <label style="font-size:0.8rem; font-weight:700; color:#64748B;">Motivo</label>
            <textarea name="motivo" rows="2" style="width:100%; padding:0.75rem; border:1px solid #E2E8F0; border-radius:8px;" required></textarea>
        </div>
        <button type="submit" style="padding:0.75rem 2rem; background:#DC2626; color:white; border:none; border-radius:8px; font-weight:800; cursor:pointer;">
            <i class="fas fa-ambulance"></i> Registrar Derivación
        </button>
    </form>
</div>
@endsection
