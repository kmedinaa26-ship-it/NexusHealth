<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte SLA — Pulso Operativo</title>
<style>
    @page { size: landscape; margin: 15mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', 'Helvetica', 'Arial', sans-serif; color: #111827; font-size: 11px; background: #F9FAFB; }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #F97316; padding: 16px 24px; margin-bottom: 16px; background: #fff; border-radius: 0 0 16px 16px; }
    .header h1 { font-size: 1.4rem; color: #F97316; }
    .header .meta { text-align: right; font-size: .8rem; color: #6B7280; }
    .header .meta strong { color: #111827; }
    .filters { background: #fff; border: 1px solid #E5E7EB; border-radius: 12px; padding: 14px 20px; margin-bottom: 16px; display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; }
    .fg { display: flex; flex-direction: column; gap: 4px; }
    .fg label { font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: #6B7280; }
    .fg input, .fg select { padding: 6px 12px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: .85rem; font-weight: 600; color: #111827; min-width: 160px; }
    .fg input:focus, .fg select:focus { outline: none; border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .btn-f { padding: 7px 24px; background: linear-gradient(135deg, #3B82F6, #6366F1); color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: .85rem; cursor: pointer; }
    .ms { background: #fff; border-radius: 14px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #E5E7EB; page-break-inside: avoid; }
    .mt { font-size: 1.1rem; font-weight: 800; color: #111827; border-left: 5px solid; padding-left: 12px; margin-bottom: 14px; }
    .kr { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 14px; }
    .kpi { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 10px; padding: 10px 14px; text-align: center; }
    .kpi .v { font-size: 1.4rem; font-weight: 800; }
    .kpi .l { font-size: .65rem; color: #6B7280; text-transform: uppercase; letter-spacing: .4px; font-weight: 700; margin-top: 2px; }
    .kpi.cr { background: #FEF2F2; border-color: #FECACA; }
    .kpi.cr .v { color: #DC2626; }
    .cg { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
    .cc { border: 1px solid #E5E7EB; border-radius: 10px; padding: 14px; }
    .cc h3 { font-size: .85rem; font-weight: 800; margin-bottom: 2px; }
    .cc p { font-size: .72rem; color: #6B7280; margin-bottom: 8px; }
    .cb { height: 240px; position: relative; }
    .cf { grid-column: 1 / -1; }
    .ot { width: 100%; border-collapse: collapse; font-size: .8rem; margin-top: 10px; }
    .ot th { background: #FEF2F2; color: #991B1B; text-align: left; padding: 7px 12px; font-size: .68rem; text-transform: uppercase; letter-spacing: .3px; font-weight: 800; }
    .ot th:first-child { border-radius: 8px 0 0 8px; }
    .ot th:last-child { border-radius: 0 8px 8px 0; }
    .ot td { padding: 7px 12px; border-bottom: 1px solid #F3F4F6; }
    .ot .du { font-weight: 800; color: #DC2626; font-size: .9rem; }
    .no { background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 10px; padding: 12px 18px; color: #166534; font-weight: 600; font-size: .85rem; display: flex; align-items: center; gap: 8px; }
    .gs { background: #fff; border-radius: 14px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #E5E7EB; page-break-inside: avoid; }
    .gs h2 { font-size: 1rem; font-weight: 800; margin-bottom: 12px; color: #111827; }
    .np { margin-bottom: 16px; display: flex; gap: 10px; align-items: center; padding: 12px 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .np button, .np a { padding: 9px 22px; border-radius: 8px; font-weight: 700; font-size: .85rem; cursor: pointer; text-decoration: none; border: none; display: flex; align-items: center; gap: 6px; }
    .bp { background: #3B82F6; color: #fff; }
    .bb { background: #6B7280; color: #fff; }
    .bc { background: #059669; color: #fff; }
    .ib { display: flex; gap: 20px; background: #FFF7ED; border: 1px solid #FED7AA; border-radius: 10px; padding: 10px 18px; margin-bottom: 16px; font-size: .8rem; color: #9A3412; font-weight: 600; }
    .ib span { color: #78350F; }
    @media print { .np { display: none !important; } .ms, .gs { box-shadow: none; page-break-inside: avoid; } body { background: #fff; } }
</style>
</head>
<body>

<div class="np">
    <button class="bp" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / PDF</button>
    <a href="{{ route('sla.export', ['module' => $moduleFilter === 'all' ? 'quirofano' : $moduleFilter]) }}" class="bc"><i class="fas fa-file-excel"></i> CSV</a>
    <a href="{{ route('sla.dashboard') }}" class="bb"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="header">
    <div>
        <h1><i class="fas fa-heartbeat"></i> Pulso Operativo SLA — Reporte</h1>
        <div style="font-size:.8rem; color:#6B7280; margin-top:2px;">Deteccion de anomalias en servicios hospitalarios</div>
    </div>
    <div class="meta">
        <div>Generado: <strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
        <div>Usuario: <strong>{{ Auth::user()->name }}</strong></div>
    </div>
</div>

<div class="np">
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
</div>

<div class="ib">
    <div>Periodo: <span>{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</span></div>
    <div>Modulo: <span>{{ $moduleFilter === 'all' ? 'Todos' : $modules[$moduleFilter]['label'] }}</span></div>
    <div>Metodo: <span>Z-Score > 2.5σ</span></div>
    <div>Escala: <span>m = minutos, hr = hora, σ = desviacion estandar</span></div>
</div>

<div class="gs">
    <h2>Comparativa entre modulos</h2>
    <div class="cg">
        <div class="cc"><h3>Distribucion estadistica</h3><p>Mediana, Q1, Q3 de cada modulo</p><div class="cb"><canvas id="gbp"></canvas></div></div>
        <div class="cc"><h3>Velocidad promedio</h3><p>Promedio de duracion por area</p><div class="cb"><canvas id="gbar"></canvas></div></div>
    </div>
</div>

@foreach($reportData as $key => $data)
<div class="ms">
    <div class="mt" style="border-color: {{ $data['config']['color'] }};">
        <i class="fas {{ $data['config']['icon'] }}" style="color:{{ $data['config']['color'] }};"></i>
        {{ $data['config']['label'] }} — {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
    </div>
    <div class="kr">
        <div class="kpi"><div class="v">{{ $data['stats']['count'] }}</div><div class="l">Eventos</div></div>
        <div class="kpi"><div class="v">{{ $data['stats']['mean'] }} m</div><div class="l">Promedio</div></div>
        <div class="kpi"><div class="v">{{ $data['stats']['threshold'] }} m</div><div class="l">Limite +2.5σ</div></div>
        <div class="kpi cr"><div class="v">{{ $data['stats']['outlier_count'] }} 🔴</div><div class="l">Anomalias</div></div>
    </div>
    <div class="cg">
        <div class="cc"><h3>Anomalias temporales</h3><p>Hora vs duracion. Puntos rojos = outliers</p><div class="cb"><canvas id="sc_{{ $key }}"></canvas></div></div>
        <div class="cc"><h3>Actividad por turno</h3><p>Eventos por turno hospitalario</p><div class="cb"><canvas id="sh_{{ $key }}"></canvas></div></div>
        <div class="cc cf"><h3>Outliers vs Limite SLA</h3><p>Barras = duracion outlier. Linea = limite +2.5σ</p><div class="cb" style="height:200px;"><canvas id="ol_{{ $key }}"></canvas></div></div>
    </div>
    @if($data['outliersTable']->count() > 0)
    <table class="ot">
        <thead><tr><th>Fecha/Hora</th><th>Duracion</th><th>Desviacion</th><th>Z-Score</th></tr></thead>
        <tbody>
            @foreach($data['outliersTable'] as $out)
            <tr><td style="font-family:monospace;">{{ $out['fecha'] }}</td><td class="du">{{ $out['duracion'] }} m</td><td style="color:#F97316; font-weight:700;">+{{ $out['desviacion'] }} m</td><td><span style="background:#DC2626; color:#fff; padding:2px 10px; border-radius:12px; font-size:.75rem; font-weight:800;">{{ $out['z_score'] }}σ</span></td></tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no"><i class="fas fa-check-circle"></i> Sin anomalias en {{ $data['config']['label'] }} en este periodo</div>
    @endif
</div>
@endforeach

    @php
    $auditLogs = \App\Models\AuditLog::where(function($q) { $q->where("action", "report")->orWhere("action", "export")->orWhere("action", "login"); })->orderBy("created_at", "desc")->limit(15)->get();
    @endphp
    <div class="gs">
        <h2><i class="fas fa-history" style="color:#6366F1;"></i> Historial de Auditoria</h2>
        <p style="font-size:.78rem; color:#6B7280; margin-bottom:12px;">Ultimas acciones registradas en el sistema</p>
        <table class="ot">
            <thead><tr><th>Fecha/Hora</th><th>Usuario</th><th>Accion</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
            @foreach($auditLogs as $log)
                <tr>
                    <td style="font-family:monospace;font-size:.78rem;">{{ $log->created_at->format("d/m/Y H:i") }}</td>
                    <td style="font-weight:700;">{{ $log->user_name }}</td>
                    <td><span style="background:{{ $log->action === "report" ? "#3B82F6" : ($log->action === "export" ? "#059669" : "#6B7280") }};color:#fff;padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:700;">{{ strtoupper($log->action) }}</span></td>
                    <td style="color:#6B7280;">{{ $log->details }}</td>
                    <td style="font-family:monospace;font-size:.75rem;color:#9CA3AF;">{{ $log->ip_address ?? "N/A" }}</td>
                </tr>
            @endforeach
            @if($auditLogs->count() === 0)
                <tr><td colspan="5" style="text-align:center;color:#9CA3AF;padding:16px;">Sin registros de auditoria aun</td></tr>
            @endif
            </tbody>
        </table>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@sgratzl/chartjs-chart-boxplot@4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
Chart.register(ChartDataLabels);
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = "#374151";
Chart.defaults.devicePixelRatio = 2;
const gc = '#D1D5DB';
const af = { size: 12, weight: 'bold', color: '#111827' };

// Variables globales
const gLabels = @json($bpAllLabels);
const gColors = @json($bpAllColors);
const gData = @json($bpAllData);
const gBgColors = gColors.map(function(c) { return c + '30'; });

const bLabels = @json(array_column($barData, 'module'));
const bValues = @json(array_column($barData, 'avg'));

// GLOBAL BOXPLOT
new Chart(document.getElementById('gbp'), {
    type: 'boxplot',
    data: { labels: gLabels, datasets: [{ data: gData, backgroundColor: gBgColors, borderColor: gColors, borderWidth: 2.5, outlierBackgroundColor: '#DC2626', outlierRadius: 5 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, title: { display: true, text: 'm', font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + 'm'; } } }, x: { grid: { display: false } } }, plugins: { legend: { display: false }, datalabels: { display: true, anchor: 'end', align: 'end', offset: 6, font: { size: 8, weight: '700' }, backgroundColor: 'rgba(255,255,255,0.9)', borderRadius: 4, padding: { left: 4, right: 4 }, formatter: function(v) { return 'Med:' + v[2] + 'm'; } } } }
});

// GLOBAL BAR
new Chart(document.getElementById('gbar'), {
    type: 'bar',
    data: { labels: bLabels, datasets: [{ data: bValues, backgroundColor: ['rgba(59,130,246,0.8)','rgba(239,68,68,0.8)','rgba(16,185,129,0.8)'], borderColor: ['#1D4ED8','#B91C1C','#059669'], borderWidth: 2, borderRadius: 8 }] },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { beginAtZero: true, title: { display: true, text: 'm', font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + 'm'; } } }, y: { grid: { display: false } } }, plugins: { legend: { display: false }, datalabels: { anchor: 'end', align: 'end', offset: 4, font: { weight: '800' }, formatter: function(v) { return v + 'm'; } } } }
});

@foreach($reportData as $key => $data)
// SCATTER {{ strtoupper($key) }}
(function() {
    var normPts = @json($data['normalPoints']);
    var outPts = @json($data['outlierPoints']);
    var limit = {{ $data['stats']['threshold'] === 'N/A' ? 0 : $data['stats']['threshold'] }};
    var modColor = '{{ $data['config']['color'] }}';

    new Chart(document.getElementById('sc_{{ $key }}'), {
        type: 'scatter',
        data: { datasets: [
            { label: 'Limite', data: [{x:-1,y:limit},{x:24,y:limit}], type: 'line', borderColor: 'rgba(220,38,38,0.5)', borderWidth: 2, borderDash: [8,4], pointRadius: 0, datalabels: { display: false } },
            { label: 'Normales', data: normPts, backgroundColor: modColor + 'CC', pointRadius: 4, datalabels: { display: false } },
            { label: 'Anomalias', data: outPts, backgroundColor: '#DC2626', pointRadius: 8, pointStyle: 'crossRot', datalabels: { display: true, font: { size: 8, weight: 'bold' }, color: '#DC2626', anchor: 'end', align: 'top', offset: 4, formatter: function(v) { return v.y + 'm'; } } }
        ] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, title: { display: true, text: 'm', font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + 'm'; } } }, x: { min: 0, max: 23, ticks: { callback: function(v) { return v + 'hr'; } }, grid: { color: gc, lineWidth: 0.5 } } }, plugins: { legend: { display: false } } }
    });
})();

// SHIFTS {{ strtoupper($key) }}
(function() {
    var sLabels = @json(array_keys($data['shifts']));
    var sValues = @json(array_values($data['shifts']));

    new Chart(document.getElementById('sh_{{ $key }}'), {
        type: 'bar',
        data: { labels: sLabels, datasets: [{ data: sValues, backgroundColor: ['rgba(99,102,241,0.8)','rgba(245,158,11,0.8)','rgba(239,68,68,0.8)','rgba(30,58,138,0.8)'], borderRadius: 6, borderWidth: 1, borderColor: ['#4F46E5','#D97706','#B91C1C','#1E3A8A'] }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: gc } }, x: { grid: { display: false } } }, plugins: { legend: { display: false }, datalabels: { anchor: 'end', align: 'end', font: { weight: '800' }, formatter: function(v) { return v; } } } }
    });
})();

// OUTLIERS {{ strtoupper($key) }}
@if(count($data['outlierPoints']) > 0)
(function() {
    var oLabels = @json($data['outliersTable']->pluck('fecha'));
    var oDurs = @json($data['outliersTable']->pluck('duracion'));
    var limit = {{ $data['stats']['threshold'] === 'N/A' ? 0 : $data['stats']['threshold'] }};
    var limitLine = oLabels.map(function() { return limit; });

    new Chart(document.getElementById('ol_{{ $key }}'), {
        type: 'bar',
        data: { labels: oLabels, datasets: [
            { label: 'Limite', data: limitLine, type: 'line', borderColor: 'rgba(220,38,38,0.6)', borderWidth: 2, borderDash: [8,4], pointRadius: 0, datalabels: { display: false } },
            { label: 'Outlier', data: oDurs, backgroundColor: 'rgba(239,68,68,0.8)', borderColor: '#B91C1C', borderWidth: 1.5, borderRadius: 6, datalabels: { anchor: 'end', align: 'top', font: { size: 8, weight: '800' }, formatter: function(v) { return v + 'm'; } } }
        ] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, title: { display: true, text: 'm', font: af }, grid: { color: gc }, ticks: { callback: function(v) { return v + 'm'; } } }, x: { grid: { display: false }, ticks: { font: { size: 8 }, maxRotation: 45 } } }, plugins: { legend: { display: false } } }
    });
})();
@endif
@endforeach
</script>
</body>
</html>
