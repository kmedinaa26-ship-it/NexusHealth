@extends('superadmin.layout')
@section('title', 'Pulso Operativo SLA')
@section('content')
<style>
    :root { --sla-primary: #F97316; --sla-critical: #DC2626; --sla-bg-warm: #FFF7ED; --sla-text: #111827; --sla-text-sec: #6B7280; }
    #sla-dashboard { font-family: 'Inter', system-ui, sans-serif; color: var(--sla-text); background: var(--sla-bg-warm); padding: 1.5rem; border-radius: 20px; }
    @keyframes slaFadeInUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slaBlink { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
    #sla-dashboard .stagger > * { animation: slaFadeInUp .55s cubic-bezier(.16,1,.3,1) both; }
    #sla-dashboard .stagger > *:nth-child(1){animation-delay:.02s}
    #sla-dashboard .stagger > *:nth-child(2){animation-delay:.08s}
    #sla-dashboard .stagger > *:nth-child(3){animation-delay:.14s}
    #sla-dashboard .stagger > *:nth-child(4){animation-delay:.20s}
    #sla-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; background: linear-gradient(120deg, var(--sla-primary), #EF4444); border-radius: 18px; padding: 1.3rem 1.7rem; color:#fff; margin-bottom: 1.7rem; box-shadow: 0 14px 30px -12px rgba(249,115,22,.5); }
    #sla-header .title-wrap { display:flex; align-items:center; gap:14px; }
    #sla-header .icon-badge { width:48px; height:48px; border-radius:14px; background:rgba(255,255,255,.22); display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
    #sla-header h1 { font-size: 1.35rem; font-weight: 800; margin:0; }
    #sla-header .sub { font-size:.8rem; opacity:.92; margin-top:2px; }
    #sla-nav { display:flex; gap:.4rem; background:rgba(255,255,255,.85); backdrop-filter: blur(6px); padding:.4rem; border-radius:14px; flex-wrap:wrap; margin-bottom:1.7rem; }
    #sla-nav a { text-decoration:none; padding:.55rem 1rem; border-radius:10px; font-size:.82rem; font-weight:700; color: #9A3412; transition: all .2s ease; white-space:nowrap; display:inline-block; }
    #sla-nav a:hover { background: var(--sla-bg-warm); }
    #sla-nav a.active { color:#fff; background: linear-gradient(135deg, var(--sla-primary), var(--sla-critical)); box-shadow: 0 6px 14px -4px rgba(220,38,38,.5); }
    #sla-kpis { display:grid; grid-template-columns: repeat(4,1fr); gap:1.3rem; margin-bottom: 1.7rem; }
    .sla-card { background:#fff; border-radius:16px; padding:1.4rem; box-shadow: 0 10px 22px -8px rgba(249,115,22,.12); border-top: 5px solid #FED7AA; transition: transform .3s ease, box-shadow .3s ease; }
    .sla-card:hover { transform: translateY(-5px); box-shadow: 0 20px 32px -10px rgba(249,115,22,.22); }
    .sla-card.critical { background: linear-gradient(135deg, #F87171, var(--sla-critical)); color:#fff; border-top-color: transparent; }
    .sla-card-title { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--sla-text-sec); }
    .sla-card.critical .sla-card-title { color: rgba(255,255,255,.85); }
    .sla-card-value { font-size: 2rem; font-weight: 800; margin: .5rem 0 .1rem; line-height:1; }
    .sla-card-sub { font-size: .78rem; font-weight: 600; color: var(--sla-text-sec); }
    .sla-card.critical .sla-card-sub { color: rgba(255,255,255,.85); }
    .sla-chart-card { background:#fff; border-radius:16px; padding:1.4rem; box-shadow: 0 10px 22px -8px rgba(249,115,22,.10); border: 1px solid #FDE9D6; margin-bottom:1.3rem; }
    .sla-chart-card h2 { font-size: 1rem; font-weight: 800; color: var(--sla-text); margin: 0 0 .2rem; display:flex; align-items:center; gap:8px; }
    .sla-chart-card p { font-size: .78rem; color: var(--sla-text-sec); margin: 0 0 1rem; }
    .sla-chart-box { height: 340px; position: relative; }
    .sla-chart-box-sm { height: 280px; position: relative; }
    #sla-outliers-card { background:#fff; border-radius:16px; padding:1.5rem; box-shadow: 0 10px 22px -8px rgba(220,38,38,.15); border-top: 5px solid var(--sla-critical); margin-bottom: 1.5rem; }
    #sla-outliers-card h2 { color: var(--sla-critical); font-size:1.15rem; font-weight:800; margin:0 0 .3rem; display:flex; align-items:center; gap:8px; }
    #sla-outliers-card > p { font-size:.85rem; color: var(--sla-text-sec); margin: 0 0 1rem; }
    .sla-table { width:100%; border-collapse: collapse; font-size:.85rem; }
    .sla-table thead th { text-align:left; background: linear-gradient(135deg, #FFF7ED, #FEF2F2); color:#C2410C; text-transform:uppercase; font-size:.68rem; font-weight:800; letter-spacing:.4px; padding: .8rem 1rem; }
    .sla-table thead th:first-child { border-radius: 10px 0 0 10px; }
    .sla-table thead th:last-child { border-radius: 0 10px 10px 0; }
    .sla-table tbody td { padding: .9rem 1rem; border-bottom: 1px solid #FEF1E4; font-weight:600; color: var(--sla-text); }
    .sla-dur { font-weight:800; font-size:1.05rem; color: var(--sla-critical); }
    .sla-zscore-pill { display:inline-block; padding: 3px 12px; border-radius:20px; font-size:.78rem; font-weight:800; color:#fff; background: linear-gradient(135deg, #F87171, var(--sla-critical)); }
    #sla-empty { display:flex; align-items:center; gap:14px; background: linear-gradient(135deg, #FFF7ED, #FFFBEB); border: 1px solid #FED7AA; border-radius:16px; padding: 1.3rem 1.5rem; margin-bottom: 1.5rem; }
    #sla-empty .icon { width:46px; height:46px; border-radius:12px; background:#FFEDD5; display:flex; align-items:center; justify-content:center; color: var(--sla-primary); font-size:1.3rem; flex-shrink:0; }
    #sla-empty h3 { margin:0; font-size:1.05rem; font-weight:800; color:#9A3412; }
    #sla-empty p { margin:2px 0 0; color:#B45309; font-size:.85rem; }
    .sla-live-badge { background:rgba(255,255,255,.25); color:#fff; padding:4px 12px; border-radius:20px; font-size:.72rem; font-weight:800; display:inline-flex; align-items:center; gap:6px; }
    .sla-live-badge::before { content:''; width:7px; height:7px; background:#fff; border-radius:50%; animation: slaBlink 1.4s infinite; }
    @media (max-width: 1024px) { #sla-kpis { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 768px) { .grid-3 { grid-template-columns: 1fr !important; } .grid-2 { grid-template-columns: 1fr !important; } #sla-kpis { grid-template-columns: 1fr; } #sla-header { flex-direction:column; align-items:flex-start; } }
    @media (prefers-reduced-motion: reduce) { #sla-dashboard * { animation: none !important; transition: none !important; } }
</style>

<div id="sla-dashboard">
    <div id="sla-header">
        <div class="title-wrap">
            <div class="icon-badge"><i class="fas fa-heartbeat"></i></div>
            <div>
                <h1>Pulso Operativo SLA</h1>
                <div class="sub"><i class="fas fa-wave-square"></i> Deteccion de anomalias en tiempo real (>2.5σ)</div>
            </div>
        </div>
        <span class="sla-live-badge">EN VIVO</span>
    </div>

    <div id="sla-nav">
        @foreach($modules as $key => $mod)
            <a href="{{ route('sla.dashboard', ['module' => $key, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="{{ $module === $key ? 'active' : '' }}">
                <i class="fas {{ $mod['icon'] }}"></i> {{ $mod['label'] }}
            </a>
        @endforeach
    </div>

    <!-- FILTROS -->
    <form method="GET" action="{{ route('sla.dashboard') }}" style="background:#fff; border-radius:12px; padding:.8rem 1.2rem; margin-bottom:1.2rem; border:1px solid #E5E7EB; display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
        <div style="display:flex; flex-direction:column; gap:2px;">
            <label style="font-size:.65rem; font-weight:800; text-transform:uppercase; color:#6B7280;">Desde</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" style="padding:5px 8px; border:1px solid #D1D5DB; border-radius:6px; font-size:.8rem;">
        </div>
        <div style="display:flex; flex-direction:column; gap:2px;">
            <label style="font-size:.65rem; font-weight:800; text-transform:uppercase; color:#6B7280;">Hasta</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" style="padding:5px 8px; border:1px solid #D1D5DB; border-radius:6px; font-size:.8rem;">
        </div>
        <button type="submit" style="padding:6px 18px; background:linear-gradient(135deg,#F97316,#EF4444); color:#fff; border:none; border-radius:6px; font-weight:700; font-size:.8rem; cursor:pointer;"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="{{ route('sla.dashboard', ['module' => $module]) }}" style="padding:6px 14px; background:#F3F4F6; color:#374151; border:1px solid #D1D5DB; border-radius:6px; font-weight:600; font-size:.78rem; text-decoration:none;">Limpiar</a>
    </form>

    <!-- GLOSARIO -->
    <details style="background:#fff; border-radius:14px; padding:0; box-shadow: 0 4px 12px rgba(0,0,0,0.04); margin-bottom:1.7rem; border:1px solid #E5E7EB;">
        <summary style="padding:1rem 1.3rem; cursor:pointer; font-size:.85rem; font-weight:800; color:#374151; display:flex; align-items:center; gap:8px; list-style:none;">
            <i class="fas fa-book" style="color:#6366F1;"></i> Glosario de terminos
            <i class="fas fa-chevron-down" style="margin-left:auto; font-size:.7rem; color:#9CA3AF;"></i>
        </summary>
        <div style="padding:0 1.3rem 1.3rem; display:grid; grid-template-columns:1fr 1fr 1fr; gap:.8rem;">
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #3B82F6;"><div style="font-size:.7rem; font-weight:800; color:#3B82F6;">m</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Minutos — unidad de eje Y</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #8B5CF6;"><div style="font-size:.7rem; font-weight:800; color:#8B5CF6;">hr</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Hora del dia formato 24h</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #EC4899;"><div style="font-size:.7rem; font-weight:800; color:#EC4899;">σ (Sigma)</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Desviacion estandar</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #F59E0B;"><div style="font-size:.7rem; font-weight:800; color:#F59E0B;">+2.5σ</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Limite seguro — cubre 98.7% normal</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #10B981;"><div style="font-size:.7rem; font-weight:800; color:#10B981;">Mediana</div><div style="font-size:.78rem; color:#374151; font-weight:600;">50% de eventos tardan menos</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #EF4444;"><div style="font-size:.7rem; font-weight:800; color:#EF4444;">Q1 / Q3</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Cuartiles — 50% central de datos</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #6366F1;"><div style="font-size:.7rem; font-weight:800; color:#6366F1;">Z-Score</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Magnitud de la anomalia</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #14B8A6;"><div style="font-size:.7rem; font-weight:800; color:#14B8A6;">SLA</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Acuerdo de Nivel de Servicio</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #F97316;"><div style="font-size:.7rem; font-weight:800; color:#F97316;">Outlier</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Evento atipico que supera +2.5σ</div></div>
            <div style="background:#FEF2F2; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #DC2626;"><div style="font-size:.7rem; font-weight:800; color:#DC2626;">Anomalia</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Evento que se desvía mucho del comportamiento normal del módulo. Requiere investigar la causa.</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #7C3AED;"><div style="font-size:.7rem; font-weight:800; color:#7C3AED;">Matutino</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Turno de 7:00 a 14:00 hr</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #D97706;"><div style="font-size:.7rem; font-weight:800; color:#D97706;">Vespertino</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Turno de 15:00 a 22:00 hr</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #1E3A8A;"><div style="font-size:.7rem; font-weight:800; color:#1E3A8A;">Nocturno</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Turno de 23:00 a 6:00 hr</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #059669;"><div style="font-size:.7rem; font-weight:800; color:#059669;">Guardia</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Dia: 7-19 hr / Noche: 19-7 hr</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #0891B2;"><div style="font-size:.7rem; font-weight:800; color:#0891B2;">Scatter</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Grafica de puntos. Cada punto = un evento. Eje X: hora, Eje Y: duración.</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #8B5CF6;"><div style="font-size:.7rem; font-weight:800; color:#8B5CF6;">Boxplot</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Caja con bigotes. Muestra mediana, cuartiles y puntos extremos.</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #EC4899;"><div style="font-size:.7rem; font-weight:800; color:#EC4899;">Doughnut</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Grafica de dona. Muestra proporciones (ej: tipo de día).</div></div>
            <div style="background:#F9FAFB; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #6B7280;"><div style="font-size:.7rem; font-weight:800; color:#6B7280;">Limite SLA</div><div style="font-size:.78rem; color:#374151; font-weight:600;">Promedio + 2.5σ. Todo lo que pasa de aquí es una anomalía.</div></div>
        </div>
    </details>
</div>

    <!-- CONTEXT -->
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr 1fr; gap:.7rem; margin-bottom:1.2rem;">
        <div style="background:#fff; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #3B82F6; box-shadow:0 2px 6px rgba(0,0,0,.03);">
            <div style="font-size:.6rem; font-weight:800; text-transform:uppercase; color:#6B7280;">Periodo</div>
            <div style="font-size:.8rem; font-weight:800; color:#111827; margin-top:2px;">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</div>
        </div>
        <div style="background:#fff; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #8B5CF6; box-shadow:0 2px 6px rgba(0,0,0,.03);">
            <div style="font-size:.6rem; font-weight:800; text-transform:uppercase; color:#6B7280;">Escala Y</div>
            <div style="font-size:.8rem; font-weight:800; color:#111827; margin-top:2px;">0 a {{ $stats['threshold'] === PHP_INT_MAX ? 'N/A' : $stats['threshold'] }}m</div>
        </div>
        <div style="background:#fff; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #10B981; box-shadow:0 2px 6px rgba(0,0,0,.03);">
            <div style="font-size:.6rem; font-weight:800; text-transform:uppercase; color:#6B7280;">Modulo</div>
            <div style="font-size:.8rem; font-weight:800; color:#111827; margin-top:2px;">{{ $config['label'] }} · {{ $stats['count'] }}</div>
        </div>
        <div style="background:#fff; border-radius:10px; padding:.7rem .9rem; border-left:4px solid #059669; box-shadow:0 2px 6px rgba(0,0,0,.03);">
            <div style="font-size:.6rem; font-weight:800; text-transform:uppercase; color:#6B7280;">Promedio</div>
            <div style="font-size:.8rem; font-weight:800; color:#111827; margin-top:2px;">{{ $stats['mean'] }}m · {{ $stats['outlier_count'] }} anom</div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:.3rem;">
            <a href="{{ route('sla.export', ['module' => $module]) }}" style="background:linear-gradient(135deg,#059669,#10B981); color:#fff; border-radius:8px; padding:.5rem; display:flex; flex-direction:column; align-items:center; justify-content:center; text-decoration:none;">
                <i class="fas fa-file-excel" style="font-size:.85rem;"></i>
                <span style="font-size:.58rem; font-weight:800;">CSV</span>
            </a>
            <a href="{{ route('sla.report', ['module' => $module, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" target="_blank" style="background:linear-gradient(135deg,#3B82F6,#6366F1); color:#fff; border-radius:8px; padding:.5rem; display:flex; flex-direction:column; align-items:center; justify-content:center; text-decoration:none;">
                <i class="fas fa-file-pdf" style="font-size:.85rem;"></i>
                <span style="font-size:.58rem; font-weight:800;">Reporte</span>
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div id="sla-kpis" class="stagger">
        <div class="sla-card">
            <div class="sla-card-title">Total Eventos</div>
            <div class="sla-card-value">{{ $stats['count'] }}</div>
            <div class="sla-card-sub">Registrados en el periodo</div>
        </div>
        <div class="sla-card">
            <div class="sla-card-title">Promedio Real</div>
            <div class="sla-card-value">{{ $stats['mean'] }} <span style="font-size:1rem; color:var(--sla-text-sec); font-weight:700;">m</span></div>
            <div class="sla-card-sub">Duracion promedio del proceso</div>
        </div>
        <div class="sla-card">
            <div class="sla-card-title">Limite Max (Prom + 2.5σ)</div>
            <div class="sla-card-value" style="color: var(--sla-primary);">{{ $stats['threshold'] === PHP_INT_MAX ? 'N/A' : $stats['threshold'] }} <span style="font-size:1rem; color:var(--sla-text-sec); font-weight:700;">m</span></div>
            <div class="sla-card-sub">Umbral estadistico seguro</div>
        </div>
        <div class="sla-card critical">
            <div class="sla-card-title">Anomalias Detectadas</div>
            <div class="sla-card-value">{{ $stats['outlier_count'] }} 🔴</div>
            <div class="sla-card-sub">Requieren investigacion</div>
        </div>
    </div>

    <!-- FILA 1: SCATTER + BOXPLOT -->
    <div class="grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:1.3rem; margin-bottom:1.3rem;">
        <div class="sla-chart-card">
            <h2><i class="fas fa-braille" style="color: {{ $config['color'] }}"></i>{{ $chartTitles['scatter'][$module] }}</h2>
            <p>{{ $chartDescriptions['scatter'][$module] }}</p>
            <div class="sla-chart-box"><canvas id="scatterChart"></canvas></div>
        </div>
        <div class="sla-chart-card">
            <h2><i class="fas fa-box-open" style="color: {{ $config['color'] }}"></i>{{ $chartTitles['boxplot'] }} — {{ $config['label'] }}</h2>
            <p>{{ $chartDescriptions['boxplot'][$module] }}</p>
            <div class="sla-chart-box"><canvas id="boxplotChart"></canvas></div>
        </div>
    </div>

    <!-- FILA 2: TURNOS + GUARDIAS + TIPO DIA -->
    <div class="grid-3" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1.3rem; margin-bottom:1.3rem;">
        <div class="sla-chart-card">
            <h2><i class="fas fa-clock" style="color: {{ $config['color'] }}"></i>Turnos — {{ $config['label'] }} {{ $from->format('M/Y') }}</h2>
            <p>Matutino, Vespertino, Nocturno</p>
            <div class="sla-chart-box-sm"><canvas id="shiftsChart"></canvas></div>
        </div>
        <div class="sla-chart-card">
            <h2><i class="fas fa-user-shield" style="color: #6366F1;"></i>Guardias — {{ $config['label'] }} {{ $from->format('M/Y') }}</h2>
            <p>Guardia Dia vs Guardia Noche</p>
            <div class="sla-chart-box-sm"><canvas id="guardiasChart"></canvas></div>
        </div>
        <div class="sla-chart-card">
            <h2><i class="fas fa-calendar-day" style="color: #F59E0B;"></i>Tipo de Dia — {{ $config['label'] }} {{ $from->format('M/Y') }}</h2>
            <p>Laboral, Sabado, Domingo</p>
            <div class="sla-chart-box-sm"><canvas id="dayTypesChart"></canvas></div>
        </div>
    </div>

    <!-- FILA 3: BARRAS -->
    <div style="display:grid; grid-template-columns:1fr; gap:1.3rem; margin-bottom:1.3rem;">
        <div class="sla-chart-card">
            <h2><i class="fas fa-align-left" style="color: var(--sla-critical);"></i>{{ $chartTitles['bar'] }}</h2>
            <p>{{ $chartDescriptions['bar'][$module] }}</p>
            <div class="sla-chart-box-sm"><canvas id="barChart"></canvas></div>
        </div>
    </div>

    <!-- FILA 4: OUTLIERS -->
    <div style="display:grid; grid-template-columns:1fr; gap:1.3rem; margin-bottom:1.3rem;">
        <div class="sla-chart-card">
            <h2><i class="fas fa-exclamation-triangle" style="color: var(--sla-critical);"></i>Outliers vs Limite SLA — {{ $chartTitles['scatter'][$module] }}</h2>
            <p>Cada barra roja es un evento que supero el limite seguro</p>
            <div style="height:250px; position:relative;"><canvas id="outlierBarChart"></canvas></div>
        </div>
    </div>

    <!-- TABLA OUTLIERS -->
    @if($outliersTable->count() > 0)
    <div id="sla-outliers-card">
        <h2><i class="fas fa-exclamation-triangle"></i>Registro de Eventos Atipicos (Outliers)</h2>
        <p>Se desviaron mas de 2.5σ del comportamiento normal en {{ $from->format('d/m/Y') }} a {{ $to->format('d/m/Y') }}. El Z-Score indica la magnitud: mayor a 3σ es severa.</p>
        <div style="overflow-x:auto;">
            <table class="sla-table">
                <thead><tr><th>Fecha/Hora</th><th>Duracion Real</th><th>Desviacion del Promedio</th><th>Z-Score (σ)</th></tr></thead>
                <tbody>
                    @foreach($outliersTable as $out)
                    <tr>
                        <td style="font-family: 'Courier New', monospace;">{{ $out['fecha'] }}</td>
                        <td><span class="sla-dur">{{ $out['duracion'] }} m</span></td>
                        <td><span style="font-weight:700; color: var(--sla-primary);">+{{ $out['desviacion'] }} m</span></td>
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
            <h3>Operacion Normal</h3>
            <p>Sin anomalias en {{ $config['label'] }} del {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}. Todos los eventos quedaron dentro del rango normal.</p>
        </div>
    </div>
    @endif

    

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@sgratzl/chartjs-chart-boxplot@4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
Chart.register(ChartDataLabels);
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#374151';
Chart.defaults.devicePixelRatio = 2;
const mc = '{{ $config["color"] }}';
const oc = '#DC2626';
const gc = '#D1D5DB';
const gw = 1.2;
const af = { size: 13, weight: 'bold', color: '#111827' };
const tf = { size: 11, weight: '600', color: '#4B5563' };
const tt = { backgroundColor: '#1F2937', titleFont: { size: 13, weight: 'bold' }, bodyFont: { size: 12 }, padding: 12, cornerRadius: 10 };
const lim = @php echo ($stats['threshold'] === PHP_INT_MAX ? 0 : $stats['threshold']); @endphp;

// 1. SCATTER
new Chart(document.getElementById('scatterChart'), {
    type: 'scatter',
    data: {
        datasets: [
            { label: 'Limite +2.5σ', data: [{x:-1,y:lim},{x:24,y:lim}], type: 'line', borderColor: 'rgba(220,38,38,0.6)', borderWidth: 2.5, borderDash: [12,6], pointRadius: 0, order: 0, datalabels: { display: false } },
            { label: 'Normales', data: @json($normalPoints), backgroundColor: mc + 'CC', borderColor: mc, borderWidth: 1.5, pointRadius: 4, datalabels: { display: false } },
            { label: 'Anomalias', data: @json($outlierPoints), backgroundColor: 'rgba(220,38,38,0.85)', borderColor: '#991B1B', borderWidth: 2.5, pointRadius: 11, pointStyle: 'crossRot', order: 2, datalabels: { display: true, color: '#FFF', backgroundColor: '#DC2626', borderRadius: 5, font: { size: 9, weight: 'bold' }, anchor: 'end', align: 'top', offset: 8, padding: { top: 3, bottom: 3, left: 5, right: 5 }, formatter: function(v) { return v.y + 'm'; } } }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 20 }, clip: false }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Duracion (m)', font: af }, grid: { color: gc, lineWidth: gw }, ticks: { font: tf, callback: function(v) { return v + 'm'; } }, border: { color: '#6B7280' } }, x: { min: 0, max: 23, title: { display: true, text: 'Hora (hr)', font: af }, ticks: { stepSize: 2, font: tf, callback: function(v) { return v + 'hr'; } }, grid: { color: gc, lineWidth: 0.5 }, border: { color: '#6B7280' } } }, plugins: { legend: { position: 'top', labels: { font: { size: 10, weight: 'bold' }, usePointStyle: true, padding: 12 } }, tooltip: { ...tt, filter: function(t) { return t.dataset.label !== 'Limite +2.5σ'; }, callbacks: { label: function(ctx) { return [ctx.raw.x + 'hr', ctx.raw.y + 'm']; } } } } }
});

// 2. BOXPLOT
new Chart(document.getElementById('boxplotChart'), {
    type: 'boxplot',
    data: { labels: @json(array_column($boxplotAllModules, "label")), datasets: [{ data: @json($boxplotChartData), backgroundColor: @json(array_column($boxplotAllModules, "color")), borderColor: @json(array_column($boxplotAllModules, "color")), borderWidth: 3, outlierBackgroundColor: oc, outlierRadius: 5 }] },
    options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 18 }, clip: false }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Duracion (m)', font: af }, grid: { color: gc, lineWidth: gw }, ticks: { font: tf, callback: function(v) { return v + 'm'; } }, border: { color: '#6B7280' } }, x: { grid: { display: false }, ticks: { font: { size: 11, weight: 'bold' } }, border: { color: '#6B7280' } } }, plugins: { legend: { display: false }, datalabels: { display: true, anchor: 'center', align: 'end', offset: 4, font: { size: 8, weight: '700' }, backgroundColor: 'rgba(255,255,255,0.95)', borderRadius: 4, padding: { left: 3, right: 3 }, formatter: function(v) { return 'Min:' + v[0] + ' Q1:' + v[1] + ' Med:' + v[2] + ' Q3:' + v[3] + ' Max:' + v[4]; } } } }
});

// 3. TURNOS
new Chart(document.getElementById('shiftsChart'), {
    type: 'bar',
    data: { labels: @json(array_keys($shifts)), datasets: [{ data: @json(array_values($shifts)), backgroundColor: ['rgba(59,130,246,0.85)','rgba(245,158,11,0.85)','rgba(30,58,138,0.85)'], borderColor: ['#1D4ED8','#D97706','#1E3A8A'], borderWidth: 2.5, borderRadius: 10, borderSkipped: false, maxBarThickness: 70 }] },
    options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 25 }, clip: false }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Eventos', font: af }, grid: { color: gc, lineWidth: gw }, ticks: { stepSize: 1, font: tf }, border: { color: '#6B7280' } }, x: { grid: { display: false }, ticks: { font: { size: 11, weight: '800', color: '#111827' } }, border: { color: '#6B7280' } } }, plugins: { legend: { display: false }, datalabels: { display: true, anchor: 'end', align: 'end', offset: 5, font: { size: 14, weight: '900' }, color: '#111827', formatter: function(v) { return v; } } } }
});

// 4. GUARDIAS
new Chart(document.getElementById('guardiasChart'), {
    type: 'bar',
    data: { labels: @json(array_keys($guardias)), datasets: [{ data: @json(array_values($guardias)), backgroundColor: ['rgba(16,185,129,0.85)','rgba(99,102,241,0.85)'], borderColor: ['#059669','#4F46E5'], borderWidth: 2.5, borderRadius: 10, borderSkipped: false, maxBarThickness: 70 }] },
    options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 25 }, clip: false }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Eventos', font: af }, grid: { color: gc, lineWidth: gw }, ticks: { stepSize: 1, font: tf }, border: { color: '#6B7280' } }, x: { grid: { display: false }, ticks: { font: { size: 11, weight: '800', color: '#111827' } }, border: { color: '#6B7280' } } }, plugins: { legend: { display: false }, datalabels: { display: true, anchor: 'end', align: 'end', offset: 5, font: { size: 14, weight: '900' }, color: '#111827', formatter: function(v) { return v; } } } }
});

// 5. TIPO DE DIA
new Chart(document.getElementById('dayTypesChart'), {
    type: 'doughnut',
    data: { labels: @json(array_keys($dayTypes)), datasets: [{ data: @json(array_values($dayTypes)), backgroundColor: ['rgba(59,130,246,0.85)','rgba(245,158,11,0.85)','rgba(239,68,68,0.85)'], borderColor: ['#1D4ED8','#D97706','#B91C1C'], borderWidth: 2.5 }] },
    options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 10 } }, plugins: { legend: { position: 'bottom', labels: { font: { size: 10, weight: '700' }, padding: 12, usePointStyle: true } }, datalabels: { display: true, color: '#fff', font: { size: 11, weight: '800' }, formatter: function(v, ctx) { var t = ctx.dataset.data.reduce(function(a,b) { return a+b; }, 0); return v + ' (' + Math.round(v/t*100) + '%)'; } } } }
});

// 6. BARRAS
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: { labels: @json(array_column($barData, 'module')), datasets: [{ data: @json(array_column($barData, 'avg')), backgroundColor: ['rgba(59,130,246,0.85)','rgba(239,68,68,0.85)','rgba(16,185,129,0.85)'], borderColor: ['#1D4ED8','#B91C1C','#059669'], borderWidth: 2.5, borderRadius: 10, borderSkipped: false, barThickness: 40 }] },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, layout: { padding: { right: 20 } }, scales: { x: { beginAtZero: true, title: { display: true, text: 'Promedio (m)', font: af }, grid: { color: gc, lineWidth: gw }, ticks: { font: tf, callback: function(v) { return v + 'm'; } }, border: { color: '#6B7280' } }, y: { grid: { display: false }, ticks: { font: { size: 13, weight: 'bold', color: '#111827' } }, border: { color: '#6B7280' } } }, plugins: { legend: { display: false }, datalabels: { anchor: 'end', align: 'end', offset: 6, font: { size: 13, weight: '800' }, color: '#111827', formatter: function(v) { return v + 'm'; } } } }
});

// 7. OUTLIERS VS LIMITE
var oL = @json($outlierChartData->pluck('label'));
var oD = @json($outlierChartData->pluck('duration'));
var oZ = @json($outlierChartData->pluck('zscore'));
var oDev = @json($outlierChartData->pluck('deviation'));
if (oL.length > 0) {
    new Chart(document.getElementById('outlierBarChart'), {
        type: 'bar',
        data: { labels: oL, datasets: [
            { label: 'Limite +2.5σ', data: oL.map(function() { return lim; }), type: 'line', borderColor: 'rgba(220,38,38,0.7)', borderWidth: 2.5, borderDash: [10,5], pointRadius: 0, order: 0, datalabels: { display: false } },
            { label: 'Duracion outlier', data: oD, backgroundColor: oD.map(function(d,i) { return oZ[i] > 3 ? 'rgba(185,28,28,0.9)' : 'rgba(239,68,68,0.8)'; }), borderColor: oD.map(function(d,i) { return oZ[i] > 3 ? '#7F1D1D' : '#B91C1C'; }), borderWidth: 2, borderRadius: 8, borderSkipped: false, order: 1, datalabels: { display: true, anchor: 'end', align: 'top', offset: 4, font: { size: 8, weight: '700' }, color: '#111827', formatter: function(v, ctx) { return v + 'm (+' + oDev[ctx.dataIndex] + 'm)'; } } }
        ] },
        options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 25 }, clip: false }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Duracion (m)', font: af }, grid: { color: gc, lineWidth: gw }, ticks: { font: tf, callback: function(v) { return v + 'm'; } }, border: { color: '#6B7280' } }, x: { title: { display: true, text: 'Fecha y hora', font: af }, grid: { display: false }, ticks: { font: { size: 10, weight: '600' }, maxRotation: 45 }, border: { color: '#6B7280' } } }, plugins: { legend: { position: 'top', labels: { font: { size: 10, weight: 'bold' }, usePointStyle: true, padding: 12 } }, tooltip: { ...tt, callbacks: { label: function(ctx) { if (ctx.dataset.label === 'Limite +2.5σ') return 'Limite: ' + lim + 'm'; return ['Duracion: ' + ctx.raw + 'm', 'Exceso: +' + oDev[ctx.dataIndex] + 'm', 'Z-Score: ' + oZ[ctx.dataIndex] + 'σ']; } } } } }
    });
} else {
    document.getElementById('outlierBarChart').parentElement.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#9CA3AF;font-size:.9rem;font-weight:600;"><i class="fas fa-check-circle" style="margin-right:8px;color:#10B981;"></i>Sin outliers en este periodo</div>';
}
</script>
@endsection
