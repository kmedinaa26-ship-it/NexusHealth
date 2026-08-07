@extends('superadmin.layout')
@section('title', 'SLA y Detección de Anomalías')
@section('content')
<style>
    :root { --sla-orange: #F97316; --sla-red: #DC2626; --sla-bg: #FFF7ED; --sla-text: #1C1917; --sla-sub: #78716C; }
    .sla-card { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.04); margin-bottom: 1.5rem; border-left: 4px solid var(--sla-orange); }
    .sla-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .sla-kpi { background: white; border-radius: 12px; padding: 1.2rem; text-align: center; border: 1px solid #FED7AA; }
    .sla-kpi .val { font-size: 2rem; font-weight: 900; color: var(--sla-text); line-height: 1; }
    .sla-kpi .lbl { font-size: 0.7rem; color: var(--sla-sub); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.4rem; font-weight: 700; }
    .sla-kpi.alert .val { color: var(--sla-red); }
    .sla-chart-wrap { position: relative; height: 450px; background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.04); }
    .sla-anscombe { background: #FFFBEB; border: 1px dashed var(--sla-orange); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.82rem; color: #78350f; line-height: 1.5; }
    .sla-anscombe strong { color: var(--sla-orange); }
</style>

<div class="sla-anscombe">
    <strong>Regla de Anscombe aplicada:</strong> El promedio escondía valores atípicos. Este Scatter Plot expone cada punto individual. Los puntos <span style="color:var(--sla-red); font-weight:800;">ROJOS</span> superan el umbral seguro (Promedio + 2 Desviaciones Estándar), indicando operaciones anómalas que requieren auditoría.
</div>

<div class="sla-grid">
    <div class="sla-kpi"><div class="val">{{ $total }}</div><div class="lbl">Total Servicios</div></div>
    <div class="sla-kpi"><div class="val">{{ $mean ?? 0 }} <span style="font-size:0.9rem;color:var(--sla-sub)">min</span></div><div class="lbl">Tiempo Promedio Real</div></div>
    <div class="sla-kpi"><div class="val">{{ $stdDev ?? 0 }} <span style="font-size:0.9rem;color:var(--sla-sub)">min</span></div><div class="lbl">Desviación Estándar</div></div>
    <div class="sla-kpi alert"><div class="val">{{ $limit ?? 0 }} <span style="font-size:0.9rem;">min</span></div><div class="lbl">Límite Máximo Seguro</div></div>
</div>

<div class="sla-chart-wrap">
    <canvas id="slaScatter"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
    fetch('/superadmin/sla-data').then(r => r.json()).then(data => {
        const normalPts = data.points.filter(p => !p.isOutlier).map(p => ({x: p.x, y: p.y, tipo: p.tipo, duracion: p.y}));
        const outlierPts = data.points.filter(p => p.isOutlier).map(p => ({x: p.x, y: p.y, tipo: p.tipo, duracion: p.y}));
        
        new Chart(document.getElementById('slaScatter'), {
            type: 'scatter',
            data: {
                datasets: [
                    { label: 'Operaciones Normales', data: normalPts, backgroundColor: 'rgba(249,115,22,0.6)', borderColor: '#F97316', pointRadius: 5, pointHoverRadius: 7 },
                    { label: 'ANOMALÍAS DETECTADAS', data: outlierPts, backgroundColor: 'rgba(220,38,38,0.8)', borderColor: '#DC2626', pointRadius: 12, pointHoverRadius: 15, pointStyle: 'crossRot' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { color: '#1C1917', font: { weight: 'bold' } } },
                    tooltip: { callbacks: { label: (ctx) => { const p = ctx.raw; return `Hora: ${Math.floor(p.x)}:00 | Tipo: ${p.tipo} | Duración: ${p.duracion} min`; } } },
                    datalabels: { display: (ctx) => ctx.dataset.data[ctx.dataIndex].isOutlier ? true : false, color: '#fff', backgroundColor: '#DC2626', borderColor: '#991B1B', borderRadius: 4, font: { weight: 'bold', size: 10 }, anchor: 'center', align: 'end', offset: 8,
                        formatter: (value, ctx) => ctx.dataset.data[ctx.dataIndex].isOutler ? '¡ANOMALÍA!' : '' }
                },
                scales: {
                    x: { type: 'linear', min: 0, max: 24, title: { display: true, text: 'Hora del Día (0-24h)', color: '#1C1971', font: { weight: 'bold' } }, grid: { color: '#FED7AA' }, ticks: { color: '#78716C' } },
                    y: { type: 'linear', min: 0, title: { display: true, text: 'Duración (Minutos) - Eje en 0', color: '#1C1971', font: { weight: 'bold' } }, grid: { color: '#FED7AA' }, ticks: { color: '#78716C' } }
                }
            }
        });
    });
</script>
@endsection