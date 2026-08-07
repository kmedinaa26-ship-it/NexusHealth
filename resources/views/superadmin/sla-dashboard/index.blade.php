@extends('superadmin.layout')
@section('title', 'Pulso Operativo SLA')
@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Pulso Operativo SLA</h1>
            <p class="text-gray-500 mt-1">Detección de anomalías en tiempo real (Desviación > +2σ)</p>
        </div>
        <div class="flex gap-2 mt-4 md:mt-0">
            @foreach($modules as $key => $mod)
                <a href="{{ route('sla.dashboard', ['module' => $key]) }}" 
                   class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $module === $key ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100 border' }}">
                    <i class="fas {{ $mod['icon'] }} mr-1"></i> {{ $mod['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- KPIS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-indigo-500">
            <p class="text-sm font-medium text-gray-500">Total Eventos (Mes)</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['count'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
            <p class="text-sm font-medium text-gray-500">Promedio Real</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $stats['mean'] }} <span class="text-lg text-gray-400">min</span></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-orange-500">
            <p class="text-sm font-medium text-gray-500">Límite Máximo (Prom + 2σ)</p>
            <p class="text-3xl font-bold text-orange-600 mt-2">{{ $stats['threshold'] === PHP_INT_MAX ? 'N/A' : $stats['threshold'] }} <span class="text-lg text-gray-400">min</span></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
            <p class="text-sm font-medium text-gray-500">Anomalías Detectadas</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['outlier_count'] }} 🔴</p>
        </div>
    </div>

    <!-- SCATTER PLOT -->
    <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas {{ $config['icon'] }} text-indigo-500 mr-2"></i>
            Dispersión: {{ $config['label'] }} (Hora vs Duración)
        </h2>
        <div class="relative" style="height: 450px;">
            <canvas id="slaScatterChart"></canvas>
        </div>
    </div>

    <!-- TABLA DE OUTLIERS -->
    @if($outliersTable->count() > 0)
    <div class="bg-white p-6 rounded-xl shadow-sm border-t-4 border-red-500">
        <h2 class="text-xl font-bold text-red-600 mb-4"><i class="fas fa-exclamation-triangle mr-2"></i>Detalle de Anomalías</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 uppercase text-sm">
                        <th class="p-4 border-b">Fecha/Hora</th>
                        <th class="p-4 border-b">Duración Real</th>
                        <th class="p-4 border-b">Desviación del Promedio</th>
                        <th class="p-4 border-b">Z-Score (σ)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outliersTable as $out)
                    <tr class="border-b hover:bg-red-50 transition">
                        <td class="p-4 font-mono text-sm">{{ $out['fecha'] }}</td>
                        <td class="p-4 font-bold text-red-600 text-lg">{{ $out['duracion'] }} min</td>
                        <td class="p-4 text-red-500 font-semibold">+{{ $out['desviacion'] }} min</td>
                        <td class="p-4"><span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-bold">{{ $out['z_score'] }}σ</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl flex items-center">
        <i class="fas fa-check-circle text-2xl mr-4"></i>
        <div>
            <h3 class="font-bold text-lg">Operación Normal</h3>
            <p>No se detectaron anomalías estadísticas (valores > Promedio + 2σ) en este período.</p>
        </div>
    </div>
    @endif
</div>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('slaScatterChart').getContext('2d');
    const slaChart = new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [
                {
                    label: 'Límite SLA (Umbral)',
                    data: [
                        {x: -1, y: @php echo ($stats['threshold'] === PHP_INT_MAX ? 0 : $stats['threshold']); @endphp}, 
                        {x: 24, y: @php echo ($stats['threshold'] === PHP_INT_MAX ? 0 : $stats['threshold']); @endphp}
                    ],
                    type: 'line',
                    borderColor: 'rgba(239, 68, 68, 0.5)',
                    borderWidth: 2,
                    borderDash: [10, 5], // Línea punteada
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    fill: false,
                    order: 0 // Se dibuja detrás de los puntos
                },
                {
                    label: 'Operaciones Normales',
                    data: @json($normalPoints),
                    backgroundColor: '{{ $config["color"] }}',
                    pointRadius: 7,
                    pointHoverRadius: 9,
                    order: 1
                },
                {
                    label: 'Anomalías (Outliers)',
                    data: @json($outlierPoints),
                    backgroundColor: '#EF4444',
                    pointRadius: 14,
                    pointHoverRadius: 17,
                    pointStyle: 'crossRot',
                    borderWidth: 3,
                    borderColor: '#B91C1C',
                    order: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Duración (Minutos)', font: { size: 14, weight: 'bold' } },
                    grid: { color: '#f3f4f6' }
                },
                x: {
                    min: 0, max: 23,
                    title: { display: true, text: 'Hora del Día (0 - 23 hrs)', font: { size: 14, weight: 'bold' } },
                    ticks: { stepSize: 1 },
                    grid: { color: '#f3f4f6' }
                }
            },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, font: { size: 13 } } },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    filter: function (tooltipItem) {
                        // Ocultar tooltip de la línea punteada
                        return tooltipItem.dataset.label !== 'Límite SLA (Umbral)';
                    },
                    callbacks: {
                        label: function(context) {
                            let duration = context.parsed.y;
                            let hour = context.parsed.x;
                            let isOutlier = context.dataset.label.includes('Anomalía');
                            if (isOutlier) {
                                return `⚠️ ANOMALÍA: ${duration} min (Promedio: {{ $stats['mean'] }} min) - Hora: ${hour}:00`;
                            }
                            return `Hora: ${hour}:00 | Duración: ${duration} min`;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
