@extends('superadmin.layout')
@section('title', 'Pulso Operativo SLA')

@section('content')
<style>
    :root {
        --sla-primary: #F97316;
        --sla-primary-light: #FED7AA;
        --sla-bg-warm: #FFF7ED;
        --sla-critical: #DC2626;
        --sla-critical-dark: #B91C1C;
        --sla-warning: #F59E0B;
        --sla-success: #16A34A;
        --sla-text: #111827;
        --sla-text-secondary: #6B7280;
    }

    #sla-dashboard { font-family: 'Inter', system-ui, sans-serif; color: var(--sla-text); background: var(--sla-bg-warm); padding: 1.5rem; border-radius: 20px; }

    @keyframes slaFadeInUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slaBlink { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

    #sla-dashboard .stagger > * { animation: slaFadeInUp .55s cubic-bezier(.16,1,.3,1) both; }
    #sla-dashboard .stagger > *:nth-child(1){animation-delay:.02s}
    #sla-dashboard .stagger > *:nth-child(2){animation-delay:.08s}
    #sla-dashboard .stagger > *:nth-child(3){animation-delay:.14s}
    #sla-dashboard .stagger > *:nth-child(4){animation-delay:.20s}

    /* HEADER */
    #sla-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; background: linear-gradient(120deg, var(--sla-primary), #EF4444); border-radius: 18px; padding: 1.3rem 1.7rem; color:#fff; margin-bottom: 1.7rem; box-shadow: 0 14px 30px -12px rgba(249,115,22,.5); }
    #sla-header .title-wrap { display:flex; align-items:center; gap:14px; }
    #sla-header .icon-badge { width:48px; height:48px; border-radius:14px; background:rgba(255,255,255,.22); display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
    #sla-header h1 { font-size: 1.35rem; font-weight: 800; margin:0; }
    #sla-header .sub { font-size:.8rem; opacity:.92; margin-top:2px; }

    /* MODULE NAV */
    #sla-nav { display:flex; gap:.4rem; background:rgba(255,255,255,.85); backdrop-filter: blur(6px); padding:.4rem; border-radius:14px; flex-wrap:wrap; }
    #sla-nav a { text-decoration:none; padding:.55rem 1rem; border-radius:10px; font-size:.82rem; font-weight:700; color: #9A3412; transition: all .2s ease; white-space:nowrap; display:inline-block; }
    #sla-nav a:hover { background: var(--sla-bg-warm); }
    #sla-nav a.active { color:#fff; background: linear-gradient(135deg, var(--sla-primary), var(--sla-critical)); box-shadow: 0 6px 14px -4px rgba(220,38,38,.5); }

    /* DESCRIPTIVE BANNER */
    #sla-banner { display:flex; align-items:flex-start; gap:12px; background:#fff; border-radius:16px; padding:1.1rem 1.4rem; margin-bottom:1.7rem; border-left:6px solid var(--sla-primary); box-shadow: 0 10px 22px -12px rgba(249,115,22,.25); }
    #sla-banner i { color: var(--sla-primary); font-size:1.2rem; margin-top:2px; }
    #sla-banner p { margin:0; font-weight:700; font-size:.95rem; color: var(--sla-text); }

    /* KPI CARDS */
    #sla-kpis { display:grid; grid-template-columns: repeat(4,1fr); gap:1.3rem; margin-bottom: 2rem; }
    .sla-card { background:#fff; border-radius:16px; padding:1.4rem; box-shadow: 0 10px 22px -8px rgba(249,115,22,.12); border-top: 5px solid var(--sla-primary-light); transition: transform .3s ease, box-shadow .3s ease; }
    .sla-card:hover { transform: translateY(-5px); box-shadow: 0 20px 32px -10px rgba(249,115,22,.22); }
    .sla-card.critical { background: linear-gradient(135deg, #F87171, var(--sla-critical)); color:#fff; border-top-color: transparent; }
    .sla-card-title { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--sla-text-secondary); display:flex; align-items:center; justify-content:space-between; gap:6px; }
    .sla-card.critical .sla-card-title { color: rgba(255,255,255,.85); }
    .sla-card-icon { width:32px; height:32px; border-radius:10px; background: var(--sla-bg-warm); display:flex; align-items:center; justify-content:center; color: var(--sla-primary); font-size:.85rem; flex-shrink:0; }
    .sla-card.critical .sla-card-icon { background: rgba(255,255,255,.22); color:#fff; }
    .sla-card-value { font-size: 2rem; font-weight: 800; margin: .5rem 0 .1rem; line-height:1; }
    .sla-card-sub { font-size: .78rem; font-weight: 600; color: var(--sla-text-secondary); }
    .sla-card.critical .sla-card-sub { color: rgba(255,255,255,.85); }

    /* CHART CARDS */
    #sla-charts-grid { display:grid; grid-template-columns: 1fr 1fr; gap:1.3rem; margin-bottom: 1.3rem; }
    .sla-chart-card { background:#fff; border-radius:16px; padding:1.4rem; box-shadow: 0 10px 22px -8px rgba(249,115,22,.10); border: 1px solid #FDE9D6; }
    .sla-chart-card h2 { font-size: 1rem; font-weight: 800; color: var(--sla-text); margin: 0 0 .2rem; display:flex; align-items:center; gap:8px; }
    .sla-chart-card p { font-size: .78rem; color: var(--sla-text-secondary); margin: 0 0 1rem; }
    .sla-chart-box { height: 340px; position: relative; }

    /* OUTLIERS TABLE */
    #sla-outliers-card { background:#fff; border-radius:16px; padding:1.5rem; box-shadow: 0 10px 22px -8px rgba(220,38,38,.15); border-top: 5px solid var(--sla-critical); margin-bottom: 1.5rem; }
    #sla-outliers-card h2 { color: var(--sla-critical); font-size:1.15rem; font-weight:800; margin:0 0 .3rem; display:flex; align-items:center; gap:8px; }
    #sla-outliers-card > p { font-size:.85rem; color: var(--sla-text-secondary); margin: 0 0 1rem; }
    .sla-table-wrap { overflow-x:auto; }
    .sla-table { width:100%; border-collapse: collapse; font-size:.85rem; }
    .sla-table thead th { text-align:left; background: linear-gradient(135deg, #FFF7ED, #FEF2F2); color:#C2410C; text-transform:uppercase; font-size:.68rem; font-weight:800; letter-spacing:.4px; padding: .8rem 1rem; }
    .sla-table thead th:first-child { border-radius: 10px 0 0 10px; }
    .sla-table thead th:last-child { border-radius: 0 10px 10px 0; }
    .sla-table tbody td { padding: .9rem 1rem; border-bottom: 1px solid #FEF1E4; font-weight:600; color: var(--sla-text); }
    .sla-table tbody tr { animation: slaFadeInUp .4s ease both; }
    .sla-table tbody tr:hover { background: #FEF2F2; }
    .sla-dur { font-weight:800; font-size:1.05rem; color: var(--sla-critical); }
    .sla-dev { font-weight:700; color: var(--sla-primary); }
    .sla-zscore-pill { display:inline-block; padding: 3px 12px; border-radius:20px; font-size:.78rem; font-weight:800; color:#fff; background: linear-gradient(135deg, #F87171, var(--sla-critical)); }

    /* EMPTY STATE */
    #sla-empty { display:flex; align-items:center; gap:14px; background: linear-gradient(135deg, #FFF7ED, #FFFBEB); border: 1px solid #FED7AA; border-radius:16px; padding: 1.3rem 1.5rem; margin-bottom: 1.5rem; }
    #sla-empty .icon { width:46px; height:46px; border-radius:12px; background:#FFEDD5; display:flex; align-items:center; justify-content:center; color: var(--sla-primary); font-size:1.3rem; flex-shrink:0; }
    #sla-empty h3 { margin:0; font-size:1.05rem; font-weight:800; color:#9A3412; }
    #sla-empty p { margin:2px 0 0; color:#B45309; font-size:.85rem; }

    .sla-live-badge { background:rgba(255,255,255,.25); color:#fff; padding:4px 12px; border-radius:20px; font-size:.72rem; font-weight:800; display:inline-flex; align-items:center; gap:6px; }
    .sla-live-badge::before { content:''; width:7px; height:7px; background:#fff; border-radius:50%; animation: slaBlink 1.4s infinite; }

    @media (max-width: 1024px) {
        #sla-kpis { grid-template-columns: repeat(2,1fr); }
        #sla-charts-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        #sla-kpis { grid-template-columns: 1fr; }
        #sla-header { flex-direction:column; align-items:flex-start; }
    }
    @media (prefers-reduced-motion: reduce) {
        #sla-dashboard * { animation: none !important; transition: none !important; }
    }
</style>

<div id="sla-dashboard">

    <!-- HEADER -->
    <div id="sla-header">
        <div class="title-wrap">
            <div class="icon-badge"><i class="fas fa-heartbeat"></i></div>
            <div>
                <h1>Pulso Operativo SLA</h1>
                <div class="sub"><i class="fas fa-wave-square"></i> Detección de anomalías en tiempo real (Desviación &gt; +2σ)</div>
            </div>
        </div>
        <span class="sla-live-badge">EN VIVO</span>
    </div>

    <!-- NAV DE MÓDULOS -->
    <div id="sla-nav" style="margin-bottom: 1.7rem;">
        @foreach($modules as $key => $mod)
            <a href="{{ route('sla.dashboard', ['module' => $key]) }}" class="{{ $module === $key ? 'active' : '' }}">
                <i class="fas {{ $mod['icon'] }}"></i> {{ $mod['label'] }}
            </a>
        @endforeach
    </div>

    <!-- TÍTULO DESCRIPTIVO -->
    <div id="sla-banner">
        <i class="fas fa-lightbulb"></i>
        <p>{{ $descriptiveTitle }}</p>
    </div>

    <!-- KPIS -->
    <div id="sla-kpis" class="stagger">
        <div class="sla-card">
            <div class="sla-card-title">Total Eventos (Mes) <span class="sla-card-icon"><i class="fas fa-calendar-check"></i></span></div>
            <div class="sla-card-value">{{ $stats['count'] }}</div>
            <div class="sla-card-sub">Registrados este período</div>
        </div>
        <div class="sla-card">
            <div class="sla-card-title">Promedio Real <span class="sla-card-icon"><i class="fas fa-stopwatch"></i></span></div>
            <div class="sla-card-value">{{ $stats['mean'] }} <span style="font-size:1.1rem; color:var(--sla-text-secondary); font-weight:700;">min</span></div>
            <div class="sla-card-sub">Duración promedio del proceso</div>
        </div>
        <div class="sla-card">
            <div class="sla-card-title">Límite Máximo (Prom + 2σ) <span class="sla-card-icon"><i class="fas fa-shield-alt"></i></span></div>
            <div class="sla-card-value" style="color: var(--sla-primary);">{{ $stats['threshold'] === PHP_INT_MAX ? 'N/A' : $stats['threshold'] }} <span style="font-size:1.1rem; color:var(--sla-text-secondary); font-weight:700;">min</span></div>
            <div class="sla-card-sub">Umbral estadístico seguro</div>
        </div>
        <div class="sla-card critical">
            <div class="sla-card-title">Anomalías Detectadas <span class="sla-card-icon"><i class="fas fa-triangle-exclamation"></i></span></div>
            <div class="sla-card-value">{{ $stats['outlier_count'] }} 🔴</div>
            <div class="sla-card-sub">Requieren investigación</div>
        </div>
    </div>

    <!-- FILA 1: SCATTER + BOXPLOT -->
    <div id="sla-charts-grid">
        <div class="sla-chart-card">
            <h2><i class="fas fa-braille" style="color: {{ $config['color'] }}"></i>Anomalías Temporales en {{ $config['label'] }}</h2>
            <p>Relación entre la hora del día y la duración. Los puntos rojos superan el límite seguro.</p>
            <div class="sla-chart-box"><canvas id="scatterChart"></canvas></div>
        </div>

        <div class="sla-chart-card">
            <h2><i class="fas fa-box-open" style="color: {{ $config['color'] }}"></i>Dispersión Estadística (Boxplot) de {{ $config['label'] }}</h2>
            <p>Muestra la mediana, rango intercuartílico (Q1-Q3) y valores máximos/mínimos reales.</p>
            <div class="sla-chart-box"><canvas id="boxplotChart"></canvas></div>
        </div>
    </div>

    <!-- FILA 2: HISTOGRAMA + BARRAS COMPARATIVAS -->
    <div id="sla-charts-grid">
        <div class="sla-chart-card">
            <h2><i class="fas fa-chart-bar" style="color: {{ $config['color'] }}"></i>Concentración de Tiempos en {{ $config['label'] }}</h2>
            <p>Frecuencia absoluta. Permite ver rápidamente en qué rango de minutos se concentra la mayoría de los eventos.</p>
            <div class="sla-chart-box"><canvas id="histogramChart"></canvas></div>
        </div>

        <div class="sla-chart-card">
            <h2><i class="fas fa-align-left" style="color: var(--sla-critical);"></i>¿Qué área del hospital es más lenta?</h2>
            <p>Comparación del promedio de duración entre Quirófano, Urgencias y Farmacia este mes.</p>
            <div class="sla-chart-box"><canvas id="barChart"></canvas></div>
        </div>
    </div>

    <!-- TABLA DE OUTLIERS -->
    @if($outliersTable->count() > 0)
    <div id="sla-outliers-card">
        <h2><i class="fas fa-exclamation-triangle"></i>Registro de Eventos Atípicos (Outliers)</h2>
        <p>Estos eventos se desviaron más de 2 desviaciones estándar del comportamiento normal y requieren investigación.</p>
        <div class="sla-table-wrap">
            <table class="sla-table">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Duración Real</th>
                        <th>Desviación del Promedio</th>
                        <th>Z-Score (σ)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outliersTable as $out)
                    <tr>
                        <td style="font-family: 'Courier New', monospace;">{{ $out['fecha'] }}</td>
                        <td><span class="sla-dur">{{ $out['duracion'] }} min</span></td>
                        <td><span class="sla-dev">+{{ $out['desviacion'] }} min</span></td>
                        <td><span class="sla-zscore-pill">{{ $out['z_score'] }}σ</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div id="sla-empty">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <div>
            <h3>Operación Normal</h3>
            <p>No se detectaron anomalías estadísticas (valores &gt; Promedio + 2σ) en este período.</p>
        </div>
    </div>
    @endif
</div>

<!-- LIBRERÍAS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@sgratzl/chartjs-chart-boxplot@4"></script>

<script>
// REGLA DE CONSISTENCIA: El color base cambia según el módulo activo. Solo el Outlier es siempre rojo.
const moduleColor = '{{ $config["color"] }}';
const outlierColor = '#EF4444';

Chart.defaults.font.family = "'Inter', 'Helvetica', 'Arial', sans-serif";
Chart.defaults.color = '#78716C';

// 1. SCATTER PLOT (Adapta su color al módulo)
new Chart(document.getElementById('scatterChart'), {
    type: 'scatter',
    data: {
        datasets: [
            { label: 'Límite SLA', data: [{x:-1,y:@php echo ($stats['threshold'] === PHP_INT_MAX ? 0 : $stats['threshold']); @endphp},{x:24,y:@php echo ($stats['threshold'] === PHP_INT_MAX ? 0 : $stats['threshold']); @endphp}], type:'line', borderColor:'rgba(220,38,38,0.5)', borderWidth:2, borderDash:[10,5], pointRadius:0, order:0 },
            { label: 'Normales', data: @json($normalPoints), backgroundColor: moduleColor, pointRadius: 6, order: 1 },
            { label: 'Anomalías', data: @json($outlierPoints), backgroundColor: outlierColor, pointRadius: 12, pointStyle: 'crossRot', borderWidth: 3, order: 2 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Duración (Minutos)' }, grid: { color: '#FFF1E6' } },
            x: { min: 0, max: 23, title: { display: true, text: 'Hora del Día' }, ticks: { stepSize: 1 }, grid: { color: '#FFF1E6' } }
        },
        plugins: { legend: { position: 'top' }, tooltip: { filter: (t) => t.dataset.label !== 'Límite SLA' } }
    }
});

// 2. BOXPLOT (Adapta su color al módulo)
new Chart(document.getElementById('boxplotChart'), {
    type: 'boxplot',
    data: {
        labels: ['{{ $config["label"] }}'],
        datasets: [{
            label: 'Distribución',
            data: [[ @php echo implode(', ', [$boxplotData['min'], $boxplotData['q1'], $boxplotData['median'], $boxplotData['q3'], $boxplotData['max']]); @endphp ]],
            backgroundColor: moduleColor + '40', // Color con 40% de transparencia
            borderColor: moduleColor,
            borderWidth: 2,
            outlierBackgroundColor: outlierColor,
            outlierRadius: 5
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Minutos' }, grid: { color: '#FFF1E6' } },
            x: { grid: { display: false } }
        }
    }
});

// 3. HISTOGRAMA (Adapta su color al módulo)
const histLabels = @json(array_keys($ranges));
const histData = @json(array_values($ranges));
// Si es la última barra (Anomalía), la pinta de rojo. Si no, usa el color del módulo.
const histColors = histLabels.map(label => label.includes('Anomalía') ? outlierColor : moduleColor);

new Chart(document.getElementById('histogramChart'), {
    type: 'bar',
    data: {
        labels: histLabels,
        datasets: [{ label: 'Frecuencia', data: histData, backgroundColor: histColors, borderRadius: 6, borderWidth: 1 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Cantidad de Eventos' }, ticks: { stepSize: 1 }, grid: { color: '#FFF1E6' } },
            x: { title: { display: true, text: 'Rango de Duración (Min)' }, grid: { display: false } }
        },
        plugins: { legend: { display: false } }
    }
});

// 4. BARRAS HORIZONTALES (Esta SÍ mantiene los 3 colores fijos para comparar)
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: @json(array_column($barData, 'module')),
        datasets: [{
            label: 'Promedio (Min)',
            data: @json(array_column($barData, 'avg')),
            backgroundColor: @json(array_column($barData, 'color')), // Naranja, Azul, Verde
            borderRadius: 6,
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        scales: {
            x: { beginAtZero: true, title: { display: true, text: 'Promedio de Duración (Min)' }, grid: { color: '#FFF1E6' } },
            y: { title: { display: true, text: 'Módulo del Hospital' }, grid: { display: false } }
        },
        plugins: { legend: { display: false } }
    }
});
</script>
@endsection