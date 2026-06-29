@extends('medico.layout')

@section('title', 'Consulta Médica')
@section('nav-consulta', 'active')

@section('content')
<div style="background:#1E1A17; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.1); margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between;">
    <h3 style="font-weight:800; color:white; margin:0;"><i class="fas fa-stethoscope" style="color:#F05A4E; margin-right:0.5rem;"></i> Consulta Médica</h3>
</div>

<div style="display:grid; grid-template-columns:1fr 1.5fr; gap:1.5rem;">
    
    <!-- Columna 1: Pacientes en Espera -->
    <div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04);">
        <h4 style="font-weight:800; margin-bottom:1rem; color:#1E1A17; border-bottom:2px solid #F3F4F6; padding-bottom:0.5rem;">
            <i class="fas fa-user-injured" style="color:#2D9E6A;"></i> Pacientes en Espera
        </h4>
        
        @foreach($pacientes as $p)
        @php $tc = $p->triage_level === 'Rojo' ? '#C7291C' : ($p->triage_level === 'Amarillo' ? '#FF8C42' : '#2D9E6A'); @endphp
        <div id="patient-card-{{ $p->id }}" style="padding:1rem; border-radius:8px; margin-bottom:0.75rem; border-left:5px solid {{ $tc }}; background:#F9FAFB; cursor:pointer; transition: all 0.2s;" onclick="selectPatient({{ $p->id }}, '{{ $p->patient_name }}', '{{ $p->triage_level }}')">
            <div style="font-weight:800; color:#1E1A17;">{{ $p->patient_name }}</div>
            <div style="font-size:0.8rem; color:#736860; margin-top:0.25rem;">
                Triage: <span style="color:{{ $tc }}; font-weight:800;">{{ $p->triage_level }}</span> | Edad: {{ $p->age ?? 'N/A' }}
            </div>
            <div style="font-size:0.8rem; color:#736860; margin-top:0.25rem;">
                Motivo: {{ $p->symptoms ?? 'Sin datos' }}
            </div>
        </div>
        @endforeach

        @if($pacientes->isEmpty())
        <div style="text-align:center; padding:2rem; color:#736860;">
            <i class="fas fa-check-circle" style="font-size:2rem; color:#2D9E6A; margin-bottom:0.5rem; display:block;"></i>
            No hay pacientes en espera.
        </div>
        @endif
    </div>

    <!-- Columna 2: Formulario de Consulta -->
    <div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04);">
        <h4 style="font-weight:800; margin-bottom:1.5rem; color:#1E1A17; border-bottom:2px solid #F3F4F6; padding-bottom:0.5rem;">
            <i class="fas fa-file-medical" style="color:#F05A4E;"></i> Registrar Consulta
        </h4>

        <form method="POST" action="{{ url('/medico/consulta') }}">
            @csrf
            <input type="hidden" name="paciente_id" id="paciente_id">

            <div style="margin-bottom:1.25rem;">
                <label style="font-size:0.85rem; font-weight:700; color:#1E1A17; display:block; margin-bottom:0.3rem;">Paciente Seleccionado</label>
                <div id="selected-patient" style="padding:1rem; background:#F1F5F9; border-radius:8px; font-weight:700; color:#736860; border:1px dashed #CBD5E1; text-align:center;">
                    Haz clic en un paciente de la lista
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="font-size:0.85rem; font-weight:700; color:#1E1A17; display:block; margin-bottom:0.3rem;">Diagnóstico</label>
                <textarea name="diagnostico" rows="3" style="width:100%; padding:0.85rem; border:1px solid #E5E7EB; border-radius:8px; font-size:0.95rem; resize:vertical; background:#FAFAFA;" required></textarea>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="font-size:0.85rem; font-weight:700; color:#1E1A17; display:block; margin-bottom:0.3rem;">Tratamiento / Plan</label>
                <textarea name="tratamiento" rows="3" style="width:100%; padding:0.85rem; border:1px solid #E5E7EB; border-radius:8px; font-size:0.95rem; resize:vertical; background:#FAFAFA;" required></textarea>
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="font-size:0.85rem; font-weight:700; color:#1E1A17; display:block; margin-bottom:0.3rem;">Notas Adicionales</label>
                <textarea name="notas" rows="2" style="width:100%; padding:0.85rem; border:1px solid #E5E7EB; border-radius:8px; font-size:0.95rem; resize:vertical; background:#FAFAFA;"></textarea>
            </div>

            <div style="background:#E6F7F0; border-left:4px solid #2D9E6A; padding:0.75rem 1rem; border-radius:6px; margin-bottom:1.5rem; font-size:0.85rem; color:#1E1A17;">
                <i class="fas fa-cash-register" style="color:#2D9E6A; margin-right:0.5rem;"></i> 
                <strong>Auto-Cobro:</strong> Al guardar, se cobrará el costo de la consulta a la cuenta abierta del paciente.
            </div>

            <button type="submit" style="width:100%; padding:1rem; background:#F05A4E; color:white; border:none; border-radius:8px; font-weight:800; cursor:pointer; font-size:1rem; transition: background 0.2s;">
                <i class="fas fa-check-circle"></i> Guardar Consulta y Cobrar
            </button>
        </form>
    </div>
</div>

<script>
function selectPatient(id, name, level) {
    document.getElementById('paciente_id').value = id;
    const selectedDiv = document.getElementById('selected-patient');
    selectedDiv.textContent = name + " (Triage: " + level + ")";
    selectedDiv.style.background = "#E6F7F0";
    selectedDiv.style.color = "#1E1A17";
    selectedDiv.style.border = "2px solid #2D9E6A";

    document.querySelectorAll('[id^="patient-card-"]').forEach(el => {
        el.style.background = "#F9FAFB";
        el.style.transform = "scale(1)";
    });
    const card = document.getElementById('patient-card-' + id);
    if(card) {
        card.style.background = "#FFF1F0"; 
        card.style.transform = "scale(1.02)";
    }
}
</script>
@endsection
