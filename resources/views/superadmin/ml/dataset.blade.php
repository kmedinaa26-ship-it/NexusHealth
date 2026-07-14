@extends('superadmin.layout')
@section('title', 'Dataset Manager')
@section('nav-ml-dataset', 'active')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <div>
            <h3 style="font-weight:900;color:#1E1A17;"><i class="fas fa-database" style="color:#7C3AED"></i> Dataset Manager</h3>
            <p style="font-size:0.8rem;color:#78716C;">{{ $total }} cerrados | <span style="color:#16A34A;font-weight:700;">{{ $aprobados }} aprobados</span> para entrenamiento</p>
        </div>
        <div style="display:flex;gap:0.5rem;">
            <a href="{{ route('superadmin.ml.dataset.csv') }}" style="padding:0.5rem 1rem;background:#F5F3FF;color:#7C3AED;border:1px solid #DDD6FE;border-radius:8px;font-weight:700;font-size:0.8rem;text-decoration:none;"><i class="fas fa-download"></i> Exportar CSV ({{ $aprobados }})</a>
            <button onclick="retrainConAprobados()" style="padding:0.5rem 1rem;background:#7C3AED;color:white;border:none;border-radius:8px;font-weight:700;font-size:0.8rem;cursor:pointer;"><i class="fas fa-redo"></i> Re-entrenar ({{ $aprobados }})</button>
        </div>
    </div>
    @if($casos->count() > 0)
    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead><tr style="background:#F5F3FF"><th style="padding:0.6rem;text-align:left;color:#7C3AED;">Caso</th><th style="padding:0.6rem;color:#7C3AED;">Doctor</th><th style="padding:0.6rem;color:#7C3AED;">Riesgo</th><th style="padding:0.6rem;color:#7C3AED;">Real</th><th style="padding:0.6rem;color:#7C3AED;">Tipo</th><th style="padding:0.6rem;color:#7C3AED;">Aprobado</th><th style="padding:0.6rem;color:#7C3AED;">Acciones</th></tr></thead>
        <tbody>
        @foreach($casos as $c)
        <?php
            $pAlto = $c->prediccion === 'alto_riesgo';
            $rAlto = $c->resultado_real === 'fallecio';
            $tipo = ($pAlto && $rAlto)?'VP':((!$pAlto && !$rAlto)?'VN':(($pAlto && !$rAlto)?'FP':'FN'));
            $tc = ($tipo==='VP'||$tipo==='VN')?'#16A34A':'#DC2626';
            $aprobado = $c->aprobado_para_entrenamiento ?? false;
        ?>
        <tr style="border-bottom:1px solid #F3F4F6;{{ !$aprobado ? 'opacity:0.6;' : '' }}">
            <td style="padding:0.5rem;font-weight:700;">#{{ $c->id }}</td>
            <td style="padding:0.5rem;color:#57534E;">{{ $c->doctor_name ?? '-' }}</td>
            <td style="padding:0.5rem;font-weight:800;color:{{ $pAlto ? '#DC2626' : '#16A34A' }};">{{ round($c->probabilidad * 100, 1) }}%</td>
            <td style="padding:0.5rem;font-weight:800;color:{{ $rAlto ? '#DC2626' : '#16A34A' }};">{{ $c->resultado_real ?? 'Pendiente' }}</td>
            <td style="padding:0.5rem;"><span style="color:{{$tc}};font-weight:800;">{{ $tipo }}</span></td>
            <td style="padding:0.5rem;text-align:center;">
                <button id="btnApr{{ $c->id }}" onclick="toggleAprobado({{ $c->id }}, {{ $aprobado ? 'true' : 'false' }})" style="padding:0.3rem 0.8rem;background:{{ $aprobado ? '#16A34A' : '#F5F5F4' }};color:{{ $aprobado ? 'white' : '#78716C' }};border:1px solid {{ $aprobado ? '#16A34A' : '#E7E5E4' }};border-radius:6px;font-size:0.75rem;font-weight:800;cursor:pointer;" title="Click para {{ $aprobado ? 'desaprobar' : 'aprobar' }}">
                    <i class="fas fa-{{ $aprobado ? 'check' : 'times' }}"></i> {{ $aprobado ? 'SI' : 'NO' }}
                </button>
            </td>
            <td style="padding:0.5rem;">
                <a href="{{ route('superadmin.ml.explicacion.show', $c->id) }}" style="padding:0.3rem 0.6rem;background:#F5F3FF;color:#7C3AED;border-radius:6px;font-size:0.75rem;font-weight:700;text-decoration:none;"><i class="fas fa-search"></i></a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#78716C;padding:2rem;">Sin casos cerrados</p>
    @endif>
</div>

<div id="toast" style="position:fixed;bottom:2rem;right:2rem;background:#16A34A;color:white;padding:1rem 1.5rem;border-radius:10px;font-weight:700;font-size:0.85rem;box-shadow:0 4px 12px rgba(0,0,0,0.2);display:none;z-index:9999;">
    <i class="fas fa-check-circle"></i> <span id="toastMsg"></span>
</div>

<script>
function toggleAprobado(id, actual) {
    fetch('/superadmin/ml/dataset/toggle', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(r => r.json())
    .then(data => {
        const btn = document.getElementById('btnApr' + id);
        const row = btn.closest('tr');
        if (data.aprobado) {
            btn.innerHTML = '<i class="fas fa-check"></i> SI';
            btn.style.background = '#16A34A'; btn.style.color = 'white'; btn.style.borderColor = '#16A34A';
            row.style.opacity = '1';
        } else {
            btn.innerHTML = '<i class="fas fa-times"></i> NO';
            btn.style.background = '#F5F5F4'; btn.style.color = '#78716C'; btn.style.borderColor = '#E7E5E4';
            row.style.opacity = '0.6';
        }
        showToast(data.aprobado ? 'Caso #' + id + ' aprobado' : 'Caso #' + id + ' desaprobadpado');
        setTimeout(() => location.reload(), 500);
    });
}

function retrainConAprobados() {
    if (!confirm('Re-entrenar con los ' + '{{ $aprobados }}' casos aprobados?')) return;
    fetch('{{ route("superadmin.ml.retrain.execute") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json'}
    })
    .then(r => r.json())
    .then(data => showToast(data.message));
}

function showToast(msg) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 3000);
}
</script>
@endsection
