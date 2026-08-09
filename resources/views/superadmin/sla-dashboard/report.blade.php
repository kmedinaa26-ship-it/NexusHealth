<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte SLA — Pulso Operativo</title>
<style>
    @page { size: landscape; margin: 12mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', 'Helvetica', 'Arial', sans-serif; color: #111827; font-size: 11px; background: #FFF7ED; display: flex; min-height: 100vh; }

    @keyframes rpFadeInUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes rpFadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* ---------------- SIDEBAR ---------------- */
    .sidebar { width: 220px; background: #FFFFFF; color: #1F2937; padding: 20px 16px; position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; z-index: 100; box-shadow: 6px 0 24px -12px rgba(249,115,22,.25); border-right: 1px solid #FDE9D6; }
    .sidebar h2 { font-size: 1rem; font-weight: 800; margin-bottom: 22px; display: flex; align-items: center; gap: 10px; color: #1F2937; }
    .sidebar h2 .icon-badge { width: 32px; height: 32px; border-radius: 10px; background: linear-gradient(135deg, #F97316, #DC2626); color: #fff; display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; box-shadow: 0 6px 14px -6px rgba(220,38,38,.5); }
    .sidebar .menu-group { margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid #FDE9D6; animation: rpFadeInUp .4s ease both; }
    .sidebar .menu-group:last-child { border-bottom: none; }
    .sidebar .menu-group:nth-child(2) { animation-delay: .04s; }
    .sidebar .menu-group:nth-child(3) { animation-delay: .09s; }
    .sidebar .menu-group:nth-child(4) { animation-delay: .14s; }
    .sidebar .menu-label { font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: #C2410C; opacity: .85; margin-bottom: 8px; }
    .sidebar a { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 8px; color: #57534E; text-decoration: none; font-size: .82rem; font-weight: 600; transition: all .18s ease; margin-bottom: 2px; }
    .sidebar a:hover { background: #FFF1E1; color: #9A3412; transform: translateX(2px); }
    .sidebar a.active { background: linear-gradient(135deg, #F97316, #DC2626); color: #fff; font-weight: 800; box-shadow: 0 8px 16px -8px rgba(220,38,38,.45); }
    .sidebar a i { width: 18px; text-align: center; font-size: .85rem; }

    .saved-item { display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border-radius: 10px; background: #FFF7ED; border: 1px solid #FDE9D6; margin-bottom: 6px; font-size: .78rem; transition: background .18s ease; }
    .saved-item:hover { background: #FFEDD5; }
    .saved-item .date { opacity: .75; font-size: .7rem; color: #6B7280; }
    .saved-item .badge { background: #FFE3C2; color: #9A3412; padding: 2px 8px; border-radius: 8px; font-size: .65rem; font-weight: 700; }

    /* ---------------- MAIN ---------------- */
    .main { margin-left: 220px; flex: 1; padding: 20px 24px; }

    .np { margin-bottom: 14px; display: flex; gap: 10px; animation: rpFadeInUp .35s ease both; }
    .np button, .np a { padding: 9px 20px; border-radius: 10px; font-weight: 700; font-size: .82rem; cursor: pointer; text-decoration: none; border: none; display: flex; align-items: center; gap: 6px; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
    .bp { background: linear-gradient(135deg, #F97316, #DC2626); color: #fff; box-shadow: 0 10px 20px -8px rgba(220,38,38,.5); }
    .bp:hover { transform: translateY(-2px); box-shadow: 0 14px 24px -8px rgba(220,38,38,.6); }
    .bb { background: #fff; color: #9A3412; border: 1.5px solid #FDBA74 !important; }
    .bb:hover { background: #FFF1E1; transform: translateY(-2px); }

    .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; margin-bottom: 16px; animation: rpFadeInUp .4s ease both; }
    .header .title-wrap { display: flex; align-items: center; gap: 12px; }
    .header .icon-badge { width: 42px; height: 42px; border-radius: 13px; background: linear-gradient(135deg, #F97316, #DC2626); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; box-shadow: 0 10px 18px -8px rgba(220,38,38,.5); flex-shrink: 0; }
    .header h1 { font-size: 1.35rem; font-weight: 800; background: linear-gradient(135deg, #EA580C, #DC2626); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .header .meta { text-align: right; font-size: .78rem; color: #6B7280; }
    .header .meta strong { color: #111827; }

    .filters { background: #fff; border: 1px solid #FDE9D6; border-radius: 14px; padding: 14px 18px; margin-bottom: 14px; display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap; box-shadow: 0 6px 18px -10px rgba(249,115,22,.18); animation: rpFadeInUp .4s ease .05s both; }
    .fg { display: flex; flex-direction: column; gap: 4px; }
    .fg label { font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; color: #9A3412; }
    .fg input, .fg select { padding: 6px 11px; border: 1.5px solid #FDE0C6; border-radius: 8px; font-size: .82rem; font-weight: 600; transition: border-color .18s ease, box-shadow .18s ease; }
    .fg input:focus, .fg select:focus { outline: none; border-color: #F97316; box-shadow: 0 0 0 3px rgba(249,115,22,.15); }
    .btn-f { padding: 7px 22px; background: linear-gradient(135deg, #F97316, #EF4444); color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: .82rem; cursor: pointer; box-shadow: 0 8px 16px -8px rgba(220,38,38,.5); transition: transform .18s ease; }
    .btn-f:hover { transform: translateY(-1px); }

    .info-bar { display: flex; gap: 18px; background: #fff; border: 1px solid #FED7AA; border-radius: 12px; padding: 11px 18px; margin-bottom: 14px; font-size: .78rem; color: #9A3412; font-weight: 600; box-shadow: 0 6px 18px -10px rgba(249,115,22,.15); flex-wrap: wrap; animation: rpFadeInUp .4s ease .08s both; }
    .info-bar span { color: #78350F; font-weight: 700; }

    .section { background: #fff; border-radius: 16px; padding: 18px; margin-bottom: 18px; box-shadow: 0 10px 24px -14px rgba(249,115,22,.2); border: 1px solid #FDE9D6; page-break-inside: avoid; transition: box-shadow .25s ease, transform .25s ease; animation: rpFadeInUp .45s ease both; }
    .section:hover { box-shadow: 0 16px 30px -14px rgba(249,115,22,.28); }
    .section-title { font-size: 1rem; font-weight: 800; color: #111827; border-left: 5px solid; padding-left: 12px; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
    .section-desc { font-size: .75rem; color: #6B7280; margin-bottom: 14px; padding-left: 17px; line-height: 1.5; }

    .kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 14px; }
    .kpi { background: #FFF7ED; border: 1px solid #FED7AA; border-radius: 12px; padding: 10px 14px; text-align: center; position: relative; transition: transform .2s ease; }
    .kpi:hover { transform: translateY(-2px); }
    .kpi .kpi-icon { width: 24px; height: 24px; border-radius: 8px; background: #FFEDD5; color: #F97316; display: flex; align-items: center; justify-content: center; margin: 0 auto 4px; font-size: .68rem; }
    .kpi .v { font-size: 1.3rem; font-weight: 800; }
    .kpi .l { font-size: .6rem; color: #6B7280; text-transform: uppercase; letter-spacing: .3px; font-weight: 700; margin-top: 2px; }
    .kpi.cr { background: #FEF2F2; border-color: #FECACA; }
    .kpi.cr .v { color: #DC2626; }
    .kpi.cr .kpi-icon { background: #FEE2E2; color: #DC2626; }

    .cg { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
    .cg3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px; }
    .cc { border: 1px solid #FDE9D6; border-radius: 12px; padding: 12px; background: #FFFDFB; transition: box-shadow .2s ease; }
    .cc:hover { box-shadow: 0 8px 18px -12px rgba(249,115,22,.3); }
    .cc h3 { font-size: .82rem; font-weight: 800; margin-bottom: 2px; color: #111827; }
    .cc .cd { font-size: .68rem; color: #6B7280; margin-bottom: 6px; line-height: 1.4; font-style: italic; }
    .cb { height: 220px; position: relative; }
    .cb-sm { height: 180px; position: relative; }

    .ot { width: 100%; border-collapse: collapse; font-size: .78rem; margin-top: 8px; }
    .ot th { background: linear-gradient(135deg, #FFF7ED, #FEF2F2); color: #991B1B; text-align: left; padding: 7px 10px; font-size: .65rem; text-transform: uppercase; letter-spacing: .3px; font-weight: 800; }
    .ot th:first-child { border-radius: 8px 0 0 8px; }
    .ot th:last-child { border-radius: 0 8px 8px 0; }
    .ot td { padding: 7px 10px; border-bottom: 1px solid #FEF1E4; }
    .ot tr:hover td { background: #FFF7ED; }
    .ot .du { font-weight: 800; color: #DC2626; }

    .no { background: #FFF7ED; border: 1px solid #FED7AA; border-radius: 12px; padding: 11px 16px; color: #9A3412; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .no i { color: #16A34A; }

    .module-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 8px; font-size: .7rem; font-weight: 800; color: #fff; margin-left: auto; }

    @media print {
        .sidebar { display: none !important; }
        .main { margin-left: 0 !important; }
        .np { display: none !important; }
        .section, .cg, .cg3 { box-shadow: none; page-break-inside: avoid; }
        body { background: #fff; }
        * { animation: none !important; transition: none !important; }
        .section:hover, .cc:hover, .kpi:hover { transform: none !important; }
    }
</style>
</head>
<body>

<div class="sidebar">
    <h2><span class="icon-badge"><i class="fas fa-heartbeat"></i></span> Pulso SLA</h2>
    <div class="menu-group">
        <div class="menu-label">Modulos</div>
        <a href="{{ route('sla.report', ['module' => 'all', 'from' => $from, 'to' => $to]) }}" class="{{ $moduleFilter === 'all' ? 'active' : '' }}"><i class="fas fa-layer-group"></i> Todos</a>
        @foreach($modules as $key => $mod)
        <a href="{{ route('sla.report', ['module' => $key, 'from' => $from, 'to' => $to]) }}" class="{{ $moduleFilter === $key ? 'active' : '' }}"><i class="fas {{ $mod['icon'] }}"></i> {{ $mod['label'] }}</a>
        @endforeach
    </div>
    <div class="menu-group">
        <div class="menu-label">Acciones</div>
        <a href="{{ route('sla.dashboard') }}"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="{{ route('sla.export', ['module' => $moduleFilter === 'all' ? 'quirofano' : $moduleFilter]) }}"><i class="fas fa-file-excel"></i> Descargar CSV</a>
    </div>
    <div class="menu-group">
        <div class="menu-label">Gestion de Memoria</div>
        @foreach($savedReports as $sr)
        <div class="saved-item">
            <div>
                <div style="font-size:.75rem;">{{ $sr->title }}</div>
                <div class="date">{{ $sr->created_at->format('d/m/Y H:i') }} · {{ $sr->user_name }}</div>
            </div>
            <div style="display:flex; gap:4px;">
                <span class="badge">{{ $sr->total_events }} evt</span>
                <span class="badge" style="background:#FEE2E2; color:#DC2626;">{{ $sr->total_outliers }} anom</span>
            </div>
        </div>
        @endforeach
        @if($savedReports->count() === 0)
        <div style="font-size:.75rem; color:#9CA3AF; padding:8px;">Sin reportes guardados aun</div>
        @endif
    </div>
</div>

<div class="main">
    <div class="np">
        <button class="bp" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / PDF</button>
        <a href="{{ route('sla.dashboard') }}" class="bb"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
    <div class="header">
        <div class="title-wrap">
            <span class="icon-badge"><i class="fas fa-file-invoice"></i></span>
            <div>
                <h1>Reporte SLA — Pulso Operativo</h1>
                <div style="font-size:.78rem; color:#6B7280;">Deteccion de anomalias en servicios hospitalarios</div>
            </div>
        </div>
        <div class="meta">
            <div>Generado: <strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
            <div>Usuario: <strong>{{ Auth::user()->name }}</strong></div>
        </div>
    </div>
    <form method="GET" action="{{ route('sla.report') }}" class="filters">
        <div class="fg"><label>Desde</label><input type="date" name="from" value="{{ $from }}"></div>
        <div class="fg"><label>Hasta</label><input type="date" name="to" value="{{ $to }}"></div>
        <div class="fg"><label>Modulo</label><select name="module">
            <option value="all" {{ $moduleFilter === 'all' ? 'selected' : '' }}>Todos</option>
            @foreach($modules as $key => $mod)
            <option value="{{ $key }}" {{ $moduleFilter === $key ? 'selected' : '' }}>{{ $mod['label'] }}</option>
            @endforeach
        </select></div>
        <button type="submit" class="btn-f"><i class="fas fa-search"></i> Generar</button>
    </form>
    <div class="info-bar">
        <div>Periodo: <span>{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</span></div>
        <div>Modulo: <span>{{ $moduleFilter === 'all' ? 'Todos' : $modules[$moduleFilter]['label'] }}</span></div>
        <div>Metodo: <span>Z-Score > 2.5σ</span></div>
        <div>Escala: <span>m = minutos, hr = hora, σ = desviacion estandar</span></div>
    </div>

    {{-- COMPARATIVA GLOBAL --}}
    <div class="section">
        <div class="section-title" style="border-color:#F97316;">
            <i class="fas fa-chart-line" style="color:#F97316;"></i> Comparativa entre modulos
            <span class="module-badge" style="background:#F97316;">GLOBAL</span>
        </div>
        <div class="section-desc">
            Esta seccion compara los tres servicios hospitalarios en paralelo. El boxplot muestra la distribucion completa de tiempos (donde esta el 50% central, los extremos y los puntos fuera de rango). La barra horizontal indica cual area es mas rapida en promedio. Un area con caja mas ancha indica mayor variabilidad e inconsistencia en sus procesos.
        </div>
        <div class="cg">
            <div class="cc">
                <h3>Distribucion estadistica (Boxplot)</h3>
                <p class="cd">La caja representa el 50% central de los tiempos. La linea interior es la mediana. Los bigotes muestran el rango normal. Puntos rojos fuera = anomalias.</p>
                <div class="cb"><canvas id="gbp"></canvas></div>
            </div>
            <div class="cc">
                <h3>Velocidad promedio por area</h3>
                <p class="cd">Cual modulo completa sus eventos mas rapido? Si farmacia supera a urgencias en promedio, hay un cuello de botella en triage que requiere atencion.</p>
                <div class="cb"><canvas id="gbar"></canvas></div>
            </div>
        </div>
    </div>

    {{-- DETALLE POR MODULO --}}
    @foreach($reportData as $key => $data)
    @php
        $descriptions = [
            'quirofano' => [
                'scatter' => 'Cada punto es una cirugia registrada en este periodo. El eje X es la hora del dia (0-23hr) y el eje Y la duracion en minutos. La linea roja punteada marca el limite seguro (+2.5σ). Los cruces rojos son cirugias que excedieron ese limite. Si los outliers se concentran en madrugada, el turno nocturno necesita refuerzo quirurgico.',
                'boxplot' => 'Muestra la distribucion estadistica de tiempos quirurgicos. Caja grande = alta variabilidad entre cirugias (procesos inconsistentes). Caja pequena con puntos alejados = pocas pero severas desviaciones que requieren investigacion individual.',
                'shifts' => 'Cuantas cirugias se realizaron en cada turno. Si la mayoria concentra en la manana pero los outliers son nocturnos, el equipo de noche puede estar subdotado.',
                'guardias' => 'Compara actividad entre guardia diurna (7-19hr) y nocturna (19-7hr). Una diferencia extrema sugiere que la cobertura nocturna es insuficiente o que los casos de noche son inherentemente mas complejos.',
                'dayTypes' => 'Distribucion de cirugias por tipo de dia. Si los domingos tienen mayor proporcion de anomalias, revisar la dotacion de personal los fines de semana.',
                'outliers' => 'Cada barra roja es una cirugia anomala. La linea punteada es el limite +2.5σ. Si una barra la supera por mucho, esa cirugia fue excepcionalmente larga y debe investigarse (complejidad, complicaciones, falta de equipo).',
                'bar' => 'Compara el promedio de este modulo contra los demas. Las cirugias son naturalmente las mas largas; lo relevante es la tendencia: si el promedio sube respecto a periodos anteriores, hay un problema de eficiencia quirurgica.'
            ],
            'urgencias' => [
                'scatter' => 'Cada punto es un paciente triageado. El eje X es la hora de llegada y el eje Y el tiempo de clasificacion. Los cruces rojos son triages que tardaron mas de lo normal. Si se agrupan en horarios pico, el problema es saturacion, no competencia del personal.',
                'boxplot' => 'El triage deberia ser un proceso rapido y predecible (caja compacta). Si la caja crece, el proceso se vuelve errático: algunos pacientes se atienden en 2 minutos y otros en 20, indicando falta de protocolo estandar.',
                'shifts' => 'Cuando llegan mas pacientes a urgencias. Si la noche supera a la manana, el flujo de pacientes esta invertido respecto a lo esperado y hay que ajustar recursos.',
                'guardias' => 'La guardia nocturna en urgencias suele ser la mas cargada. Si esta barra domina, confirmar que hay suficiente personal de triage en turno noche.',
                'dayTypes' => 'Los fines de semana suelen incrementar las urgencias. Si los domingos muestran mas anomalias, revisar si la dotacion de fin de semana es proporcional a la demanda.',
                'outliers' => 'Triage lentos identificados. Cada barra es un paciente que espero demasiado para ser clasificado. La linea punteada marca el limite aceptable. Investigar: falta de personal, sistema de triage colapsado, casos complejos.',
                'bar' => 'El triage deberia ser el mas rapido de los tres modulos. Si su promedio se acerca a farmacia o quirofano, hay un problema critico de flujo en urgencias.'
            ],
            'farmacia' => [
                'scatter' => 'Cada punto es una dispensacion de medicamentos. El eje X es la hora y el eje Y el tiempo de entrega. Los cruces rojos indican entregas excesivamente lentas. Un patron de acumulacion en la tarde sugiere cuello de botella por recetas pendientes del dia.',
                'boxplot' => 'La dispensacion deberia ser estable y predecible. Compara contra los otros modulos: si farmacia tiene la caja mas compacta, es el area mejor controlada del hospital.',
                'shifts' => 'Cuando se dispensa mas. Un pico en tarde/noche indica acumulacion de recetas. Si la manana tiene poca actividad, quizas los medicos recetan despues de las rondas.',
                'guardias' => 'Si la guardia nocturna tiene actividad significativa, hay dispensacion de urgencia (antibioticos, analgesicos). Si es casi cero, considerar si el servicio de farmacia nocturna es necesario.',
                'dayTypes' => 'La dispensacion deberia ser relativamente uniforme. Si los sabados dominan, puede deberse a recetas de salida de pacientes de fin de semana.',
                'outliers' => 'Dispensaciones lentas. Cada barra es una receta que tardo demasiado. Causas comunes: medicamento no disponible, verificacion de dosis compleja, falta de stock, receta illegible.',
                'bar' => 'La dispensacion debe ser la mas rapida. Si se acerca a urgencias, hay retrasos sistematicos en la entrega de medicamentos que afectan la experiencia del paciente.'
            ]
        ];
        $desc = $descriptions[$key] ?? $descriptions['quirofano'];
    @endphp

    <div class="section">
        <div class="section-title" style="border-color: {{ $data['config']['color'] }};">
            <i class="fas {{ $data['config']['icon'] }}" style="color:{{ $data['config']['color'] }};"></i>
            {{ $data['config']['label'] }} — Analisis detallado
            <span class="module-badge" style="background:{{ $data['config']['color'] }};">{{ $data['config']['label'] }}</span>
        </div>
        <div class="section-desc">
            Periodo: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}.
            Se registraron {{ $data['stats']['count'] }} eventos con un promedio de {{ $data['stats']['mean'] }} minutos.
            {{ $data['stats']['outlier_count'] > 0 ? 'Se detectaron ' . $data['stats']['outlier_count'] . ' anomalias que requieren atencion.' : 'No se detectaron anomalias en este periodo.' }}
        </div>

        <div class="kpi-row">
            <div class="kpi"><div class="kpi-icon"><i class="fas fa-calendar-check"></i></div><div class="v">{{ $data['stats']['count'] }}</div><div class="l">Eventos {{ $data['config']['label'] }}</div></div>
            <div class="kpi"><div class="kpi-icon"><i class="fas fa-stopwatch"></i></div><div class="v">{{ $data['stats']['mean'] }} m</div><div class="l">Promedio</div></div>
            <div class="kpi"><div class="kpi-icon"><i class="fas fa-shield-alt"></i></div><div class="v">{{ $data['stats']['threshold'] }} m</div><div class="l">Limite +2.5σ</div></div>
            <div class="kpi cr"><div class="kpi-icon"><i class="fas fa-triangle-exclamation"></i></div><div class="v">{{ $data['stats']['outlier_count'] }} 🔴</div><div class="l">Anomalias</div></div>
        </div>

        <div class="cg">
            <div class="cc">
                <h3>Mapa de anomalias temporales — {{ $data['config']['label'] }}</h3>
                <p class="cd">{{ $desc['scatter'] }}</p>
                <div class="cb"><canvas id="sc_{{ $key }}"></canvas></div>
            </div>
            <div class="cc">
                <h3>Boxplot estadistico — {{ $data['config']['label'] }}</h3>
                <p class="cd">{{ $desc['boxplot'] }}</p>
                <div class="cb"><canvas id="bp_{{ $key }}"></canvas></div>
            </div>
        </div>

        <div class="cg3">
            <div class="cc">
                <h3>Eventos por turno</h3>
                <p class="cd">{{ $desc['shifts'] }}</p>
                <div class="cb-sm"><canvas id="sh_{{ $key }}"></canvas></div>
            </div>
            <div class="cc">
                <h3>Eventos por guardia</h3>
                <p class="cd">{{ $desc['guardias'] }}</p>
                <div class="cb-sm"><canvas id="gd_{{ $key }}"></canvas></div>
            </div>
            <div class="cc">
                <h3>Tipo de dia</h3>
                <p class="cd">{{ $desc['dayTypes'] }}</p>
                <div class="cb-sm"><canvas id="dy_{{ $key }}"></canvas></div>
            </div>
        </div>

        <div class="cg">
            <div class="cc">
                <h3>Outliers vs Limite — {{ $data['config']['label'] }}</h3>
                <p class="cd">{{ $desc['outliers'] }}</p>
                <div class="cb"><canvas id="ol_{{ $key }}"></canvas></div>
            </div>
            <div class="cc">
                <h3>Comparativa de velocidad</h3>
                <p class="cd">{{ $desc['bar'] }}</p>
                <div class="cb"><canvas id="bv_{{ $key }}"></canvas></div>
            </div>
        </div>

        @if($data['outliersTable']->count() > 0)
        <div style="margin-top:14px;">
            <h3 style="font-size:.85rem; font-weight:800; margin-bottom:8px; color:#991B1B;">
                <i class="fas fa-exclamation-triangle" style="color:#DC2626;"></i>
                Detalle de anomalias en {{ $data['config']['label'] }} — {{ $data['outliersTable']->count() }} caso(s) que exceden el limite seguro
            </h3>
            <table class="ot">
                <thead><tr><th>Fecha/Hora</th><th>Duracion</th><th>Desviacion sobre el promedio</th><th>Z-Score (que tan extremo)</th></tr></thead>
                <tbody>
                    @foreach($data['outliersTable'] as $out)
                    <tr>
                        <td style="font-family:monospace;">{{ $out['fecha'] }}</td>
                        <td class="du">{{ $out['duracion'] }} m</td>
                        <td style="color:#F97316; font-weight:700;">+{{ $out['desviacion'] }} m sobre el promedio</td>
                        <td><span style="background:#DC2626; color:#fff; padding:2px 8px; border-radius:10px; font-size:.72rem; font-weight:800;">{{ $out['z_score'] }}σ</span> <span style="font-size:.68rem; color:#6B7280;">(limite: 2.5σ)</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="no"><i class="fas fa-check-circle"></i> Sin anomalias en {{ $data['config']['label'] }} en este periodo — Todos los eventos estuvieron dentro del rango esperado</div>
        @endif
    </div>
    @endforeach
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@sgratzl/chartjs-chart-boxplot@4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
Chart.register(ChartDataLabels);
Chart.defaults.font.family = "Inter, sans-serif";
Chart.defaults.color = "#374151";
Chart.defaults.devicePixelRatio = 2;
var gc = "#D1D5DB";
var af = { size: 11, weight: "bold", color: "#111827" };

new Chart(document.getElementById("gbp"), {
    type: "boxplot",
    data: { labels: @json($bpAllLabels), datasets: [{ data: @json($bpAllData), backgroundColor: @json($bpAllColors), borderColor: @json($bpAllColors), borderWidth: 2.5, outlierBackgroundColor: "#DC2626", outlierRadius: 5 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, title: { display: true, text: "Minutos", font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + "m"; } } }, x: { grid: { display: false } } }, plugins: { legend: { display: false }, datalabels: { display: true, anchor: "end", align: "end", offset: 4, font: { size: 8, weight: "700" }, formatter: function(v) { return "Med:" + v[2] + "m"; } } } }
});

new Chart(document.getElementById("gbar"), {
    type: "bar",
    data: { labels: @json(array_column($barData, "module")), datasets: [{ data: @json(array_column($barData, "avg")), backgroundColor: ["rgba(59,130,246,0.8)","rgba(239,68,68,0.8)","rgba(16,185,129,0.8)"], borderColor: ["#1D4ED8","#B91C1C","#059669"], borderWidth: 2, borderRadius: 8 }] },
    options: { indexAxis: "y", responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true, title: { display: true, text: "Minutos", font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + "m"; } } }, y: { grid: { display: false } } }, plugins: { legend: { display: false }, datalabels: { anchor: "end", align: "end", offset: 4, font: { weight: "800" }, formatter: function(v) { return v + "m"; } } } }
});
</script>
<script>
@foreach($reportData as $key => $data)
(function() {
    var normPts = @json($data["normalPoints"]);
    var outPts = @json($data["outlierPoints"]);
    var limit = {{ $data["stats"]["threshold"] === "N/A" ? 0 : $data["stats"]["threshold"] }};
    var modColor = "{{ $data["config"]["color"] }}";
    new Chart(document.getElementById("sc_{{ $key }}"), {
        type: "scatter", data: { datasets: [
            { label: "Limite", data: [{x:-1,y:limit},{x:24,y:limit}], type: "line", borderColor: "rgba(220,38,38,0.5)", borderWidth: 2, borderDash: [8,4], pointRadius: 0, datalabels: { display: false } },
            { label: "Normales", data: normPts, backgroundColor: modColor + "CC", pointRadius: 3, datalabels: { display: false } },
            { label: "Anomalias", data: outPts, backgroundColor: "#DC2626", pointRadius: 7, pointStyle: "crossRot", datalabels: { display: true, font: { size: 8, weight: "bold" }, color: "#DC2626", anchor: "end", align: "top", offset: 6, formatter: function(v) { return v.y + "m"; } } }
        ] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, title: { display: true, text: "Min", font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + "m"; } } }, x: { min: 0, max: 23, title: { display: true, text: "Hora", font: af }, ticks: { callback: function(v) { return v + "hr"; } }, grid: { color: gc, lineWidth: 0.5 } } }, plugins: { legend: { display: false } } }
    });
})();
@endforeach
@foreach($reportData as $key => $data)
(function() {
var bpData = @json([$data["boxplotChartData"]]);    new Chart(document.getElementById("bp_{{ $key }}"), {
        type: "boxplot", data: { labels: ["{{ $data["config"]["label"] }}"], datasets: [{ data: bpData, backgroundColor: "{{ $data["config"]["color"] }}30", borderColor: "{{ $data["config"]["color"] }}", borderWidth: 2.5, outlierBackgroundColor: "#DC2626", outlierRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, title: { display: true, text: "Min", font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + "m"; } } }, x: { grid: { display: false } } }, plugins: { legend: { display: false }, datalabels: { display: true, anchor: "center", align: "end", offset: 3, font: { size: 8, weight: "700" }, backgroundColor: "rgba(255,255,255,0.95)", borderRadius: 3, padding: { left: 3, right: 3 }, formatter: function(v) { return "Med:" + v[2] + "m"; } } } }
    });
})();
@endforeach
@foreach($reportData as $key => $data)
(function() {
    var sL = @json(array_keys($data["shifts"]));
    var sV = @json(array_values($data["shifts"]));
    var sC = ["rgba(59,130,246,0.85)","rgba(245,158,11,0.85)","rgba(30,58,138,0.85)","rgba(99,102,241,0.85)"];
    var sB = ["#1D4ED8","#D97706","#1E3A8A","#4F46E5"];
    new Chart(document.getElementById("sh_{{ $key }}"), { type: "bar", data: { labels: sL, datasets: [{ data: sV, backgroundColor: sC, borderColor: sB, borderWidth: 2.5, borderRadius: 8, maxBarThickness: 60 }] }, options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 25 }, clip: false }, scales: { y: { beginAtZero: true, title: { display: true, text: "Eventos", font: af }, grid: { color: gc }, ticks: { stepSize: 1 }, border: { color: "#6B7280" } }, x: { grid: { display: false }, ticks: { font: { size: 10, weight: "800", color: "#111827" } }, border: { color: "#6B7280" } } }, plugins: { legend: { display: false }, datalabels: { display: true, anchor: "end", align: "end", offset: 5, font: { size: 13, weight: "900" }, color: "#111827", formatter: function(v) { return v; } } } } });
})();
@endforeach
@foreach($reportData as $key => $data)
(function() {
    var gL = @json(array_keys($data["guardias"]));
    var gV = @json(array_values($data["guardias"]));
    new Chart(document.getElementById("gd_{{ $key }}"), { type: "bar", data: { labels: gL, datasets: [{ data: gV, backgroundColor: ["rgba(16,185,129,0.85)","rgba(99,102,241,0.85)"], borderColor: ["#059669","#4F46E5"], borderWidth: 2.5, borderRadius: 8, maxBarThickness: 60 }] }, options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 25 }, clip: false }, scales: { y: { beginAtZero: true, title: { display: true, text: "Eventos", font: af }, grid: { color: gc }, ticks: { stepSize: 1 }, border: { color: "#6B7280" } }, x: { grid: { display: false }, ticks: { font: { size: 10, weight: "800", color: "#111827" } }, border: { color: "#6B7280" } } }, plugins: { legend: { display: false }, datalabels: { display: true, anchor: "end", align: "end", offset: 5, font: { size: 13, weight: "900" }, color: "#111827", formatter: function(v) { return v; } } } } });
})();
@endforeach
@foreach($reportData as $key => $data)
(function() {
    var dL = @json(array_keys($data["dayTypes"]));
    var dV = @json(array_values($data["dayTypes"]));
    new Chart(document.getElementById("dy_{{ $key }}"), { type: "doughnut", data: { labels: dL, datasets: [{ data: dV, backgroundColor: ["rgba(59,130,246,0.85)","rgba(245,158,11,0.85)","rgba(239,68,68,0.85)"], borderColor: ["#1D4ED8","#D97706","#B91C1C"], borderWidth: 2.5 }] }, options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 10 } }, plugins: { legend: { position: "bottom", labels: { font: { size: 10, weight: "700" }, padding: 12, usePointStyle: true } }, datalabels: { display: true, color: "#fff", font: { size: 11, weight: "800" }, formatter: function(v, ctx) { var t = ctx.dataset.data.reduce(function(a,b) { return a+b; }, 0); return t > 0 ? v + " (" + Math.round(v/t*100) + "%)" : v; } } } } });
})();
@endforeach
@foreach($reportData as $key => $data)
(function() {
    var oL = @json($data["outliersTable"]->pluck("fecha"));
    var oD = @json($data["outliersTable"]->pluck("duracion"));
    var lim = {{ $data["stats"]["threshold"] === "N/A" ? 0 : $data["stats"]["threshold"] }};
    if (oL.length > 0) {
        var limitLine = oL.map(function() { return lim; });
        new Chart(document.getElementById("ol_{{ $key }}"), { type: "bar", data: { labels: oL, datasets: [
            { label: "Limite", data: limitLine, type: "line", borderColor: "rgba(220,38,38,0.6)", borderWidth: 2, borderDash: [8,4], pointRadius: 0, datalabels: { display: false } },
            { label: "Anomalia", data: oD, backgroundColor: "rgba(239,68,68,0.8)", borderColor: "#B91C1C", borderWidth: 1.5, borderRadius: 6, datalabels: { anchor: "end", align: "top", font: { size: 8, weight: "800" }, formatter: function(v) { return v + "m"; } } }
        ] }, options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 20 }, clip: false }, scales: { y: { beginAtZero: true, title: { display: true, text: "Min", font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + "m"; } } }, x: { grid: { display: false }, ticks: { font: { size: 9, maxRotation: 45 } } } }, plugins: { legend: { position: "top", labels: { font: { size: 10, weight: "bold" }, usePointStyle: true, padding: 10 } } } } });
    } else {
        var ctx2 = document.getElementById("ol_{{ $key }}").getContext("2d");
        ctx2.fillStyle = "#D1D5DB";
        ctx2.font = "12px Inter, sans-serif";
        ctx2.textAlign = "center";
        ctx2.fillText("Sin anomalias en este periodo", ctx2.canvas.width / 2, ctx2.canvas.height / 2);
    }
})();
@endforeach
@foreach($reportData as $key => $data)
(function() {
    var bL = @json(array_column($barData, "module"));
    var bV = @json(array_column($barData, "avg"));
    new Chart(document.getElementById("bv_{{ $key }}"), { type: "bar", data: { labels: bL, datasets: [{ data: bV, backgroundColor: ["rgba(59,130,246,0.8)","rgba(239,68,68,0.8)","rgba(16,185,129,0.8)"], borderColor: ["#1D4ED8","#B91C1C","#059669"], borderWidth: 2, borderRadius: 8, maxBarThickness: 50 }] }, options: { indexAxis: "y", responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true, title: { display: true, text: "Min", font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + "m"; } } }, y: { grid: { display: false } } }, plugins: { legend: { display: false }, datalabels: { anchor: "end", align: "end", offset: 4, font: { weight: "800" }, formatter: function(v) { return v + "m"; } } } } });
})();
@endforeach
</script>
</body>
</html>