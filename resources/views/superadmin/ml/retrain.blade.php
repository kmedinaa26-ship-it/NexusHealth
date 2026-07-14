@extends('superadmin.layout')
@section('title', 'Re-entrenar Modelo')
@section('nav-ml-retrain', 'active')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
    <div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;text-align:center;">
        <div style="font-size:2.5rem;font-weight:900;color:#7C3AED;">{{ $totalCasos }}</div>
        <div style="font-size:0.8rem;font-weight:700;color:#78716C;">Casos Cerrados Disponibles</div>
    </div>
    <div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #059669;text-align:center;">
        <div style="font-size:2.5rem;font-weight:900;color:#059669;">{{ $ultimaVersion ? $ultimaVersion->nombre : 'N/A' }}</div>
        <div style="font-size:0.8rem;font-weight:700;color:#78716C;">Ultima Version Entrenada</div>
    </div>
</div>

<div style="background:white;border-radius:14px;padding:2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;text-align:center;">
    <i class="fas fa-brain" style="font-size:3rem;color:#7C3AED;margin-bottom:1rem;"></i>
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:0.5rem;">Re-entrenar Modelo con Datos Actualizados</h3>
    <p style="font-size:0.85rem;color:#78716C;margin-bottom:1.5rem;max-width:500px;margin-left:auto;margin-right:auto;">Se creara una nueva version del modelo usando los {{ $totalCasos }} casos cerrados. El modelo anterior se conserva como rollback.</p>
    <button id="btnRetrain" onclick="retrainModel()" style="padding:0.8rem 2.5rem;background:linear-gradient(135deg,#7C3AED,#5B21B6);color:white;border:none;border-radius:10px;font-weight:800;font-size:1rem;cursor:pointer;"><i class="fas fa-redo"></i> Re-entrenar Modelo</button>
    <div id="retrainResult" style="margin-top:1rem;display:none;"></div>
</div>

<script>
function retrainModel() {
    const btn = document.getElementById('btnRetrain');
    const result = document.getElementById('retrainResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrenando...';
    btn.style.opacity = '0.7';
    
    fetch('{{ route("superadmin.ml.retrain.execute") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        result.style.display = 'block';
        result.innerHTML = '<div style="background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;padding:1rem;border-radius:10px;font-weight:700;"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
        btn.innerHTML = '<i class="fas fa-check"></i> Completado';
        btn.style.background = '#16A34A';
    })
    .catch(err => {
        result.style.display = 'block';
        result.innerHTML = '<div style="background:#FEF2F2;border:1px solid #FCA5A5;color:#991B1B;padding:1rem;border-radius:10px;font-weight:700;"><i class="fas fa-times-circle"></i> Error al re-entrenar</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-redo"></i> Reintentar';
        btn.style.opacity = '1';
    });
}
</script>
@endsection
