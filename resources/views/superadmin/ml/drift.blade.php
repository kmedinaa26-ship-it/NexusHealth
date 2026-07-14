@extends('superadmin.layout')
@section('title', 'Drift del Modelo')
@section('nav-ml-drift', 'active')

@section('content')
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;margin-bottom:1.5rem;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:0.5rem;"><i class="fas fa-chart-line" style="color:#7C3AED"></i> Data Drift - Envejecimiento del Modelo</h3>
    <p style="font-size:0.8rem;color:#78716C;margin-bottom:1.5rem;">Seguimiento del F1-Score en el tiempo. Si baja, el modelo necesita re-entrenarse.</p>
    
    <!-- Grafica simulada del drift -->
    <div style="border:1px solid #E5E7EB;border-radius:10px;padding:1rem;margin-bottom:1.5rem;">
        <div style="display:flex;justify-content:space-between;font-size:0.7rem;color:#A8A29E;margin-bottom:0.5rem;">
            <span>Enero</span><span>Febrero</span><span>Marzo</span><span>Abril</span><span>Mayo</span><span>Junio</span><span>Julio</span><span>Hoy</span>
        </div>
        <div style="height:180px;display:flex;align-items:flex-end;gap:6px;padding-bottom:2rem;border-bottom:2px solid #E5E7EB;position:relative;">
            <!-- Linea de alerta al 70% -->
            <div style="position:absolute;left:0;right:0;bottom:calc(2rem + 126px);border-top:2px dashed #DC2626;height:0;">
                <span style="position:absolute;right:0;top:-16px;font-size:0.6rem;color:#DC2626;font-weight:800;">Alerta 70%</span>
            </div>
            <div style="flex:1;background:#7C3AED;height:88%;border-radius:4px 4px 0 0;position:relative;" title="88%"><span style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#7C3AED;">88%</span></div>
            <div style="flex:1;background:#7C3AED;height:86%;border-radius:4px 4px 0 0;position:relative;" title="86%"><span style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#7C3AED;">86%</span></div>
            <div style="flex:1;background:#7C3AED;height:82%;border-radius:4px 4px 0 0;position:relative;" title="82%"><span style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#7C3AED;">82%</span></div>
            <div style="flex:1;background:#EA580C;height:78%;border-radius:4px 4px 0 0;position:relative;" title="78%"><span style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#EA580C;">78%</span></div>
            <div style="flex:1;background:#EA580C;height:74%;border-radius:4px 4px 0 0;position:relative;" title="74%"><span style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#EA580C;">74%</span></div>
            <div style="flex:1;background:#DC2626;height:68%;border-radius:4px 4px 0 0;position:relative;" title="68%"><span style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#DC2626;">68%</span></div>
            <div style="flex:1;background:#DC2626;height:63%;border-radius:4px 4px 0 0;position:relative;" title="63%"><span style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#DC2626;">63%</span></div>
            <div style="flex:1;background:linear-gradient(to top,#7C2D12,#991B1B);height:58%;border-radius:4px 4px 0 0;position:relative;" title="58%"><span style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);font-size:0.65rem;font-weight:900;color:#DC2626;">58%</span></div>
        </div>
    </div>

    <!-- Alerta de drift -->
    <div style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:10px;padding:1rem;display:flex;align-items:center;gap:1rem;">
        <i class="fas fa-exclamation-triangle" style="font-size:1.5rem;color:#DC2626;"></i>
        <div>
            <p style="font-weight:800;color:#991B1B;font-size:0.9rem;">ALERTA: Data Drift Detectado</p>
            <p style="font-size:0.8rem;color:#78716C;">El F1-Score bajo de 88% a 58% en 7 meses. El modelo se esta quedando viejo con los datos nuevos. Se recomienda re-entrenar con el dataset actualizado.</p>
        </div>
        <a href="{{ route('superadmin.ml.retrain') }}" style="margin-left:auto;padding:0.5rem 1rem;background:#DC2626;color:white;border:none;border-radius:8px;font-weight:700;font-size:0.8rem;text-decoration:none;white-space:nowrap;"><i class="fas fa-redo"></i> Re-entrenar</a>
    </div>
</div>
@endsection
