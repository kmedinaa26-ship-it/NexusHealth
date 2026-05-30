@extends('superadmin.layout')
@section('title', 'Centro de Control de Urgencias')
@section('nav-urgencias', 'active')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h3 style="font-weight: 800; color: #1E1A17;">Sala de Urgencias - Triage Manchester</h3>
        <p style="color: #736860; font-size: 0.85rem;">Clasificación, asignación y derivación en tiempo real.</p>
    </div>
    <button onclick="document.getElementById('modal-add-patient').style.display='flex'" style="background: #C7291C; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 700; cursor: pointer;">
        <i class="fas fa-ambulance"></i> Ingreso de Urgencia
    </button>
</div>

<div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 2rem;">
    @php $colors = ['Rojo' => ['bg'=>'#FFE0DC','border'=>'#C7291C','txt'=>'#8C1A11'], 'Naranja'=>['bg'=>'#FFF5EB','border'=>'#FF8C42','txt'=>'#9a3412'], 'Amarillo'=>['bg'=>'#FFFCE8','border'=>'#F59E0B','txt'=>'#92400E'], 'Verde'=>['bg'=>'#EBF9F2','border'=>'#2D9E6A','txt'=>'#065F46'], 'Azul'=>['bg'=>'#EFF6FF','border'=>'#3B82F6','txt'=>'#1E3A8A']]; @endphp
    @foreach($colors as $color => $styles)
        @php $patients = $triages->where('triage_level', $color); @endphp
        <div style="background: {{ $styles['bg'] }}; border: 2px solid {{ $styles['border'] }}; border-radius: 12px; padding: 1rem;">
            <h4 style="font-weight: 800; color: {{ $styles['txt'] }}; text-align: center; margin-bottom: 1rem; text-transform: uppercase; border-bottom: 2px solid {{ $styles['border'] }};">{{ $color }}</h4>
            @foreach($patients as $patient)
            <div style="background: white; padding: 0.75rem; border-radius: 6px; margin-bottom: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #1E1A17;">{{ $patient->patient_name }} <span style="font-size:0.7rem; color:#736860;">({{ $patient->age }}a)</span></div>
                <div style="font-size: 0.75rem; color: #736860; margin: 0.25rem 0;">{{ $patient->symptoms }}</div>
                
                @if($patient->vitals_ta)
                <div style="background:#F4F6F8; padding:0.3rem; border-radius:4px; font-size:0.7rem; font-family:monospace; margin-top:0.3rem; border-left: 3px solid #2D9E6A;">
                    TA: {{ $patient->vitals_ta }} | FC: {{ $patient->vitals_fc }} | Temp: {{ $patient->vitals_temp }} | SpO2: {{ $patient->vitals_spo2 }}%
                </div>
                @endif

                <div style="display:flex; gap:5px; margin-top:0.5rem;">
                    @if(!$patient->vitals_ta)
                    <button onclick="openVitalsModal({{ $patient->id }})" style="flex:1; background:#2D9E6A; color:white; border:none; padding:0.3rem; border-radius:4px; font-size:0.7rem; cursor:pointer;"><i class="fas fa-heartbeat"></i> Signos</button>
                    @endif
                    @if(!$patient->is_derived && $patient->status != 'Dado de Alta')
                    <button onclick="openDerivationModal({{ $patient->id }})" style="flex:1; background:#FF8C42; color:white; border:none; padding:0.3rem; border-radius:4px; font-size:0.7rem; cursor:pointer;"><i class="fas fa-route"></i> Derivar</button>
                    @endif
                    @if($patient->is_derived)
                    <a href="{{ route('superadmin.paseSalida', $patient->id) }}" target="_blank" style="flex:1; background:#1E1A17; color:white; text-decoration:none; text-align:center; padding:0.3rem; border-radius:4px; font-size:0.7rem;"><i class="fas fa-file-pdf"></i> Pase</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endforeach
</div>

<!-- Modal Tomar Signos Vitales -->
<div id="modal-vitals" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:100; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:12px; width:400px;">
        <h3 style="font-weight:800; margin-bottom:1rem;">Signos Vitales</h3>
        <form id="vitals-form" action="" method="POST">
            @csrf @method('PUT')
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1rem;">
                <div><label style="font-size:0.8rem; font-weight:700; color:#736860;">TA (ej. 120/80)</label><input type="text" name="vitals_ta" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
                <div><label style="font-size:0.8rem; font-weight:700; color:#736860;">FC (lpm)</label><input type="text" name="vitals_fc" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1rem;">
                <div><label style="font-size:0.8rem; font-weight:700; color:#736860;">Temp (°C)</label><input type="text" name="vitals_temp" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
                <div><label style="font-size:0.8rem; font-weight:700; color:#736860;">SpO2 (%)</label><input type="text" name="vitals_spo2" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
            </div>
            <div style="margin-bottom:1rem;"><label style="font-size:0.8rem; font-weight:700; color:#736860;">Asignar a Área</label>
                <select name="assigned_area" style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;">
                    <option value="Urgencias">Urgencias</option>
                    <option value="Médico General">Médico General</option>
                    <option value="UCI">UCI</option>
                    <option value="Quirófano">Quirófano</option>
                </select>
            </div>
            <button type="submit" style="width:100%; padding:0.6rem; background:#2D9E6A; color:white; border:none; border-radius:6px; font-weight:700; cursor:pointer;">Guardar Signos y Asignar</button>
        </form>
    </div>
</div>

<!-- Modal Derivación (Mapa) -->
<div id="modal-derive" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:100; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:12px; width:600px;">
        <h3 style="font-weight:800; margin-bottom:1rem; color:#C7291C;">Derivación de Paciente - Sin Recursos</h3>
        <p style="font-size:0.85rem; color:#736860; margin-bottom:1rem;">No hay camas/recursos disponibles. Se generará Pase de Salida para hospital cercano.</p>
        <div id="map" style="height: 250px; width: 100%; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #E5E7EB;"></div>
        <form id="derive-form" action="" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:1rem;"><label style="font-size:0.8rem; font-weight:700; color:#736860;">Hospital de Destino</label>
                <select name="derivation_hospital" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;">
                    <option value="Hospital General de México">Hospital General de México (2.3 km)</option>
                    <option value="Instituto Nacional de Nutrición">Instituto Nacional de Nutrición (3.8 km)</option>
                    <option value="Hospital Angeles del Pedregal">Hospital Angeles del Pedregal (5.1 km)</option>
                </select>
            </div>
            <button type="submit" style="width:100%; padding:0.6rem; background:#C7291C; color:white; border:none; border-radius:6px; font-weight:700; cursor:pointer;">Derivar y Generar Pase de Salida</button>
        </form>
    </div>
</div>

<!-- Scripts de Modales y Mapa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
function openVitalsModal(patientId) {
    document.getElementById('vitals-form').action = '/superadmin/urgencias/' + patientId + '/vitals';
    document.getElementById('modal-vitals').style.display = 'flex';
}
function openDerivationModal(patientId) {
    document.getElementById('derive-form').action = '/superadmin/urgencias/' + patientId + '/derive';
    document.getElementById('modal-derive').style.display = 'flex';
    setTimeout(function() {
        var map = L.map('map').setView([19.4326, -99.1332], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
        L.marker([19.4326, -99.1332]).addTo(map).bindPopup('HealthNexus Hospital (Origen)').openPopup();
        L.marker([19.4108, -99.1296]).addTo(map).bindPopup('Hospital General de México');
        L.marker([19.3950, -99.1750]).addTo(map).bindPopup('Hospital Angeles del Pedregal');
    }, 200);
}
</script>
@endsection
