@extends('medico.layout')
@section('title', 'Solicitar Servicios')
@section('nav-servicios', 'active')
@section('content')
@php $role = session('doctor_profile', 'Médico C'); @endphp
<div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:1.5rem;">
    <h3 style="font-weight:800;"><i class="fas fa-concierge-bell" style="color:#EA580C;"></i> Solicitar Servicio al Sistema</h3>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
    <div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h4 style="font-weight:800; margin-bottom:1rem;">Nueva Solicitud</h4>
        <form method="POST" action="{{ route('medico.solicitarServicio') }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:700; color:#78716C;">Paciente</label>
                <select name="triage_id" style="width:100%; padding:0.75rem; border:1px solid #E7E5E4; border-radius:8px;" required>
                    <option value="">Seleccionar paciente</option>
                    @foreach($pacientes as $p)<option value="{{ $p->id }}">{{ $p->patient_name }} ({{ $p->triage_level }})</option>@endforeach
                </select>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:700; color:#78716C;">Tipo de Servicio</label>
                <select name="tipo" style="width:100%; padding:0.75rem; border:1px solid #E7E5E4; border-radius:8px;" required>
                    <option value="">Seleccionar</option>
                    <option value="Farmacia">💊 Solicitud a Farmacia</option>
                    <option value="Laboratorio">🔬 Laboratorio Clínico</option>
                    <option value="Imagenología">📷 Imagenología</option>
                    <option value="Oxígeno">🫟 Oxígeno / Respiratorio</option>
                    <option value="Terapia">🏋️ Terapia Física</option>
                    <option value="Nutrición">🍎 Nutrición</option>
                    <option value="Trabajo Social">🤝 Trabajo Social</option>
                    <option value="Sangre">🩸 Banco de Sangre</option>
                </select>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:700; color:#78716C;">Prioridad</label>
                <select name="prioridad" style="width:100%; padding:0.75rem; border:1px solid #E7E5E4; border-radius:8px;" required>
                    <option value="Normal">Normal</option>
                    <option value="Urgente">Urgente</option>
                    <option value="Crítica">Crítica</option>
                </select>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.8rem; font-weight:700; color:#78716C;">Descripción</label>
                <textarea name="descripcion" rows="3" style="width:100%; padding:0.75rem; border:1px solid #E7E5E4; border-radius:8px;" placeholder="Detalle de lo que necesitas..."></textarea>
            </div>
            <button type="submit" style="width:100%; padding:0.75rem; background:#EA580C; color:white; border:none; border-radius:8px; font-weight:800; cursor:pointer; font-size:0.95rem;">
                <i class="fas fa-paper-plane"></i> Enviar Solicitud
            </button>
        </form>
    </div>

    <div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
        <h4 style="font-weight:800; margin-bottom:1rem;">Mis Solicitudes Recientes</h4>
        @if($solicitudes->isEmpty())
        <p style="color:#A8A29E; text-align:center; padding:2rem;">Sin solicitudes aún</p>
        @else
        @foreach($solicitudes as $s)
        <div style="padding:0.75rem; border-radius:8px; margin-bottom:0.5rem; border-left:4px solid {{ $s->prioridad === 'Crítica' ? '#DC2626' : ($s->prioridad === 'Urgente' ? '#EA580C' : '#F97316') }}; background:#FFFBEB;">
            <div style="display:flex; justify-content:space-between;">
                <span style="font-weight:800; font-size:0.85rem;">{{ $s->tipo }}</span>
                <span style="background:{{ $s->status === 'Pendiente' ? '#FEF2F2' : '#F0FDF4' }}; color:{{ $s->status === 'Pendiente' ? '#DC2626' : '#166534' }}; padding:0.1rem 0.5rem; border-radius:10px; font-size:0.7rem; font-weight:700;">{{ $s->status }}</span>
            </div>
            <div style="font-size:0.8rem; color:#57534E; margin-top:0.25rem;">{{ $s->descripcion ?? 'Sin descripción' }}</div>
            <div style="font-size:0.7rem; color:#A8A29E;">{{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}</div>
        </div>
        @endforeach
        @endif
    </div>
</div>
@endsection
