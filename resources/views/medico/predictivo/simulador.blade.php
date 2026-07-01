@extends('medico.layout')
@section('title', 'Simulador Clinico')
@section('nav-simulador', 'active')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div style="display:grid;grid-template-columns:2fr 3fr;gap:1.5rem;">
    
    <div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #F97316;">
        <h3 style="font-weight:900;color:#9A3412;margin-bottom:1.2rem"><i class="fas fa-flask" style="color:#F97316"></i> Parametros Clinicos</h3>
        
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">ID Paciente (Referencia)</label>
            <input type="number" id="patient_id" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;" placeholder="Ej. 123">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Edad</label>
                <input type="number" id="edad" value="65" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Presion Arterial</label>
                <input type="number" id="presion" value="140" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Saturacion O2 (%)</label>
                <input type="number" id="saturacion" value="90" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Frecuencia Cardiaca</label>
                <input type="number" id="fc" value="95" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Temperatura (C)</label>
                <input type="number" id="temp" value="38.5" step="0.1" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Glasgow</label>
                <input type="number" id="glasgow" value="12" min="3" max="15" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;">
            </div>
        </div>
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Diabetes</label>
            <select id="diabetes" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;background:white;">
                <option value="0">No</option>
                <option value="1" selected>Si</option>
            </select>
        </div>
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Hipertension</label>
            <select id="hipertension" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;background:white;">
                <option value="0">No</option>
                <option value="1" selected>Si</option>
            </select>
        </div>
        <div style="margin-bottom:1.5rem;">
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Escenario: Que pasa si...</label>
            <select id="escenario" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;background:white;">
                <option value="normal">Tratamiento actual</option>
                <option value="uci">Ingreso a UCI</option>
                <option value="quirurgico">Intervencion quirurgica</option>
                <option value="ventilacion">Ventilacion mecanica</option>
            </select>
        </div>
        
        <button onclick="simular()" style="width:100%;padding:0.7rem;background:linear-gradient(135deg,#F97316,#EA580C);color:white;border:none;border-radius:8px;font-weight:800;font-size:0.85rem;cursor:pointer;margin-bottom:0.5rem;">
            <i class="fas fa-play"></i> SIMULAR ESCENARIO
        </button>
        
        <button id="btnGuardar" onclick="guardarPrediccion()" style="width:100%;padding:0.7rem;background:linear-gradient(135deg,#16A34A,#15803D);color:white;border:none;border-radius:8px;font-weight:800;font-size:0.85rem;cursor:pointer;display:none;">
            <i class="fas fa-save"></i> GUARDAR PREDICCION
        </button>
        <p style="text-align:center;font-size:0.65rem;color:#A8A29E;margin-top:0.5rem;">Simular = solo vista | Guardar = crea el caso</p>
    </div>
    
    <div>
        <div id="resultado_riesgo" style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #DC2626;margin-bottom:1.5rem;display:none;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 style="font-weight:900;color:#9A3412"><i class="fas fa-heart-pulse" style="color:#DC2626"></i> Resultado de Simulacion</h3>
                <span id="escenario_badge" style="background:#FFEDD5;color:#9A3412;padding:0.2rem 0.6rem;border-radius:10px;font-size:0.7rem;font-weight:800">Tratamiento actual</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                <div style="text-align:center;padding:1rem;background:#FEF2F2;border-radius:10px;">
                    <div id="riesgo_valor" style="font-size:2.5rem;font-weight:900;color:#DC2626;">--</div>
                    <div style="font-size:0.7rem;font-weight:700;color:#991B1B;">Riesgo Mortalidad</div>
                </div>
                <div style="text-align:center;padding:1rem;background:#FFF7ED;border-radius:10px;">
                    <div id="dias_estimados" style="font-size:2.5rem;font-weight:900;color:#EA580C;">--</div>
                    <div style="font-size:0.7rem;font-weight:700;color:#9A3412;">Dias Estancia</div>
                </div>
                <div style="text-align:center;padding:1rem;background:#F0FDF4;border-radius:10px;">
                    <div id="costo_estimado" style="font-size:2.5rem;font-weight:900;color:#166534;">--</div>
                    <div style="font-size:0.7rem;font-weight:700;color:#166534;">Costo Est. ($)</div>
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.7rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">
                    <span>Bajo</span><span>Medio</span><span>Alto</span><span>Critico</span>
                </div>
                <div style="height:12px;background:#E7E5E4;border-radius:6px;overflow:hidden;">
                    <div id="barra_riesgo" style="height:100%;width:0%;border-radius:6px;transition:width 0.8s ease;"></div>
                </div>
            </div>
            <div id="recomendacion" style="background:#FFEDD5;border:1px solid #FDBA74;border-radius:8px;padding:0.8rem;font-size:0.8rem;color:#9A3412;font-weight:600;">
                <i class="fas fa-lightbulb" style="color:#F97316"></i> Ingresa parametros y presiona Simular
            </div>
        </div>
        
        <div id="explicabilidad" style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #F97316;display:none;">
            <h3 style="font-weight:900;color:#9A3412;margin-bottom:1rem"><i class="fas fa-chart-bar" style="color:#F97316"></i> Desglose de Variables</h3>
            <div id="barras_variables"></div>
        </div>
        
        <div id="estado_inicial" style="background:white;border-radius:14px;padding:3rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #E7E5E4;text-align:center;">
            <i class="fas fa-flask-vial" style="font-size:3rem;color:#D6D3D1;margin-bottom:1rem;"></i>
            <h3 style="font-weight:800;color:#78716C;margin-bottom:0.5rem;">Sandbox Clinico</h3>
            <p style="font-size:0.85rem;color:#A8A29E;max-width:400px;margin:0 auto;">Ajusta los parametros y selecciona un escenario.</p>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toastOk" style="position:fixed;bottom:2rem;right:2rem;background:#16A34A;color:white;padding:1rem 1.5rem;border-radius:10px;font-weight:700;font-size:0.85rem;box-shadow:0 4px 12px rgba(0,0,0,0.2);display:none;z-index:9999;">
    <i class="fas fa-check-circle"></i> <span id="toastOkMsg"></span>
</div>
<div id="toastErr" style="position:fixed;bottom:2rem;right:2rem;background:#DC2626;color:white;padding:1rem 1.5rem;border-radius:10px;font-weight:700;font-size:0.85rem;box-shadow:0 4px 12px rgba(0,0,0,0.2);display:none;z-index:9999;">
    <i class="fas fa-times-circle"></i> <span id="toastErrMsg"></span>
</div>



<script>
let ultimaSimulacion = null;

function simular() {
    const edad = parseInt(document.getElementById('edad').value) || 65;
    const presion = parseInt(document.getElementById('presion').value) || 120;
    const saturacion = parseInt(document.getElementById('saturacion').value) || 98;
    const fc = parseInt(document.getElementById('fc').value) || 80;
    const temp = parseFloat(document.getElementById('temp').value) || 37;
    const glasgow = parseInt(document.getElementById('glasgow').value) || 15;
    const diabetes = parseInt(document.getElementById('diabetes').value) || 0;
    const hipertension = parseInt(document.getElementById('hipertension').value) || 0;
    const escenario = document.getElementById('escenario').value;
    
    let riesgo = 5;
    riesgo += (edad > 70 ? 25 : edad > 60 ? 15 : edad > 50 ? 8 : 3);
    riesgo += (presion > 160 ? 20 : presion > 140 ? 12 : presion > 120 ? 5 : 0);
    riesgo += (saturacion < 85 ? 30 : saturacion < 90 ? 20 : saturacion < 95 ? 8 : 0);
    riesgo += (fc > 110 ? 15 : fc > 100 ? 8 : fc < 50 ? 10 : 0);
    riesgo += (temp > 39 ? 10 : temp > 38 ? 5 : 0);
    riesgo += (15 - glasgow) * 5;
    riesgo += diabetes ? 10 : 0;
    riesgo += hipertension ? 8 : 0;
    
    const escenarios = {
        'normal': { mod: 0, dias: 5, label: 'Tratamiento actual' },
        'uci': { mod: 15, dias: 12, label: 'Ingreso a UCI' },
        'quirurgico': { mod: 10, dias: 8, label: 'Intervencion quirurgica' },
        'ventilacion': { mod: 20, dias: 15, label: 'Ventilacion mecanica' }
    };
    const esc = escenarios[escenario];
    riesgo += esc.mod;
    riesgo = Math.min(Math.max(riesgo, 2), 98);
    const dias = Math.round(esc.dias * (riesgo / 50));
    const costo = Math.round(dias * 1800 + (escenario === 'uci' ? 8000 : 0) + (escenario === 'quirurgico' ? 15000 : 0) + (escenario === 'ventilacion' ? 12000 : 0));
    
    let colorRiesgo, colorBarra, bgColor, textColor, recomendacion;
    if (riesgo < 30) {
        colorRiesgo = '#16A34A'; colorBarra = 'linear-gradient(90deg,#16A34A,#22C55E)'; bgColor = '#F0FDF4'; textColor = '#166534';
        recomendacion = 'Riesgo bajo. Paciente estable.';
    } else if (riesgo < 60) {
        colorRiesgo = '#EA580C'; colorBarra = 'linear-gradient(90deg,#EA580C,#F97316)'; bgColor = '#FFF7ED'; textColor = '#9A3412';
        recomendacion = 'Riesgo moderado. Monitoreo cada 4 horas.';
    } else if (riesgo < 80) {
        colorRiesgo = '#DC2626'; colorBarra = 'linear-gradient(90deg,#DC2626,#EF4444)'; bgColor = '#FEF2F2'; textColor = '#991B1B';
        recomendacion = 'Riesgo alto. Considerar UCI.';
    } else {
        colorRiesgo = '#7C2D12'; colorBarra = 'linear-gradient(90deg,#7C2D12,#991B1B)'; bgColor = '#431407'; textColor = '#FED7AA';
        recomendacion = 'RIESGO CRITICO. Intervencion inmediata.';
    }
    
    document.getElementById('estado_inicial').style.display = 'none';
    document.getElementById('resultado_riesgo').style.display = 'block';
    document.getElementById('explicabilidad').style.display = 'block';
    document.getElementById('btnGuardar').style.display = 'block';
    
    document.getElementById('riesgo_valor').textContent = riesgo + '%';
    document.getElementById('riesgo_valor').style.color = colorRiesgo;
    document.getElementById('dias_estimados').textContent = dias;
    document.getElementById('costo_estimado').textContent = '$' + costo.toLocaleString();
    document.getElementById('escenario_badge').textContent = esc.label;
    const barra = document.getElementById('barra_riesgo');
    barra.style.width = riesgo + '%';
    barra.style.background = colorBarra;
    document.getElementById('resultado_riesgo').style.borderTopColor = colorRiesgo;
    document.getElementById('recomendacion').innerHTML = '<i class="fas fa-lightbulb" style="color:' + colorRiesgo + '"></i> ' + recomendacion;
    document.getElementById('recomendacion').style.background = bgColor;
    document.getElementById('recomendacion').style.borderColor = colorRiesgo + '40';
    document.getElementById('recomendacion').style.color = textColor;
    
    const variables = [
        { nombre: 'Edad (' + edad + ')', valor: edad > 70 ? 25 : edad > 60 ? 15 : 8, color: '#F97316' },
        { nombre: 'Saturacion O2 (' + saturacion + '%)', valor: saturacion < 85 ? 30 : saturacion < 90 ? 20 : 8, color: '#DC2626' },
        { nombre: 'Presion (' + presion + ' mmHg)', valor: presion > 160 ? 20 : presion > 140 ? 12 : 5, color: '#EA580C' },
        { nombre: 'Glasgow (' + glasgow + '/15)', valor: (15 - glasgow) * 5, color: '#9A3412' },
        { nombre: 'FC (' + fc + ' lpm)', valor: fc > 110 ? 15 : fc > 100 ? 8 : 0, color: '#F59E0B' },
        { nombre: 'Escenario: ' + esc.label, valor: esc.mod, color: '#7C3AED' },
        { nombre: 'Diabetes: ' + (diabetes ? 'Si' : 'No'), valor: diabetes ? 10 : 0, color: '#059669' },
        { nombre: 'Hipertension: ' + (hipertension ? 'Si' : 'No'), valor: hipertension ? 8 : 0, color: '#0284C7' },
    ].sort((a, b) => b.valor - a.valor);
    
    const maxVal = variables[0].valor;
    let htmlVars = '';
    variables.forEach(v => {
        const pct = maxVal > 0 ? (v.valor / maxVal * 100) : 0;
        htmlVars += '<div style="margin-bottom:0.8rem;"><div style="display:flex;justify-content:space-between;font-size:0.75rem;margin-bottom:0.2rem;"><span style="font-weight:700;color:#57534E;">' + v.nombre + '</span><span style="font-weight:800;color:' + v.color + ';">+' + v.valor + '%</span></div><div style="height:8px;background:#F5F5F4;border-radius:4px;overflow:hidden;"><div style="height:100%;width:' + pct + '%;background:' + v.color + ';border-radius:4px;transition:width 0.6s ease;"></div></div></div>';
    });
    document.getElementById('barras_variables').innerHTML = htmlVars;
    
    ultimaSimulacion = {
        riesgo: riesgo,
        dias: dias,
        costo: costo,
        escenario: escenario,
        variables: variables
    };
}

function debug(msg) { console.log(msg); }

function guardarPrediccion() {
    if (!ultimaSimulacion) return;
    
    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    
    const parametros = {
        edad: document.getElementById('edad').value,
        presion: document.getElementById('presion').value,
        saturacion: document.getElementById('saturacion').value,
        fc: document.getElementById('fc').value,
        temp: document.getElementById('temp').value,
        glasgow: document.getElementById('glasgow').value,
        diabetes: document.getElementById('diabetes').value,
        hipertension: document.getElementById('hipertension').value,
    };
    
    const url = '{{ route("medico.predictivo.crear") }}';
    const token = document.querySelector('meta[name="csrf-token"]').content;
    
    debug('URL: ' + url);
    debug('Token: ' + token.substring(0, 20) + '...');
    
    const body = JSON.stringify({
        patient_id: document.getElementById('patient_id').value || null,
        riesgo_mortalidad: ultimaSimulacion.riesgo,
        dias_estimados: ultimaSimulacion.dias,
        costo_estimado: ultimaSimulacion.costo,
        escenario: ultimaSimulacion.escenario,
        parametros: parametros,
        explicabilidad: ultimaSimulacion.variables
    });
    
    debug('Body: ' + body.substring(0, 100) + '...');
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: body
    })
    .then(response => {
        debug('Status: ' + response.status + ' ' + response.statusText);
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error('HTTP ' + response.status + ': ' + text.substring(0, 200));
            });
        }
        return response.json();
    })
    .then(data => {
        debug('Response: ' + JSON.stringify(data));
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> GUARDADO (Caso #' + data.id + ')';
            btn.style.background = 'linear-gradient(135deg,#16A34A,#15803D)';
            showToast('ok', 'Prediccion guardada como Caso #' + data.id);
        } else {
            throw new Error(JSON.stringify(data));
        }
    })
    .catch(err => {
        debug('ERROR: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> GUARDAR PREDICCION';
        showToast('err', err.message);
    });
}

function showToast(type, msg) {
    const t = document.getElementById(type === 'ok' ? 'toastOk' : 'toastErr');
    document.getElementById(type === 'ok' ? 'toastOkMsg' : 'toastErrMsg').textContent = msg;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 6000);
}
</script>
@endsection
