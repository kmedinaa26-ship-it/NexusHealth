@extends('superadmin.layout')

@section('title', 'Fenotipado Clinico')

@section('content')
<style>
    :root {
        --ph-primary: #F97316;
        --ph-primary-dark: #C2410C;
        --ph-primary-light: #FED7AA;
        --ph-bg-warm: #FFF7ED;
        --ph-critical: #DC2626;
        --ph-critical-dark: #991B1B;
        --ph-amber: #D97706;
        --ph-coral: #FB923C;
        --ph-rust: #B91C1C;
        --ph-success: #16A34A;
        --ph-text: #1E1A17;
        --ph-text-secondary: #78716C;
    }

    @keyframes phFadeUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes phPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,.4); } 70% { box-shadow: 0 0 0 8px rgba(220,38,38,0); } }

    .ph-stagger > * { animation: phFadeUp .5s cubic-bezier(.16,1,.3,1) both; }
    .ph-stagger > *:nth-child(1){animation-delay:.02s} .ph-stagger > *:nth-child(2){animation-delay:.07s}
    .ph-stagger > *:nth-child(3){animation-delay:.12s} .ph-stagger > *:nth-child(4){animation-delay:.17s}
    .ph-stagger > *:nth-child(5){animation-delay:.22s} .ph-stagger > *:nth-child(6){animation-delay:.27s}

    .ph-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1rem; }
    .ph-kpi { text-align: center; padding: 0.85rem 0.75rem; border-radius: 12px; border: 1px solid var(--ph-primary-light); background: linear-gradient(160deg, #fff, var(--ph-bg-warm)); transition: transform .25s ease, box-shadow .25s ease; }
    .ph-kpi:hover { transform: translateY(-4px); box-shadow: 0 14px 24px -10px rgba(249,115,22,.28); }
    .ph-kpi .label { font-size: 0.6rem; color: var(--ph-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .ph-kpi .value { font-size: 1.25rem; font-weight: 800; margin-top: 0.15rem; }
    .ph-kpi i { font-size: 0.95rem; margin-bottom: 0.2rem; display: block; }

    .ph-card { background: white; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid var(--ph-primary-light); margin-bottom: 1rem; transition: box-shadow .25s ease; }
    .ph-card:hover { box-shadow: 0 12px 22px -12px rgba(249,115,22,.25); }
    .ph-section { font-size: 0.8rem; font-weight: 800; color: var(--ph-text); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem; }
    .ph-section i { color: var(--ph-primary); }

    .ph-vars-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
    .ph-var-item { background: var(--ph-bg-warm); border: 1px solid var(--ph-primary-light); border-radius: 10px; padding: 0.6rem 0.8rem; display: flex; align-items: center; gap: 0.6rem; transition: transform .2s ease; }
    .ph-var-item:hover { transform: translateY(-2px); }
    .ph-var-num { width: 26px; height: 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.62rem; font-weight: 800; color: white; flex-shrink: 0; }
    .ph-var-text { font-size: 0.75rem; font-weight: 700; color: var(--ph-text); }

    .ph-tab-bar { display: flex; gap: 0.25rem; margin-bottom: 1rem; border-bottom: 2px solid var(--ph-primary-light); }
    .ph-tab { padding: 0.5rem 0.9rem; font-size: 0.72rem; font-weight: 800; color: var(--ph-text-secondary); cursor: pointer; border: none; background: none; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: color .2s ease; }
    .ph-tab.active { color: var(--ph-primary); border-bottom-color: var(--ph-primary); }
    .ph-tab:hover { color: var(--ph-primary-dark); }
    .ph-tab-panel { display: none; }
    .ph-tab-panel.active { display: block; animation: phFadeUp .35s ease both; }

    .ph-select { padding: 0.45rem 0.65rem; border: 1px solid var(--ph-primary-light); border-radius: 8px; font-size: 0.8rem; font-weight: 700; color: var(--ph-text); background: #fff; }
    .ph-select:focus { outline: none; border-color: var(--ph-primary); box-shadow: 0 0 0 3px rgba(249,115,22,0.12); }

    .btn-ph { padding: 0.4rem 1rem; border-radius: 8px; font-size: 0.72rem; font-weight: 800; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; background: linear-gradient(120deg, var(--ph-primary), var(--ph-coral)); color: white; transition: transform .2s ease, box-shadow .2s ease; box-shadow: 0 8px 16px -8px rgba(249,115,22,.55); }
    .btn-ph:hover { transform: translateY(-2px); box-shadow: 0 12px 20px -8px rgba(249,115,22,.6); }
    .btn-ph:disabled { opacity: 0.4; cursor: not-allowed; transform:none; box-shadow:none; }
    .btn-ph-alt { background: linear-gradient(120deg, var(--ph-critical), var(--ph-rust)); box-shadow: 0 8px 16px -8px rgba(220,38,38,.5); }
    .btn-ph-alt:hover { box-shadow: 0 12px 20px -8px rgba(220,38,38,.55); }

    .ph-score-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    .ph-score-card { border-radius: 12px; padding: 1rem; text-align: center; border: 1px solid var(--ph-primary-light); background: linear-gradient(160deg,#fff,var(--ph-bg-warm)); }
    .ph-score-card .label { font-size: 0.6rem; color: var(--ph-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .ph-score-card .value { font-size: 1.85rem; font-weight: 800; margin-top: 0.2rem; }
    .ph-score-card .sub { font-size: 0.6rem; color: #A8A29E; margin-top: 0.15rem; }
    .ph-interp { margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--ph-primary-light); font-size: 0.65rem; font-weight: 700; line-height: 1.3; min-height: 1.2em; }
    .ph-interp.ok { color: var(--ph-success); }
    .ph-interp.mid { color: var(--ph-amber); }
    .ph-interp.low { color: var(--ph-critical); }
    .ph-interp.neutral { color: var(--ph-text-secondary); font-weight: 600; }

    .ph-caption { font-size: 0.68rem; color: var(--ph-text-secondary); font-weight: 600; line-height: 1.35; margin: -0.2rem 0 0.75rem; }

    .ph-chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; }

    .ph-phenotype-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    .ph-phenotype { border-radius: 12px; padding: 1rem; border: 2px solid; background: white; transition: transform .2s ease, box-shadow .2s ease; }
    .ph-phenotype:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -10px rgba(0,0,0,.15); }
    .ph-phenotype .name { font-size: 0.75rem; font-weight: 800; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.4rem; }
    .ph-phenotype .dot { width: 10px; height: 10px; border-radius: 50%; }
    .ph-phenotype .row { display: flex; justify-content: space-between; font-size: 0.72rem; padding: 0.2rem 0; }
    .ph-phenotype .row .k { color: var(--ph-text-secondary); }
    .ph-phenotype .row .v { font-weight: 700; color: var(--ph-text); }
    .ph-phenotype .tri { margin-top: 0.4rem; padding-top: 0.4rem; border-top: 1px solid #F5F5F4; font-size: 0.65rem; color: var(--ph-text-secondary); }

    .ph-tbl { width: 100%; border-collapse: collapse; font-size: 0.72rem; }
    .ph-tbl th { background: linear-gradient(120deg, var(--ph-primary), var(--ph-coral)); color: white; padding: 0.4rem 0.5rem; text-align: left; font-size: 0.6rem; text-transform: uppercase; letter-spacing: .4px; }
    .ph-tbl th.c { text-align: center; }
    .ph-tbl td { padding: 0.32rem 0.5rem; border-bottom: 1px solid var(--ph-bg-warm); }
    .ph-tbl tr:hover td { background: var(--ph-bg-warm); }
    .ph-tbl td.c { text-align: center; }

    .badge { padding: 0.1rem 0.5rem; border-radius: 20px; font-size: 0.58rem; font-weight: 800; display: inline-block; }
    .badge-rojo { background: #FEE2E2; color: var(--ph-critical-dark); }
    .badge-amarillo { background: #FEF3C7; color: #92400E; }
    .badge-verde { background: #FED7AA; color: var(--ph-primary-dark); }
    .badge-negro { background: #1C1917; color: #F5F5F4; }

    .ph-pca-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    .ph-pca-card { background: white; border-radius: 12px; padding: 1rem; border: 1px solid var(--ph-primary-light); border-top: 3px solid; transition: transform .2s ease; }
    .ph-pca-card:hover { transform: translateY(-3px); }
    .ph-pca-card .pc-head { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.6rem; }
    .ph-pca-card .pc-badge { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 0.68rem; }
    .ph-pca-card .pc-name { font-weight: 800; font-size: 0.78rem; color: var(--ph-text); }
    .ph-pca-card .pc-pct { font-size: 0.65rem; color: var(--ph-text-secondary); }

    .ph-ltbl { width: 100%; border-collapse: collapse; font-size: 0.72rem; }
    .ph-ltbl th { background: linear-gradient(120deg, var(--ph-primary), var(--ph-coral)); color: white; padding: 0.35rem 0.5rem; text-align: center; font-size: 0.6rem; text-transform: uppercase; }
    .ph-ltbl th:first-child { text-align: left; }
    .ph-ltbl td { padding: 0.3rem 0.5rem; text-align: center; border-bottom: 1px solid var(--ph-bg-warm); }
    .ph-ltbl td:first-child { text-align: left; font-weight: 700; }
    .ph-ltbl tr:hover td { background: var(--ph-bg-warm); }

    .ph-loading { display: none; align-items: center; gap: 0.4rem; font-size: 0.72rem; color: var(--ph-text-secondary); font-weight: 700; }
    .ph-loading.show { display: inline-flex; }
    .ph-loading i { animation: spin 0.8s linear infinite; color: var(--ph-primary); }

    .ph-wrap { background: white; border-radius: 12px; border: 1px solid var(--ph-primary-light); overflow: hidden; margin-bottom: 1rem; }
    .ph-wrap-head { padding: 0.65rem 1rem; background: var(--ph-bg-warm); border-bottom: 1px solid var(--ph-primary-light); }
    .ph-wrap-head h3 { margin: 0; font-size: 0.8rem; font-weight: 800; color: var(--ph-text); display: flex; align-items: center; gap: 0.35rem; }
    .ph-wrap-head h3 i { color: var(--ph-primary); }
    .ph-scroll { max-height: 400px; overflow-y: auto; }

    .flow-box { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem; border: 1px solid var(--ph-primary-light); background: linear-gradient(120deg, #FFF7ED, #FFEDD5); border-radius: 12px; padding: 0.8rem 1.1rem; }
    .flow-box .flow-icon { display: flex; align-items: center; gap: 0.5rem; }
    .flow-box .flow-icon i { font-size: 1.25rem; color: var(--ph-primary); animation: spin 2.5s linear infinite; }
    .flow-box .flow-text .ft-title { font-size: 0.78rem; font-weight: 800; color: var(--ph-text); }
    .flow-box .flow-text .ft-sub { font-size: 0.65rem; color: var(--ph-text-secondary); }
    .flow-metrics { display: flex; align-items: center; gap: 1.5rem; }
    .flow-metric { text-align: center; }
    .flow-metric .fm-num { font-size: 1.3rem; font-weight: 800; color: var(--ph-primary-dark); line-height: 1; }
    .flow-metric .fm-label { font-size: 0.6rem; color: var(--ph-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }

    @media (prefers-reduced-motion: reduce) {
        .ph-stagger > *, .ph-tab-panel.active, .flow-box .flow-icon i { animation: none !important; }
    }
</style>

<!-- KPIs -->
<div class="ph-grid ph-stagger">
    <div class="ph-kpi" style="border-left:3px solid var(--ph-primary);"><i class="fas fa-users" style="color:var(--ph-primary);"></i><div class="label">Pacientes</div><div class="value" style="color:var(--ph-primary);">{{ $totalPacientes }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid var(--ph-primary-dark);"><i class="fas fa-procedures" style="color:var(--ph-primary-dark);"></i><div class="label">Alta Fenotipado</div><div class="value" style="color:var(--ph-primary-dark);">{{ $totalAdmisiones }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid var(--ph-critical);"><i class="fas fa-heartbeat" style="color:var(--ph-critical);"></i><div class="label">Signos Vitales</div><div class="value" style="color:var(--ph-critical);">{{ $totalVitales }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid var(--ph-amber);"><i class="fas fa-pills" style="color:var(--ph-amber);"></i><div class="label">Recetas</div><div class="value" style="color:var(--ph-amber);">{{ $totalRecetas }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid var(--ph-coral);"><i class="fas fa-flask" style="color:var(--ph-coral);"></i><div class="label">Estudios Lab</div><div class="value" style="color:var(--ph-coral);">{{ $totalLabs }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid var(--ph-rust);"><i class="fas fa-x-ray" style="color:var(--ph-rust);"></i><div class="label">Imagenes</div><div class="value" style="color:var(--ph-rust);">{{ $totalImg }}</div></div>
</div>

<!-- FLUJO DE DATOS EN TIEMPO REAL -->
<div class="flow-box">
    <div class="flow-icon"><i class="fas fa-sync-alt"></i></div>
    <div class="flow-text">
        <div class="ft-title">Flujo de Datos en Tiempo Real</div>
        <div class="ft-sub">Los datos se alimentan de admisiones reales dadas de alta. El fenotipado siempre lee las tablas vivas del hospital.</div>
    </div>
    <div class="flow-metrics">
        <div class="flow-metric"><div class="fm-num">{{ $newAdmisions }}</div><div class="fm-label">Nuevas altas</div></div>
        <div class="flow-metric"><div class="fm-num" style="color:var(--ph-critical);">{{ $newVitals }}</div><div class="fm-label">Nuevos vitales</div></div>
        @if($lastRun)
        <div style="font-size:0.6rem;color:#A8A29E;">Ultima ejecucion: {{ $lastRun }}</div>
        @endif
    </div>
</div>

<!-- VECTOR CLINICO -->
<div class="ph-card">
    <div class="ph-section"><i class="fas fa-dna"></i> Vector Clinico — 8 Variables</div>
    <div class="ph-vars-grid">
        <div class="ph-var-item"><div class="ph-var-num" style="background:var(--ph-primary);">01</div><div class="ph-var-text">Edad</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:var(--ph-critical);">02</div><div class="ph-var-text">Severidad Entrada</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:var(--ph-primary-dark);">03</div><div class="ph-var-text">FC Promedio</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:var(--ph-rust);">04</div><div class="ph-var-text">Variabilidad FC</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:var(--ph-amber);">05</div><div class="ph-var-text">Temp Promedio</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:var(--ph-coral);">06</div><div class="ph-var-text">Variabilidad Temp</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#EA580C;">07</div><div class="ph-var-text">SpO2 Promedio</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#9A3412;">08</div><div class="ph-var-text">Variabilidad SpO2</div></div>
    </div>
</div>

<!-- TABS -->
<div class="ph-tab-bar">
    <button class="ph-tab active" onclick="switchPhTab('kmeans', this)"><i class="fas fa-project-diagram" style="margin-right:0.3rem;"></i>K-Means</button>
    <button class="ph-tab" onclick="switchPhTab('pca', this)"><i class="fas fa-compress-arrows-alt" style="margin-right:0.3rem;"></i>PCA</button>
</div>

<!-- ====== K-MEANS ====== -->
<div id="ph-tab-kmeans" class="ph-tab-panel active">
    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
        <span style="font-size:0.75rem;font-weight:700;color:#57534E;">Clusters (K):</span>
        <select id="k-value" class="ph-select">
            <option value="2">2</option><option value="3">3</option><option value="4" selected>4</option><option value="5">5</option><option value="6">6</option>
        </select>
        <button id="btn-km" class="btn-ph" onclick="runKm()"><i class="fas fa-bolt"></i> Ejecutar K-Means</button>
        <div class="ph-loading" id="km-load"><i class="fas fa-spinner"></i> Procesando...</div>
    </div>

    <div id="km-out" style="display:none;">
        <div class="ph-score-grid">
            <div class="ph-score-card">
                <div class="label">Silhouette Score</div>
                <div class="value" style="color:var(--ph-primary);" id="km-sil">—</div>
                <div class="sub">-1 a 1 (mayor = mejor)</div>
                <div class="ph-interp" id="km-sil-badge"></div>
            </div>
            <div class="ph-score-card">
                <div class="label">Inercia (SSE)</div>
                <div class="value" style="color:var(--ph-critical);" id="km-ine">—</div>
                <div class="sub">Suma distancias al centroide</div>
                <div class="ph-interp" id="km-ine-badge">No tiene rango fijo: compárala solo contra otros valores de K en la gráfica del codo →</div>
            </div>
            <div class="ph-score-card">
                <div class="label">Iteraciones</div>
                <div class="value" style="color:var(--ph-amber);" id="km-iter">—</div>
                <div class="sub">Para converger</div>
                <div class="ph-interp" id="km-iter-badge"></div>
            </div>
        </div>
        <div class="ph-chart-grid">
            <div class="ph-card">
                <div class="ph-section"><i class="fas fa-chart-line"></i> Metodo del Codo</div>
                <div class="ph-caption">Busca dónde la curva deja de bajar bruscamente: ese "codo" es el K más eficiente, no necesariamente el de menor inercia.</div>
                <canvas id="ch-elbow" height="220"></canvas>
            </div>
            <div class="ph-card">
                <div class="ph-section"><i class="fas fa-chart-bar"></i> Silhouette por K</div>
                <div class="ph-caption">Barras verdes = clusters bien separados. Ámbar o rojas = grupos solapados; considera otro K.</div>
                <canvas id="ch-sil" height="220"></canvas>
            </div>
        </div>
        <div id="ph-cards" class="ph-phenotype-grid"></div>
        <div class="ph-card">
            <div class="ph-section"><i class="fas fa-braille"></i> Distribucion de Clusters</div>
            <div class="ph-caption">Cada punto es un paciente. Ejes: frecuencia cardiaca promedio vs. su variabilidad. Grupos muy mezclados = clusters poco definidos.</div>
            <canvas id="ch-scatter" height="280"></canvas>
        </div>
        <div class="ph-wrap">
            <div class="ph-wrap-head"><h3><i class="fas fa-table"></i> Detalle por Paciente</h3></div>
            <div class="ph-scroll">
                <table class="ph-tbl">
                    <thead><tr><th>Paciente</th><th class="c">Edad</th><th class="c">Triage</th><th class="c">Horas</th><th class="c">Var FC</th><th class="c">SpO2</th><th class="c">Fenotipo</th></tr></thead>
                    <tbody id="km-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ====== PCA ====== -->
<div id="ph-tab-pca" class="ph-tab-panel">
    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
        <span style="font-size:0.75rem;font-weight:700;color:#57534E;">Componentes:</span>
        <select id="pca-comp" class="ph-select">
            <option value="2">2</option><option value="3" selected>3</option><option value="4">4</option>
        </select>
        <button id="btn-pca" class="btn-ph btn-ph-alt" onclick="runPca()"><i class="fas fa-bolt"></i> Ejecutar PCA</button>
        <div class="ph-loading" id="pca-load"><i class="fas fa-spinner"></i> Procesando...</div>
    </div>

    <div id="pca-out" style="display:none;">
        <div class="ph-card">
            <div class="ph-section"><i class="fas fa-chart-bar"></i> Varianza Explicada</div>
            <div class="ph-caption">Cada barra es cuánta información resume ese componente. La línea negra es el acumulado; con ≥70% ya representas bien a los pacientes.</div>
            <canvas id="ch-var" height="180"></canvas>
        </div>
        <div id="pca-cards" class="ph-pca-grid"></div>
        <div class="ph-wrap">
            <div class="ph-wrap-head"><h3><i class="fas fa-th"></i> Matriz de Loadings</h3></div>
            <div class="ph-caption" style="padding:0 1rem .6rem;">Verde = la variable empuja el componente hacia arriba; rojo = lo empuja hacia abajo. Entre más cerca de ±1, más peso tiene.</div>
            <div style="overflow-x:auto;padding:0.5rem;"><table class="ph-ltbl" id="ld-tbl"></table></div>
        </div>
        <div class="ph-card">
            <div class="ph-section"><i class="fas fa-braille"></i> Proyeccion de Pacientes</div>
            <div class="ph-caption">Cada punto es un paciente ubicado según sus dos componentes principales. Puntos cercanos = perfiles clínicos parecidos.</div>
            <canvas id="ch-pca-sc" height="320"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const C=['#F97316','#DC2626','#D97706','#B91C1C','#FB923C','#9A3412'];
const CB=['rgba(249,115,22,0.15)','rgba(220,38,38,0.15)','rgba(217,119,6,0.15)','rgba(185,28,28,0.15)','rgba(251,146,60,0.15)','rgba(154,52,18,0.15)'];
const VN=['Edad','Severidad','FC Prom.','Var. FC','Temp Prom.','Var. Temp','SpO2 Prom.','Var. SpO2'];
let ch={};

function switchPhTab(t, el){
    document.querySelectorAll('.ph-tab').forEach(function(e){e.classList.remove('active');});
    document.querySelectorAll('.ph-tab-panel').forEach(function(e){e.classList.remove('active');});
    document.getElementById('ph-tab-'+t).classList.add('active');
    if(el) el.classList.add('active');
}
function dc(id){if(ch[id]){ch[id].destroy();delete ch[id];}}
function tBadge(l){return 'badge-'+(l||'verde');}

function interpSilhouette(s){
    if(s>=0.70) return {t:'Estructura fuerte: clusters muy bien separados.', cls:'ok'};
    if(s>=0.50) return {t:'Estructura razonable: separación aceptable.', cls:'ok'};
    if(s>=0.25) return {t:'Estructura débil: hay solape entre grupos. Prueba otro K.', cls:'mid'};
    return {t:'Sin estructura clara: los clusters se mezclan demasiado.', cls:'low'};
}
function interpIter(n, maxIter){
    var ref = maxIter || 100;
    var pct = n / ref;
    if(pct <= 0.20) return {t:'Convergencia rápida: los grupos eran fáciles de separar.', cls:'ok'};
    if(pct <= 0.50) return {t:'Convergencia normal para este volumen de datos.', cls:'mid'};
    return {t:'Convergencia lenta: revisa si K es adecuado para tus datos.', cls:'low'};
}
function setInterp(id, obj){
    var el = document.getElementById(id);
    if(!el) return;
    el.textContent = obj.t;
    el.className = 'ph-interp ' + obj.cls;
}

async function runKm(){
    var k=document.getElementById('k-value').value;
    var btn=document.getElementById('btn-km');
    var ld=document.getElementById('km-load');
    btn.disabled=true;ld.classList.add('show');
    try{
        var r=await fetch('/superadmin/phenotyping/kmeans?k='+k);
        var d=await r.json();
        if(d.error){alert(d.error);return;}
        renderKM(d);
    }catch(e){alert('Error: '+e.message);}
    finally{btn.disabled=false;ld.classList.remove('show');}
}

function renderKM(d){
    document.getElementById('km-out').style.display='block';
    document.getElementById('km-sil').textContent=d.silhouette;
    document.getElementById('km-ine').textContent=d.inertia.toLocaleString();
    document.getElementById('km-iter').textContent=d.n_iter;

    setInterp('km-sil-badge', interpSilhouette(parseFloat(d.silhouette)));
    setInterp('km-iter-badge', interpIter(d.n_iter, d.max_iter));

    dc('elbow');
    var elbowLabels=d.elbow.map(function(e){return 'K='+e.k;});
    var elbowData=d.elbow.map(function(e){return e.inertia;});
    ch.elbow=new Chart(document.getElementById('ch-elbow'),{
        type:'line',
        data:{labels:elbowLabels,datasets:[{data:elbowData,borderColor:'#F97316',backgroundColor:'rgba(249,115,22,0.08)',fill:true,tension:0.4,pointRadius:6,pointBackgroundColor:'#F97316',borderWidth:2.5}]},
        options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{grid:{color:'#FED7AA'},ticks:{color:'#78716C',font:{weight:'bold'}}},x:{grid:{display:false},ticks:{color:'#78716C',font:{weight:'bold'}}}}}
    });

    dc('sil');
    var silData=d.elbow.map(function(e){return e.silhouette;});
    var silColors=d.elbow.map(function(e){return e.silhouette>0.5?'#F97316':(e.silhouette>0.25?'#D97706':'#DC2626');});
    ch.sil=new Chart(document.getElementById('ch-sil'),{
        type:'bar',
        data:{labels:elbowLabels,datasets:[{data:silData,backgroundColor:silColors,borderRadius:8,borderSkipped:false}]},
        options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{min:-0.1,max:1,grid:{color:'#FED7AA'},ticks:{color:'#78716C',font:{weight:'bold'}}},x:{grid:{display:false},ticks:{color:'#78716C',font:{weight:'bold'}}}}}
    });

    var cardsHtml='';
    for(var i=0;i<d.cluster_stats.length;i++){
        var s=d.cluster_stats[i];
        var triageParts=[];
        for(var tk in s.triage_dist){triageParts.push(tk+': '+s.triage_dist[tk]);}
        cardsHtml+='<div class="ph-phenotype" style="border-color:'+C[i]+';">';
        cardsHtml+='<div class="name"><div class="dot" style="background:'+C[i]+';"></div><span style="color:'+C[i]+';">'+s.name+'</span></div>';
        cardsHtml+='<div class="row"><span class="k">Pacientes</span><span class="v">'+s.n+' ('+s.pct+'%)</span></div>';
        cardsHtml+='<div class="row"><span class="k">Edad prom.</span><span class="v">'+s.avg_age+' anos</span></div>';
        cardsHtml+='<div class="row"><span class="k">Estancia</span><span class="v">'+s.avg_days+' dias</span></div>';
        cardsHtml+='<div class="row"><span class="k">Rango</span><span class="v">'+Math.round(s.min_hours/24)+'-'+Math.round(s.max_hours/24)+'d</span></div>';
        cardsHtml+='<div class="tri">Triage: '+triageParts.join(', ')+'</div>';
        cardsHtml+='</div>';
    }
    document.getElementById('ph-cards').innerHTML=cardsHtml;

    dc('scatter');
    var sds=[];
    for(var c=0;c<d.k;c++){
        var pts=d.patients.filter(function(p){return p.cluster===c;});
        sds.push({
            label:d.cluster_names[c],
            data:pts.map(function(p){return {x:p.vector[2],y:p.vector[3]};}),
            backgroundColor:CB[c],borderColor:C[c],borderWidth:2,pointRadius:5,pointHoverRadius:8
        });
    }
    ch.scatter=new Chart(document.getElementById('ch-scatter'),{
        type:'scatter',
        data:{datasets:sds},
        options:{responsive:true,plugins:{legend:{position:'top',labels:{font:{weight:'bold',size:11},usePointStyle:true,pointStyle:'circle',color:'#57534E'}}},scales:{x:{title:{display:true,text:'FC Promedio (bpm)',color:'#57534E',font:{weight:'bold'}},grid:{color:'#FED7AA'},ticks:{color:'#78716C'}},y:{title:{display:true,text:'Variabilidad FC (DE)',color:'#57534E',font:{weight:'bold'}},grid:{color:'#FED7AA'},ticks:{color:'#78716C'}}}}
    });

    var sorted=d.patients.slice().sort(function(a,b){return a.cluster-b.cluster;});
    var tbodyHtml='';
    for(var p=0;p<sorted.length;p++){
        var pt=sorted[p];
        tbodyHtml+='<tr>';
        tbodyHtml+='<td style="font-weight:700;">'+pt.patient_name+'</td>';
        tbodyHtml+='<td class="c">'+pt.age+'</td>';
        tbodyHtml+='<td class="c"><span class="badge '+tBadge(pt.triage_level)+'">'+pt.triage_level+'</span></td>';
        tbodyHtml+='<td class="c" style="font-family:monospace;color:#78716C;">'+pt.hours_stay+'h</td>';
        tbodyHtml+='<td class="c" style="font-family:monospace;">'+pt.vector[3].toFixed(1)+'</td>';
        tbodyHtml+='<td class="c" style="font-family:monospace;">'+pt.vector[6].toFixed(1)+'%</td>';
        tbodyHtml+='<td class="c"><span class="badge" style="background:'+CB[pt.cluster]+';color:'+C[pt.cluster]+';">'+pt.cluster_name+'</span></td>';
        tbodyHtml+='</tr>';
    }
    document.getElementById('km-tbody').innerHTML=tbodyHtml;
}

async function runPca(){
    var comp=document.getElementById('pca-comp').value;
    var btn=document.getElementById('btn-pca');
    var ld=document.getElementById('pca-load');
    btn.disabled=true;ld.classList.add('show');
    try{
        var r=await fetch('/superadmin/phenotyping/pca?components='+comp);
        var d=await r.json();
        if(d.error){alert(d.error);return;}
        renderPCA(d);
    }catch(e){alert('Error: '+e.message);}
    finally{btn.disabled=false;ld.classList.remove('show');}
}

function renderPCA(d){
    document.getElementById('pca-out').style.display='block';
    var labels=d.explained_variance_pct.map(function(v,i){return 'PC'+(i+1);});

    dc('var');
    ch.var=new Chart(document.getElementById('ch-var'),{
        type:'bar',
        data:{labels:labels,datasets:[
            {label:'Varianza %',data:d.explained_variance_pct,backgroundColor:C.slice(0,labels.length),borderRadius:8,borderSkipped:false},
            {label:'Acumulado %',data:d.cumulative_variance_pct,type:'line',borderColor:'#1E1A17',pointRadius:6,pointBackgroundColor:'#1E1A17',borderWidth:2,tension:0.15}
        ]},
        options:{responsive:true,scales:{y:{max:100,ticks:{callback:function(v){return v+'%';},color:'#78716C',font:{weight:'bold'}},grid:{color:'#FED7AA'}},x:{grid:{display:false},ticks:{color:'#78716C',font:{weight:'bold'}}}}}
    });

    var pcaCardsHtml='';
    for(var i=0;i<d.component_names.length;i++){
        var name=d.component_names[i];
        var loadings=d.loadings[i];
        pcaCardsHtml+='<div class="ph-pca-card" style="border-top-color:'+C[i]+';">';
        pcaCardsHtml+='<div class="pc-head"><div class="pc-badge" style="background:'+C[i]+';">PC'+(i+1)+'</div><div><div class="pc-name">'+name+'</div><div class="pc-pct">'+d.explained_variance_pct[i]+'% varianza</div></div></div>';
        pcaCardsHtml+='<div style="display:flex;flex-direction:column;gap:0.25rem;">';
        for(var j=0;j<loadings.length;j++){
            var l=loadings[j];
            var lcolor=l>0?'#F97316':'#DC2626';
            var w=Math.min(Math.abs(l)*100,100);
            pcaCardsHtml+='<div style="display:flex;align-items:center;gap:0.35rem;font-size:0.7rem;">';
            pcaCardsHtml+='<span style="width:68px;color:#78716C;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+VN[j]+'</span>';
            pcaCardsHtml+='<div style="flex:1;height:5px;background:#FED7AA;border-radius:3px;overflow:hidden;"><div style="height:100%;width:'+w+'%;background:'+lcolor+';border-radius:3px;"></div></div>';
            pcaCardsHtml+='<span style="width:34px;text-align:right;font-family:monospace;font-weight:700;font-size:0.65rem;color:'+lcolor+';">'+(l>0?'+':'')+l.toFixed(2)+'</span>';
            pcaCardsHtml+='</div>';
        }
        pcaCardsHtml+='</div></div>';
    }
    document.getElementById('pca-cards').innerHTML=pcaCardsHtml;

    var tblHtml='<thead><tr><th>Variable</th>';
    for(var i=0;i<d.component_names.length;i++){
        tblHtml+='<th style="color:white;">PC'+(i+1)+'</th>';
    }
    tblHtml+='</tr></thead><tbody>';
    for(var j=0;j<VN.length;j++){
        tblHtml+='<tr><td>'+VN[j]+'</td>';
        for(var i=0;i<d.loadings.length;i++){
            var val=d.loadings[i][j];
            var bg=val>0.4?'background:#FED7AA':(val<-0.4?'background:#FEE2E2':'');
            var vcolor=val>0?'#C2410C':'#DC2626';
            tblHtml+='<td style="'+bg+'"><span style="color:'+vcolor+';font-weight:700;font-family:monospace;">'+(val>0?'+':'')+val.toFixed(3)+'</span></td>';
        }
        tblHtml+='</tr>';
    }
    tblHtml+='</tbody>';
    document.getElementById('ld-tbl').innerHTML=tblHtml;

    dc('pca-sc');
    ch['pca-sc']=new Chart(document.getElementById('ch-pca-sc'),{
        type:'scatter',
        data:{datasets:[{label:'Pacientes',data:d.patients.map(function(p){return {x:p.cluster_projection[0],y:p.cluster_projection[1]};}),backgroundColor:'rgba(249,115,22,0.2)',borderColor:'#F97316',borderWidth:1.5,pointRadius:5,pointHoverRadius:8}]},
        options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{title:{display:true,text:'PC1 ('+d.explained_variance_pct[0]+'%)',color:'#57534E',font:{weight:'bold'}},grid:{color:'#FED7AA'},ticks:{color:'#78716C'}},y:{title:{display:true,text:'PC2 ('+d.explained_variance_pct[1]+'%)',color:'#57534E',font:{weight:'bold'}},grid:{color:'#FED7AA'},ticks:{color:'#78716C'}}}}
    });
}
</script>
@endsection}
