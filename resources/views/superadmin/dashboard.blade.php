@extends('superadmin.layout')
@section('nav-dashboard', 'active')

@section('content')
<style>
    :root {
        --primary: #F97316;
        --primary-light: #FED7AA;
        --bg-warm: #FFF7ED;
        --critical: #DC2626;
        --critical-dark: #B91C1C;
        --warning: #F59E0B;
        --success: #16A34A;
        --text: #111827;
        --text-secondary: #6B7280;
    }

    #hn-dashboard { font-family: 'Inter', system-ui, sans-serif; color: var(--text); background: var(--bg-warm); padding: 1.5rem; border-radius: 20px; }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
    @keyframes pulseRing { 0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,.45); } 70% { box-shadow: 0 0 0 9px rgba(220,38,38,0); } }
    @keyframes slideDown { from { opacity:0; max-height:0; } to { opacity:1; max-height: 600px; } }
    @keyframes slideUp { from { opacity:0; transform: translateY(24px) scale(.97); } to { opacity:1; transform: translateY(0) scale(1); } }
    @keyframes typingDot { 0%, 60%, 100% { transform: translateY(0); opacity:.4; } 30% { transform: translateY(-4px); opacity:1; } }
    @keyframes shimmerBar { 0% { transform: translateX(-100%);} 100% { transform: translateX(100%);} }

    #hn-dashboard .stagger > * { animation: fadeInUp .55s cubic-bezier(.16,1,.3,1) both; }
    #hn-dashboard .stagger > *:nth-child(1){animation-delay:.02s}
    #hn-dashboard .stagger > *:nth-child(2){animation-delay:.08s}
    #hn-dashboard .stagger > *:nth-child(3){animation-delay:.14s}
    #hn-dashboard .stagger > *:nth-child(4){animation-delay:.20s}
    #hn-dashboard .stagger > *:nth-child(5){animation-delay:.26s}
    #hn-dashboard .stagger > *:nth-child(6){animation-delay:.32s}
    #hn-dashboard .stagger > *:nth-child(7){animation-delay:.38s}
    #hn-dashboard .stagger > *:nth-child(8){animation-delay:.44s}
    #hn-dashboard .stagger > *:nth-child(9){animation-delay:.50s}
    #hn-dashboard .stagger > *:nth-child(10){animation-delay:.56s}

    #hn-dashboard .card { background: #fff; border-radius: 16px; padding: 1.4rem; box-shadow: 0 10px 22px -8px rgba(249,115,22,.12); border-left: 5px solid var(--primary-light); transition: transform .3s ease, box-shadow .3s ease; position: relative; }
    #hn-dashboard .card:hover { transform: translateY(-5px); box-shadow: 0 20px 32px -10px rgba(249,115,22,.22); }
    #hn-dashboard .card-title { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--text-secondary); display:flex; align-items:center; gap:6px; }
    #hn-dashboard .card-value { font-size: 1.9rem; font-weight: 800; margin: .4rem 0 .15rem; line-height:1; color: var(--text); }
    #hn-dashboard .card-sub { font-size: .78rem; color: var(--text-secondary); font-weight: 600; }

    #hn-dashboard .section-head { display:flex; align-items:center; justify-content:space-between; margin: 2.2rem 0 1rem; }
    #hn-dashboard .section-title { font-size: 1rem; font-weight: 800; color: var(--text); display:flex; align-items:center; gap:8px; }
    #hn-dashboard .section-title .dot { width:9px; height:9px; border-radius:50%; background: var(--primary); }

    #hn-dashboard .live-badge { background:#FEE2C7; color:#9A3412; padding:4px 12px; border-radius:20px; font-size:.72rem; font-weight:800; display:inline-flex; align-items:center; gap:6px; }
    #hn-dashboard .live-badge::before { content:''; width:7px; height:7px; background: var(--critical); border-radius:50%; animation: blink 1.4s infinite; }

    /* Header */
    #hn-header { display:flex; justify-content:space-between; align-items:center; background: linear-gradient(120deg, var(--primary), #FB923C); border-radius: 18px; padding: 1.3rem 1.7rem; color:#fff; margin-bottom: 1.7rem; box-shadow: 0 14px 30px -12px rgba(249,115,22,.5); }
    #hn-header h1 { font-size: 1.15rem; font-weight: 800; margin:0; }
    #hn-header .sub { font-size:.78rem; opacity:.92; margin-top:2px; }
    #hn-header .meta { display:flex; gap:1.4rem; align-items:center; font-size:.8rem; font-weight:700; }
    #hn-clock { font-variant-numeric: tabular-nums; }

    /* Portfolio */
    .service-card { background:#fff; border-radius:14px; padding:1rem; text-align:center; border:1px solid #FED7AA; transition: transform .25s ease, box-shadow .25s ease; }
    .service-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -8px rgba(249,115,22,.25); }
    .service-icon { width:44px; height:44px; border-radius:12px; background: var(--bg-warm); display:flex; align-items:center; justify-content:center; margin:0 auto .55rem; color: var(--primary); font-size:1.15rem; }
    .status-pill { display:inline-block; margin-top:.4rem; padding:2px 9px; border-radius:20px; font-size:.65rem; font-weight:800; }
    .status-activo { background:#DCFCE7; color:#166534; }
    .status-saturado { background:#FEE2E2; color:#991B1B; }
    .status-mantenimiento { background:#FEF3C7; color:#92400E; }

    /* Progress + semaphore */
    .progress-track { width:100%; height:8px; background:#FDE9D6; border-radius:20px; overflow:hidden; margin-top:8px; }
    .progress-fill { height:100%; width:0%; border-radius:20px; background: var(--primary); transition: width 1.1s cubic-bezier(.16,1,.3,1) .1s; }
    .semaforo { width:12px; height:12px; border-radius:50%; display:inline-block; }

    /* Risk matrix */
    #risk-matrix { overflow:hidden; max-height:0; opacity:0; transition: max-height .4s ease, opacity .4s ease; }
    #risk-matrix.open { max-height: 600px; opacity:1; animation: slideDown .4s ease; }
    .risk-table { width:100%; border-collapse: collapse; font-size:.8rem; margin-top:.8rem; }
    .risk-table th { text-align:left; color:var(--text-secondary); font-size:.66rem; text-transform:uppercase; font-weight:800; padding:6px 8px; border-bottom:2px solid #FDE9D6; }
    .risk-table td { padding:8px; border-bottom:1px solid #FEF1E4; font-weight:600; }

    /* Ref table */
    .ref-table { width:100%; border-collapse:collapse; font-size:.82rem; margin-top:.8rem; }
    .ref-table th { text-align:left; color:var(--text-secondary); font-size:.66rem; text-transform:uppercase; font-weight:800; padding:8px; border-bottom:2px solid #FDE9D6; }
    .ref-table td { padding:9px 8px; border-bottom:1px solid #FEF1E4; font-weight:600; }
    .ref-table tbody tr:hover { background:#FFF7ED; }
    .route-pill { padding:3px 10px; border-radius:20px; font-size:.68rem; font-weight:800; }

    /* Disponibilidad de camas — mapa desplegable tipo panal (vista visual, sin datos en vivo aún) */
    .legend-dot { width:9px; height:9px; border-radius:3px; display:inline-block; margin-right:5px; }
    .bed-accordion { display:flex; flex-direction:column; gap:.85rem; margin-top:1.1rem; }
    .bed-area { border-radius:16px; overflow:hidden; border:1px solid #FDE9D6; background:#fff; }
    .bed-area-head { display:flex; align-items:center; gap:1rem; padding:1rem 1.2rem; cursor:pointer; background:linear-gradient(135deg,#fff,#FFF7ED); transition: background .2s ease; }
    .bed-area-head:hover { background:#FFF1E1; }
    .bed-area-icon { width:42px; height:42px; border-radius:12px; background: var(--bg-warm); display:flex; align-items:center; justify-content:center; color: var(--primary); font-size:1.05rem; flex-shrink:0; }
    .bed-area-name { font-weight:800; font-size:.88rem; color: var(--text); }
    .bed-area-count { font-size:.74rem; color: var(--text-secondary); font-weight:700; }
    .bed-ring { margin-left:auto; flex-shrink:0; }
    .ring-fg { transition: stroke-dashoffset 1.2s cubic-bezier(.16,1,.3,1) .1s; }
    .bed-chevron { transition: transform .3s ease; color: var(--text-secondary); flex-shrink:0; }
    .bed-area.open .bed-chevron { transform: rotate(180deg); }
    .bed-area-body { max-height:0; opacity:0; overflow:hidden; transition: max-height .5s cubic-bezier(.16,1,.3,1), opacity .4s ease; }
    .bed-area.open .bed-area-body { max-height:360px; opacity:1; }
    .hex-wrap {
        position:relative; padding:1.6rem 1.2rem; overflow:hidden;
        background:
            repeating-linear-gradient(0deg, rgba(249,115,22,.06) 0px, rgba(249,115,22,.06) 1px, transparent 1px, transparent 22px),
            repeating-linear-gradient(90deg, rgba(249,115,22,.06) 0px, rgba(249,115,22,.06) 1px, transparent 1px, transparent 22px);
    }
    .hex-scan { position:absolute; top:0; left:-45%; width:45%; height:100%; background: linear-gradient(90deg, transparent, rgba(249,115,22,.22), transparent); animation: hexScan 3.4s linear infinite; pointer-events:none; }
    @keyframes hexScan { from { left:-45%; } to { left:100%; } }
    .hex-row { display:flex; gap:8px; margin-top:-13px; position:relative; z-index:1; }
    .hex-row:first-child { margin-top:0; }
    .hex-row.offset { margin-left:33px; }
    .hex {
        width:58px; height:50px; clip-path: polygon(25% 0%,75% 0%,100% 50%,75% 100%,25% 100%,0% 50%);
        display:flex; align-items:center; justify-content:center; color:#fff; font-size:.8rem;
        transition: transform .25s ease; filter: drop-shadow(0 3px 5px rgba(0,0,0,.15));
    }
    .hex:hover { transform: scale(1.18) translateY(-2px); z-index:2; }
    .hex-libre { background: linear-gradient(135deg, #22C55E, var(--success)); }
    .hex-ocupada { background: linear-gradient(135deg, #EF4444, var(--critical-dark)); }
    .hex-mantenimiento { background: linear-gradient(135deg, #FBBF24, var(--warning)); }
    .hex-legend { display:flex; gap:1.1rem; padding:.75rem 1.2rem; font-size:.7rem; font-weight:700; color: var(--text-secondary); border-top:1px solid #FDE9D6; background:#FFFBF6; }
    .mock-badge { background:#FEF3C7; color:#92400E; }

    /* Alerts */
    .alert-item { display:flex; align-items:center; gap:10px; background:#fff; border-left:4px solid var(--critical); border-radius:10px; padding:.75rem 1rem; margin-bottom:.6rem; animation: fadeInUp .45s ease both; }
    .alert-item.warning { border-left-color: var(--warning); }
    .alert-icon { color: var(--critical); animation: pulseRing 1.8s infinite; border-radius:50%; }
    .alert-item.warning .alert-icon { color: var(--warning); animation:none; }
    .alert-text { font-size:.85rem; font-weight:700; color: var(--text); }
    .alert-time { font-size:.7rem; color: var(--text-secondary); margin-left:auto; font-weight:600; white-space:nowrap; }

    /* Chatbot */
    #hn-chat-toggle { position: fixed; bottom: 26px; right: 26px; width:60px; height:60px; border-radius:50%; background: linear-gradient(135deg, var(--primary), #EA580C); border:none; color:#fff; font-size:1.4rem; box-shadow: 0 12px 26px -6px rgba(249,115,22,.6); cursor:pointer; z-index:9999; transition: transform .25s ease; }
    #hn-chat-toggle:hover { transform: scale(1.08); }
    #hn-chat-panel { position: fixed; bottom: 100px; right: 26px; width: 340px; max-height: 480px; background:#fff; border-radius:18px; box-shadow: 0 24px 48px -12px rgba(0,0,0,.25); display:none; flex-direction:column; overflow:hidden; z-index:9999; animation: slideUp .3s cubic-bezier(.16,1,.3,1); }
    #hn-chat-panel.open { display:flex; }
    #hn-chat-head { background: linear-gradient(120deg, var(--primary), #FB923C); color:#fff; padding: .9rem 1.1rem; display:flex; align-items:center; gap:10px; }
    #hn-chat-head .bot-icon { width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,.25); display:flex; align-items:center; justify-content:center; }
    #hn-chat-head .title { font-weight:800; font-size:.88rem; }
    #hn-chat-head .status { font-size:.68rem; opacity:.9; }
    #hn-chat-close { margin-left:auto; background:none; border:none; color:#fff; font-size:1rem; cursor:pointer; opacity:.85; }
    #hn-chat-body { flex:1; overflow-y:auto; padding: .9rem; background:#FFFBF6; display:flex; flex-direction:column; gap:.55rem; }
    .msg { max-width: 82%; padding: .6rem .8rem; border-radius: 14px; font-size:.8rem; line-height:1.35; animation: fadeInUp .3s ease both; }
    .msg.bot { background: var(--primary-light); color:#7C2D12; align-self:flex-start; border-bottom-left-radius:4px; }
    .msg.user { background: var(--text); color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
    .typing { display:flex; gap:4px; padding:.5rem .8rem; align-self:flex-start; background: var(--primary-light); border-radius:14px; border-bottom-left-radius:4px; }
    .typing span { width:6px; height:6px; border-radius:50%; background:#9A3412; display:inline-block; animation: typingDot 1s infinite; }
    .typing span:nth-child(2){animation-delay:.15s} .typing span:nth-child(3){animation-delay:.3s}
    #hn-chat-suggestions { display:flex; flex-wrap:wrap; gap:6px; padding: 0 .9rem .7rem; }
    .chip { background:#fff; border:1px solid var(--primary-light); color:#9A3412; font-size:.68rem; font-weight:700; padding:5px 10px; border-radius:20px; cursor:pointer; transition: background .2s ease; }
    .chip:hover { background: var(--primary-light); }
    #hn-chat-input-row { display:flex; border-top:1px solid #FEE2C7; padding:.6rem; gap:.5rem; }
    #hn-chat-input { flex:1; border:1px solid #FDE9D6; border-radius:20px; padding:.5rem .9rem; font-size:.8rem; outline:none; }
    #hn-chat-input:focus { border-color: var(--primary); }
    #hn-chat-send { background: var(--primary); border:none; color:#fff; width:36px; height:36px; border-radius:50%; cursor:pointer; flex-shrink:0; }

    @media (prefers-reduced-motion: reduce) {
        #hn-dashboard * { animation: none !important; transition: none !important; }
    }
</style>

<div id="hn-dashboard">

    <!-- HEADER -->
    <div id="hn-header">
        <div>
            <h1>HealthNexus <span style="font-weight:500; opacity:.85;">| Dashboard Ejecutivo Inteligente</span></h1>
            <div class="sub" id="hn-date">Cargando fecha…</div>
        </div>
        <div class="meta">
            <span id="hn-clock">--:--:--</span>
            <span class="live-badge" style="background:rgba(255,255,255,.25); color:#fff;">EN VIVO</span>
            <span><i class="fas fa-user-shield"></i> SUPERADMIN</span>
        </div>
    </div>

    <!-- 1. KPIs PRINCIPALES -->
    <div style="display:grid; grid-template-columns: repeat(4,1fr); gap:1.3rem;" class="stagger">
        <div class="card">
            <div class="card-title"><i class="fas fa-bed"></i> Ocupación hospitalaria</div>
            <div class="card-value" data-count="{{ $porcentajeOcupacion ?? 78 }}" style="color:var(--primary);">0%</div>
            <div class="card-sub"><i class="fas fa-arrow-up" style="color:var(--success);"></i> +5% vs ayer</div>
        </div>
        <div class="card" style="border-left-color: var(--critical);">
            <div class="card-title"><i class="fas fa-heartbeat"></i> Urgencias críticas</div>
            <div class="card-value" style="color:var(--critical-dark);"><span style="display:inline-block; animation: pulseRing 1.8s infinite; border-radius:50%;">{{ $urgenciasCriticas ?? 4 }}</span></div>
            <div class="card-sub" style="color:var(--critical);"><i class="fas fa-exclamation-triangle"></i> Triage rojo activo</div>
        </div>
        <div class="card">
            <div class="card-title"><i class="fas fa-procedures"></i> Camas UCI libres</div>
            <div class="card-value" data-count="{{ $uciLibres ?? 3 }}" style="color:var(--primary);">0</div>
            <div class="card-sub">de {{ $uciTotal ?? 12 }} camas totales</div>
        </div>
        <div class="card">
            <div class="card-title"><i class="fas fa-ambulance"></i> Ambulancias disponibles</div>
            <div class="card-value" data-count="{{ $ambulanciasDisponibles ?? 2 }}" style="color:var(--primary);">0</div>
            <div class="card-sub">de {{ $ambulanciasTotal ?? 5 }} unidades activas</div>
        </div>
    </div>

    <!-- 2. PORTAFOLIO DE SERVICIOS -->
    <div class="section-head">
        <div class="section-title"><span class="dot"></span> Portafolio de servicios</div>
    </div>
    <div class="card" style="border-left:none; border-top:5px solid var(--primary);">
        <div style="display:grid; grid-template-columns: repeat(5,1fr); gap:1rem;" class="stagger">
            @php
                $servicios = [
                    ['Consulta externa','fa-user-md','activo'],
                    ['Urgencias','fa-truck-medical','activo'],
                    ['Hospitalización','fa-bed','saturado'],
                    ['UCI','fa-heart-pulse','activo'],
                    ['Quirófano','fa-hospital-user','mantenimiento'],
                    ['Laboratorio','fa-flask','activo'],
                    ['Imagenología','fa-x-ray','activo'],
                    ['Farmacia','fa-pills','activo'],
                    ['Ambulancias','fa-ambulance','activo'],
                    ['Especialidades','fa-stethoscope','activo'],
                ];
                $estLabel = ['activo'=>'Activo','saturado'=>'Saturado','mantenimiento'=>'Mantenimiento'];
            @endphp
            @foreach($servicios as [$nombre,$icono,$estado])
                <div class="service-card">
                    <div class="service-icon"><i class="fas {{ $icono }}"></i></div>
                    <div style="font-size:.78rem; font-weight:800; color:var(--text);">{{ $nombre }}</div>
                    <span class="status-pill status-{{ $estado }}">{{ $estLabel[$estado] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. NORMATIVIDADES -->
    <div class="section-head">
        <div class="section-title"><span class="dot"></span> Normatividades de administración</div>
    </div>
    <div style="display:grid; grid-template-columns: repeat(3,1fr); gap:1.3rem;" class="stagger">
        <div class="card">
            <div class="card-title"><i class="fas fa-lock"></i> ISO/IEC 27001</div>
            <div style="font-size:1rem; font-weight:800; margin-top:.3rem;">Seguridad de la Información</div>
            <div class="progress-track"><div class="progress-fill" data-fill="98"></div></div>
            <div class="card-sub" style="margin-top:6px; color:var(--success);"><i class="fas fa-check-circle"></i> Cumplimiento 98%</div>
        </div>
        <div class="card">
            <div class="card-title"><i class="fas fa-leaf"></i> ISO 14001</div>
            <div style="font-size:1rem; font-weight:800; margin-top:.3rem;">Gestión Ambiental</div>
            <div class="progress-track"><div class="progress-fill" data-fill="100"></div></div>
            <div class="card-sub" style="margin-top:6px; color:var(--success);"><i class="fas fa-check-circle"></i> Auditoría vigente</div>
        </div>
        <div class="card">
            <div class="card-title"><i class="fas fa-diagram-project"></i> ISO 31000</div>
            <div style="font-size:1rem; font-weight:800; margin-top:.3rem;">Gestión de Riesgos</div>
            <div style="margin-top:.6rem; display:flex; align-items:center; gap:8px;">
                <span class="semaforo" style="background:var(--success);"></span>
                <span class="card-sub" style="font-weight:800; color:var(--text);">Riesgo operativo: Bajo</span>
            </div>
            <div class="card-sub" style="margin-top:4px; color:var(--warning);"><i class="fas fa-exclamation-circle"></i> 3 riesgos moderados abiertos</div>
        </div>
    </div>

    <!-- 4. GESTIÓN DE RIESGOS + 5. DERIVACIONES -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.3rem; margin-top:2.2rem;">
        <div class="card" style="border-left:none; border-top:5px solid var(--primary);">
            <h3 style="margin:0 0 1rem; font-size:1rem; font-weight:800;">Gestión de riesgos</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.7rem;">
                @php
                    $riesgos = [['Operativo','Bajo','#16A34A'],['Tecnológico','Medio','#F59E0B'],['Clínico','Bajo','#16A34A'],['Financiero','Bajo','#16A34A']];
                @endphp
                @foreach($riesgos as [$nombre,$estado,$color])
                    <div style="background:var(--bg-warm); border:1px solid #FDE9D6; border-radius:12px; padding:.7rem 1rem; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:.8rem; font-weight:700;">{{ $nombre }}</span>
                        <span style="font-size:.75rem; font-weight:800; color:{{ $color }};"><span class="semaforo" style="background:{{ $color }};"></span> {{ $estado }}</span>
                    </div>
                @endforeach
            </div>
            <button onclick="document.getElementById('risk-matrix').classList.toggle('open'); this.textContent = document.getElementById('risk-matrix').classList.contains('open') ? 'Ocultar matriz de riesgos ▲' : 'Ver matriz de riesgos ▼';"
                style="margin-top:1rem; background:var(--primary); color:#fff; border:none; padding:.55rem 1rem; border-radius:10px; font-size:.78rem; font-weight:800; cursor:pointer;">
                Ver matriz de riesgos ▼
            </button>
            <div id="risk-matrix">
                <table class="risk-table">
                    <thead><tr><th>Riesgo</th><th>Probabilidad</th><th>Impacto</th><th>Nivel</th><th>Acción</th></tr></thead>
                    <tbody>
                        <tr><td>Operativo</td><td>Baja</td><td>Medio</td><td style="color:var(--success);">Bajo</td><td>Monitoreo estándar</td></tr>
                        <tr><td>Tecnológico</td><td>Media</td><td>Alto</td><td style="color:var(--warning);">Medio</td><td>Refuerzo de respaldo diario</td></tr>
                        <tr><td>Clínico</td><td>Baja</td><td>Alto</td><td style="color:var(--success);">Bajo</td><td>Protocolo vigente</td></tr>
                        <tr><td>Financiero</td><td>Baja</td><td>Medio</td><td style="color:var(--success);">Bajo</td><td>Revisión trimestral</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="border-left:none; border-top:5px solid var(--primary);">
            <h3 style="margin:0 0 1rem; font-size:1rem; font-weight:800;">Derivaciones y rango razonable</h3>
            <table class="risk-table" style="margin-top:0;">
                <thead><tr><th>Tipo</th><th>Tiempo</th></tr></thead>
                <tbody>
                    <tr><td>Corta distancia</td><td>10–20 min</td></tr>
                    <tr><td>Media distancia</td><td>20–35 min</td></tr>
                    <tr><td>Larga distancia</td><td>35–60 min</td></tr>
                </tbody>
            </table>
            <div class="card-sub" style="margin-top:.6rem;"><i class="fas fa-info-circle"></i> Mayor a 60 min = fuera de rango; se genera alerta automática.</div>
            <div style="display:flex; gap:1rem; margin-top:1rem; padding-top:1rem; border-top:1px solid #FDE9D6;">
                <div>
                    <div class="card-title" style="margin-bottom:2px;">Tiempo promedio actual</div>
                    <div style="font-size:1.3rem; font-weight:800; color:var(--primary);">{{ $tiempoPromedioDerivacion ?? 18 }} min</div>
                </div>
                <div>
                    <div class="card-title" style="margin-bottom:2px;">Estado</div>
                    <div style="font-size:1.3rem; font-weight:800; color:var(--success);"><i class="fas fa-check-circle"></i> Dentro del rango</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. VALORACIÓN PRIMARIA + 7. AMBULANCIAS -->
    <div style="display:grid; grid-template-columns: 1fr 1.4fr; gap:1.3rem; margin-top:2.2rem;">
        <div class="card" style="border-left:none; border-top:5px solid var(--primary);">
            <h3 style="margin:0 0 1rem; font-size:1rem; font-weight:800;">Valoración primaria</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.8rem;">
                <div style="background:var(--bg-warm); border-radius:12px; padding:.8rem; text-align:center;">
                    <div style="font-size:1.3rem; font-weight:800;">{{ $valoracionPromedio ?? 12 }} min</div>
                    <div class="card-sub">Tiempo promedio</div>
                </div>
                <div style="background:var(--bg-warm); border-radius:12px; padding:.8rem; text-align:center;">
                    <div style="font-size:1.3rem; font-weight:800;">{{ $valoradosHoy ?? 34 }}</div>
                    <div class="card-sub">Valorados hoy</div>
                </div>
                <div style="background:var(--bg-warm); border-radius:12px; padding:.8rem; text-align:center;">
                    <div style="font-size:1.3rem; font-weight:800; color:var(--warning);">{{ $valoracionPendientes ?? 3 }}</div>
                    <div class="card-sub">Pendientes</div>
                </div>
                <div style="background:var(--bg-warm); border-radius:12px; padding:.8rem; text-align:center;">
                    <div style="font-size:1.3rem; font-weight:800; color:var(--critical);">{{ $triageRojo ?? 4 }}</div>
                    <div class="card-sub">Triage rojo</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px; margin-top:1rem; padding-top:.8rem; border-top:1px solid #FDE9D6; font-size:.75rem; font-weight:700; color:var(--text-secondary);">
                <span class="semaforo" style="background:var(--success);"></span> &lt;15 min
                <span class="semaforo" style="background:var(--warning); margin-left:8px;"></span> 15–30 min
                <span class="semaforo" style="background:var(--critical); margin-left:8px;"></span> &gt;30 min
            </div>
        </div>

        <div class="card" style="border-left:none; border-top:5px solid var(--primary);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.8rem;">
                <h3 style="margin:0; font-size:1rem; font-weight:800;">Ambulancias y recepción de pacientes</h3>
                <span class="live-badge">EN VIVO</span>
            </div>
            <div style="display:flex; gap:1.5rem; margin-bottom:.6rem;">
                <div><span style="font-size:1.1rem; font-weight:800; color:var(--primary);">{{ $ambulanciasDisponibles ?? 2 }}/{{ $ambulanciasTotal ?? 5 }}</span> <span class="card-sub">disponibles</span></div>
                <div><span style="font-size:1.1rem; font-weight:800; color:var(--primary);">{{ $trasladosEnCurso ?? 3 }}</span> <span class="card-sub">traslados en curso</span></div>
            </div>
            <table class="ref-table">
                <thead><tr><th>Hospital</th><th>UCI libres</th><th>Tiempo</th><th>Estado</th></tr></thead>
                <tbody>
                    @php
                        $redHospitalesDefault = [
                            ['HealthNexus Central', 3, 'En sitio', 'disponible'],
                            ['Hospital Regional', 2, '18 min', 'disponible'],
                            ['Centro Médico Especialidad', 1, '27 min', 'disponible'],
                            ['Hospital Materno', 0, '22 min', 'saturado'],
                        ];
                        $estados = ['disponible'=>['#DCFCE7','#166534','Disponible'], 'saturado'=>['#FEE2E2','#991B1B','Saturado']];
                    @endphp
                    @foreach(($redHospitales ?? $redHospitalesDefault) as [$nombre,$uci,$tiempo,$estado])
                        <tr>
                            <td>{{ $nombre }}</td>
                            <td>{{ $uci }}</td>
                            <td>{{ $tiempo }}</td>
                            <td><span class="route-pill" style="background:{{ $estados[$estado][0] }}; color:{{ $estados[$estado][1] }};">{{ $estados[$estado][2] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 8. DISPONIBILIDAD DE CAMAS (ARDUINO) -->
    <div class="section-head">
        <div class="section-title"><span class="dot"></span> Disponibilidad de camas por área</div>
        <span class="live-badge mock-badge" style="background:#FEF3C7; color:#92400E;"><i class="fas fa-flask"></i> Vista previa visual</span>
    </div>
    <div class="card" style="border-left:none; border-top:5px solid var(--primary);">
        <div class="card-sub">Mapa conceptual de camas por área, desplegable por sección. Todavía no está conectado al hardware Arduino — lo que ves aquí es una previsualización de diseño, no datos en vivo.</div>

        <div class="bed-accordion">
            @php
                $areas = [
                    ['id'=>'uci','nombre'=>'UCI','icono'=>'fa-heart-pulse','libres'=>$uciLibres ?? 3,'total'=>$uciTotal ?? 12,'hexes'=>12],
                    ['id'=>'hosp','nombre'=>'Hospitalización','icono'=>'fa-bed','libres'=>$hospLibres ?? 18,'total'=>$hospTotal ?? 40,'hexes'=>20],
                    ['id'=>'obs','nombre'=>'Observación','icono'=>'fa-eye','libres'=>$obsLibres ?? 5,'total'=>$obsTotal ?? 10,'hexes'=>10],
                ];
                $r = 20; $circ = round(2 * M_PI * $r, 2);
            @endphp
            @foreach($areas as $i => $area)
                @php
                    $ocupPct = $area['total'] > 0 ? round((($area['total'] - $area['libres']) / $area['total']) * 100) : 0;
                    $ringColor = $ocupPct >= 85 ? '#DC2626' : ($ocupPct >= 60 ? '#F59E0B' : '#16A34A');
                    $ringOffset = round($circ * (1 - $ocupPct / 100), 2);
                    $libreCount = max(1, (int) round($area['hexes'] * ($area['libres'] / max($area['total'], 1))));
                    $mantCount = $area['hexes'] > 8 ? 2 : 1;
                    $ocupCount = max(0, $area['hexes'] - $libreCount - $mantCount);
                    $hexList = array_merge(array_fill(0, $libreCount, 'libre'), array_fill(0, $ocupCount, 'ocupada'), array_fill(0, $mantCount, 'mantenimiento'));
                    shuffle($hexList);
                    $rows = array_chunk($hexList, 8);
                @endphp
                <div class="bed-area {{ $i === 0 ? 'open' : '' }}" id="bed-area-{{ $area['id'] }}">
                    <div class="bed-area-head" onclick="document.getElementById('bed-area-{{ $area['id'] }}').classList.toggle('open')">
                        <div class="bed-area-icon"><i class="fas {{ $area['icono'] }}"></i></div>
                        <div>
                            <div class="bed-area-name">{{ $area['nombre'] }}</div>
                            <div class="bed-area-count">{{ $area['libres'] }} libres de {{ $area['total'] }}</div>
                        </div>
                        <svg class="bed-ring" width="52" height="52" viewBox="0 0 52 52">
                            <circle cx="26" cy="26" r="{{ $r }}" fill="none" stroke="#FDE9D6" stroke-width="6"/>
                            <circle class="ring-fg" cx="26" cy="26" r="{{ $r }}" fill="none" stroke="{{ $ringColor }}" stroke-width="6"
                                stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ }}"
                                data-offset="{{ $ringOffset }}" transform="rotate(-90 26 26)"/>
                            <text x="26" y="30" text-anchor="middle" font-size="11" font-weight="800" fill="#111827">{{ $ocupPct }}%</text>
                        </svg>
                        <i class="fas fa-chevron-down bed-chevron"></i>
                    </div>
                    <div class="bed-area-body">
                        <div class="hex-wrap">
                            <div class="hex-scan"></div>
                            @foreach($rows as $ri => $row)
                                <div class="hex-row {{ $ri % 2 === 1 ? 'offset' : '' }}">
                                    @foreach($row as $estadoHex)
                                        <div class="hex hex-{{ $estadoHex }}" title="{{ ucfirst($estadoHex) }}">
                                            <i class="fas {{ $estadoHex === 'libre' ? 'fa-bed' : ($estadoHex === 'ocupada' ? 'fa-user' : 'fa-broom') }}"></i>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <div class="hex-legend">
                            <span><span class="legend-dot" style="background:var(--success);"></span>Libre</span>
                            <span><span class="legend-dot" style="background:var(--critical);"></span>Ocupada</span>
                            <span><span class="legend-dot" style="background:var(--warning);"></span>Limpieza / mantenimiento</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card-sub" style="margin-top:1rem; display:flex; justify-content:space-between; flex-wrap:wrap; gap:.6rem;">
            <span><i class="fas fa-broadcast-tower"></i> Módulos reportando: {{ $modulosReportando ?? 21 }}/{{ $modulosTotal ?? 24 }}</span>
            <span><i class="fas fa-sync-alt"></i> Cuando se conecte el hardware, esta vista se actualizará automáticamente en tiempo real.</span>
        </div>
    </div>

    <!-- 9. CENTRO DE ALERTAS CRÍTICAS -->
    <div class="section-head">
        <div class="section-title"><span class="dot"></span> Centro de alertas críticas</div>
    </div>
    <div class="card" style="border-left:none; border-top:5px solid var(--critical);">
        <div class="alert-item">
            <i class="fas fa-triangle-exclamation alert-icon"></i>
            <span class="alert-text">Paciente con riesgo IA UCI &gt; 90%</span>
            <span class="alert-time">hace 4 min</span>
        </div>
        <div class="alert-item warning">
            <i class="fas fa-clock alert-icon"></i>
            <span class="alert-text">Ambulancia con retraso en ruta a Hospital Regional</span>
            <span class="alert-time">hace 9 min</span>
        </div>
        <div class="alert-item">
            <i class="fas fa-bed alert-icon"></i>
            <span class="alert-text">Cama UCI agotada en Hospital Materno</span>
            <span class="alert-time">hace 22 min</span>
        </div>
        <div class="alert-item warning">
            <i class="fas fa-broadcast-tower alert-icon"></i>
            <span class="alert-text">Fallo intermitente de comunicación con Arduino (módulo 14)</span>
            <span class="alert-time">hace 31 min</span>
        </div>
    </div>
</div>

<!-- CHATBOT FLOTANTE -->
<button id="hn-chat-toggle" aria-label="Asistente IA HealthNexus"><i class="fas fa-robot"></i></button>
<div id="hn-chat-panel">
    <div id="hn-chat-head">
        <div class="bot-icon"><i class="fas fa-robot"></i></div>
        <div>
            <div class="title">Asistente IA HealthNexus</div>
            <div class="status">● En línea</div>
        </div>
        <button id="hn-chat-close" aria-label="Cerrar">✕</button>
    </div>
    <div id="hn-chat-body">
        <div class="msg bot">Hola, soy el asistente de HealthNexus. Puedo ayudarte con dudas de ocupación, camas UCI, ambulancias, normas ISO, derivaciones o uso del sistema. ¿Qué necesitas saber?</div>
    </div>
    <div id="hn-chat-suggestions">
        <span class="chip" data-q="¿Cuántas camas UCI hay disponibles?">Camas UCI</span>
        <span class="chip" data-q="¿Qué ambulancias están libres?">Ambulancias</span>
        <span class="chip" data-q="¿Cuál es el rango razonable de derivación?">Rango derivación</span>
        <span class="chip" data-q="¿Qué normas ISO monitorea HealthNexus?">Normas ISO</span>
        <span class="chip" data-q="¿Cómo genero una derivación?">Generar derivación</span>
    </div>
    <div id="hn-chat-input-row">
        <input id="hn-chat-input" type="text" placeholder="Escribe tu pregunta…" />
        <button id="hn-chat-send" aria-label="Enviar"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
(function () {
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Datos vivos de la página (mismo origen que los KPIs de arriba)
    var HN_DATA = {
        ocupacion: {{ $porcentajeOcupacion ?? 78 }},
        uciLibres: {{ $uciLibres ?? 3 }},
        uciTotal: {{ $uciTotal ?? 12 }},
        ambDisponibles: {{ $ambulanciasDisponibles ?? 2 }},
        ambTotal: {{ $ambulanciasTotal ?? 5 }},
        valoracionPromedio: {{ $valoracionPromedio ?? 12 }},
        tiempoPromedioDerivacion: {{ $tiempoPromedioDerivacion ?? 18 }},
        urgenciasCriticas: {{ $urgenciasCriticas ?? 4 }}
    };

    /* ---------- animaciones de entrada de datos ---------- */
    function countUp() {
        document.querySelectorAll('#hn-dashboard .card-value[data-count]').forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-count')) || 0;
            var suffix = el.textContent.replace(/[0-9.]/g, '');
            if (reduceMotion) { el.textContent = target + suffix; return; }
            var start = null, duration = 1000;
            function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / duration, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * eased) + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });
    }
    function growProgress() {
        document.querySelectorAll('.progress-fill').forEach(function (bar) {
            var target = bar.getAttribute('data-fill') || 0;
            requestAnimationFrame(function () { bar.style.width = target + '%'; });
        });
    }
    function growRings() {
        document.querySelectorAll('.ring-fg').forEach(function (ring) {
            var target = ring.getAttribute('data-offset');
            requestAnimationFrame(function () { ring.style.strokeDashoffset = target; });
        });
    }
    setTimeout(function () { countUp(); growProgress(); growRings(); }, 150);

    /* ---------- reloj en vivo ---------- */
    function tick() {
        var now = new Date();
        var clock = document.getElementById('hn-clock');
        var dateEl = document.getElementById('hn-date');
        if (clock) clock.textContent = now.toLocaleTimeString('es-MX', { hour12: false });
        if (dateEl) dateEl.textContent = now.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    tick(); setInterval(tick, 1000);

    /* ---------- chatbot ---------- */
    var toggle = document.getElementById('hn-chat-toggle');
    var panel = document.getElementById('hn-chat-panel');
    var closeBtn = document.getElementById('hn-chat-close');
    var body = document.getElementById('hn-chat-body');
    var input = document.getElementById('hn-chat-input');
    var sendBtn = document.getElementById('hn-chat-send');

    function openPanel() { panel.classList.add('open'); input.focus(); }
    function closePanel() { panel.classList.remove('open'); }
    toggle.addEventListener('click', function () { panel.classList.contains('open') ? closePanel() : openPanel(); });
    closeBtn.addEventListener('click', closePanel);

    function addMessage(text, who) {
        var div = document.createElement('div');
        div.className = 'msg ' + who;
        div.textContent = text;
        body.appendChild(div);
        body.scrollTop = body.scrollHeight;
    }

    function showTyping() {
        var t = document.createElement('div');
        t.className = 'typing';
        t.id = 'hn-typing';
        t.innerHTML = '<span></span><span></span><span></span>';
        body.appendChild(t);
        body.scrollTop = body.scrollHeight;
    }
    function hideTyping() {
        var t = document.getElementById('hn-typing');
        if (t) t.remove();
    }

    /* Quita acentos y pasa a minúsculas para que "genero", "generó" o "genera" se traten igual */
    function normalizeText(str) {
        return (str || '')
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[¿?¡!.,]/g, ' ');
    }

    /* Cada intención se activa si, para CADA grupo, aparece al menos uno de sus "stems"
       (raíces de palabra) en el texto. Los stems son deliberadamente cortos para
       cubrir variaciones: "gener" -> genero/genera/generar/generando. */
    var INTENTS = [
        { groups: [['uci'], ['cama', 'disponib', 'libre', 'ocup']],
          answer: function () { return 'Actualmente hay ' + HN_DATA.uciLibres + ' camas UCI libres de un total de ' + HN_DATA.uciTotal + '.'; } },

        { groups: [['ambulanci']],
          answer: function () { return 'Hay ' + HN_DATA.ambDisponibles + ' ambulancias disponibles de ' + HN_DATA.ambTotal + ' unidades activas.'; } },

        { groups: [['valorac'], ['primari']],
          answer: function () { return 'El tiempo promedio de valoración primaria es de ' + HN_DATA.valoracionPromedio + ' minutos.'; } },

        { groups: [['rango'], ['deriv']],
          answer: function () { return 'El rango recomendado de derivación es: 10–20 min (corta distancia), 20–35 min (media distancia) y 35–60 min (larga distancia). Más de 60 min se considera fuera de rango y genera una alerta automática. El tiempo promedio actual es de ' + HN_DATA.tiempoPromedioDerivacion + ' min, dentro del rango.'; } },

        { groups: [['gener', 'crea', 'solicit', 'nuev', 'como hago'], ['deriv']],
          answer: function () { return 'Para generar una derivación ve al módulo Ambulancias → Nueva derivación, selecciona el hospital receptor según camas disponibles y confirma la ruta óptima sugerida.'; } },

        { groups: [['27001']],
          answer: function () { return 'El cumplimiento actual de ISO/IEC 27001 (Seguridad de la Información) es del 98%.'; } },
        { groups: [['14001']],
          answer: function () { return 'ISO 14001 (Gestión Ambiental) tiene su auditoría vigente y sin observaciones pendientes.'; } },
        { groups: [['31000']],
          answer: function () { return 'ISO 31000 (Gestión de Riesgos) reporta riesgo operativo Bajo, con 3 riesgos moderados abiertos en seguimiento.'; } },
        { groups: [['iso', 'norma', 'cumplimient']],
          answer: function () { return 'HealthNexus monitorea tres normas principales: ISO/IEC 27001 (seguridad de la información), ISO 14001 (gestión ambiental) e ISO 31000 (gestión de riesgos).'; } },

        { groups: [['urgenc'], ['critic', 'rojo']],
          answer: function () { return 'Hay ' + HN_DATA.urgenciasCriticas + ' pacientes en triage rojo activo en este momento.'; } },

        { groups: [['registr', 'alta', 'nuev'], ['pacient']],
          answer: function () { return 'Para registrar un paciente nuevo, ve al módulo Pacientes → Nuevo registro y completa sus datos de identificación y motivo de consulta.'; } },

        { groups: [['egres', 'salida', 'dar de alta']],
          answer: function () { return 'El egreso de un paciente se registra desde su expediente activo, en el botón "Dar de alta / Egreso", donde se documenta el diagnóstico final.'; } },

        { groups: [['inventario']],
          answer: function () { return 'El inventario general se consulta en el módulo correspondiente a cada área (Farmacia, Laboratorio, Insumos), donde puedes ver existencias y generar órdenes de compra.'; } },

        { groups: [['farmacia']],
          answer: function () { return 'El módulo de Farmacia muestra el inventario, medicamentos con stock bajo y permite generar órdenes de compra.'; } },

        { groups: [['ocupaci']],
          answer: function () { return 'La ocupación hospitalaria actual es del ' + HN_DATA.ocupacion + '%.'; } },

        { groups: [['cita', 'agenda']],
          answer: function () { return 'Las citas se gestionan en el módulo Citas → Agenda, donde puedes programar, reagendar o cancelar consultas por médico y horario.'; } },

        { groups: [['factur', 'cobro', 'pago', 'cuenta por cobrar']],
          answer: function () { return 'La facturación y cuentas pendientes se consultan en el módulo Administración → Facturación, con el detalle de cuentas por cobrar y vencimientos.'; } },

        { groups: [['reporte', 'informe', 'exporta']],
          answer: function () { return 'Puedes generar reportes desde el módulo Reportes, con exportación a PDF o Excel de ocupación, urgencias, farmacia y auditoría.'; } },

        { groups: [['usuario', 'permiso', 'rol', 'acceso']],
          answer: function () { return 'La gestión de usuarios, roles y permisos está en Configuración → Usuarios, donde el SuperAdmin controla qué puede ver y hacer cada rol.'; } },

        { groups: [['respaldo', 'backup']],
          answer: function () { return 'El último respaldo automático del sistema se realizó hoy a las 03:00 horas. Puedes forzar uno manual desde Configuración → Respaldos.'; } },

        { groups: [['auditor']],
          answer: function () { return 'El historial de auditoría (quién hizo qué cambio y cuándo) se consulta en Seguridad → Auditoría del sistema.'; } },

        { groups: [['consentimiento', 'firma digital']],
          answer: function () { return 'Los consentimientos informados pendientes de firma se ven en el expediente del paciente, sección Documentos → Consentimientos.'; } },

        { groups: [['historial', 'expediente']],
          answer: function () { return 'El historial clínico completo de un paciente está en su Expediente digital, accesible desde el módulo Pacientes.'; } },

        { groups: [['laboratorio', 'resultado']],
          answer: function () { return 'Los resultados de laboratorio se consultan en el módulo Laboratorio → Resultados, ligados al expediente del paciente.'; } },

        { groups: [['imagenolog', 'rayos x', 'radiografia', 'estudio de imagen']],
          answer: function () { return 'Los estudios de imagenología (rayos X, tomografías, etc.) se consultan en el módulo Imagenología → Estudios.'; } },

        { groups: [['quirofano', 'cirugia', 'operacion']],
          answer: function () { return 'La agenda de quirófanos y cirugías programadas está en el módulo Quirófano → Agenda quirúrgica.'; } },

        { groups: [['contrasena', 'password', 'clave', 'perfil']],
          answer: function () { return 'Puedes cambiar tu contraseña o datos de perfil desde el ícono de usuario, arriba a la derecha → Mi perfil.'; } },

        { groups: [['soporte', 'ticket', 'falla', 'error', 'arduino']],
          answer: function () { return 'Para reportar una falla o abrir un ticket, ve al módulo Soporte Técnico → Nuevo ticket. El equipo de TI da seguimiento por SLA.'; } },

        { groups: [['hola', 'buenas', 'buenos dias', 'buenas tardes', 'buenas noches']],
          answer: function () { return '¡Hola! ¿En qué puedo ayudarte hoy: ocupación, camas UCI, ambulancias, normas ISO, derivaciones, citas, farmacia o algo del sistema?'; } }
    ];

    function matchIntent(text) {
        var norm = normalizeText(text);
        for (var i = 0; i < INTENTS.length; i++) {
            var groups = INTENTS[i].groups;
            var matchesAll = groups.every(function (stems) {
                return stems.some(function (stem) { return norm.indexOf(stem) !== -1; });
            });
            if (matchesAll) return INTENTS[i];
        }
        return null;
    }

    function answer(rawText) {
        var intent = matchIntent(rawText);
        if (intent) return intent.answer();
        return 'Aún no tengo una respuesta exacta para eso. Puedo ayudarte con: ocupación, camas por área, ambulancias, valoración primaria, derivaciones, normas ISO, pacientes, citas, farmacia, laboratorio, imagenología, quirófano, facturación, reportes, usuarios y permisos, respaldos, auditoría o soporte técnico. ¿Puedes reformular tu pregunta con alguno de esos temas?';
    }

    function handleUserMessage(text) {
        text = (text || '').trim();
        if (!text) return;
        addMessage(text, 'user');
        input.value = '';
        showTyping();
        setTimeout(function () {
            hideTyping();
            addMessage(answer(text), 'bot');
        }, 650 + Math.random() * 400);
    }

    sendBtn.addEventListener('click', function () { handleUserMessage(input.value); });
    input.addEventListener('keydown', function (e) { if (e.key === 'Enter') handleUserMessage(input.value); });
    document.querySelectorAll('#hn-chat-suggestions .chip').forEach(function (chip) {
        chip.addEventListener('click', function () { handleUserMessage(chip.getAttribute('data-q')); });
    });
})();
</script>
@endsection