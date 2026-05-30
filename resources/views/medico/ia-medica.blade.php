@extends('medico.layout')
@section('title', 'IA Médica')
@section('nav-ia', 'active')
@section('content')
<div style="background:linear-gradient(135deg,#1E293B,#334155); padding:2rem; border-radius:12px; margin-bottom:1.5rem; color:white;">
    <h3 style="font-weight:800;"><i class="fas fa-brain"></i> IA Médica</h3>
    <p style="font-size:0.85rem; color:#94A3B8; margin-top:0.5rem;">Predicción de gravedad, riesgo de reingreso y alertas inteligentes</p>
</div>
<div style="display:grid; grid-template-columns:repeat(2,1fr); gap:1rem;">
    <div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); text-align:center;">
        <i class="fas fa-heartbeat" style="font-size:2.5rem; color:#DC2626;"></i>
        <h4 style="font-weight:800; margin-top:0.75rem;">Predicción de Gravedad</h4>
        <p style="font-size:0.8rem; color:#64748B;">Análisis de signos vitales en tiempo real</p>
    </div>
    <div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); text-align:center;">
        <i class="fas fa-redo" style="font-size:2.5rem; color:#F59E0B;"></i>
        <h4 style="font-weight:800; margin-top:0.75rem;">Riesgo de Reingreso</h4>
        <p style="font-size:0.8rem; color:#64748B;">Modelo predictivo basado en historial</p>
    </div>
    <div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); text-align:center;">
        <i class="fas fa-exclamation-triangle" style="font-size:2.5rem; color:#EA580C;"></i>
        <h4 style="font-weight:800; margin-top:0.75rem;">Alertas Críticas</h4>
        <p style="font-size:0.8rem; color:#64748B;">Detección automática de anomalías</p>
    </div>
    <div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); text-align:center;">
        <i class="fas fa-skull-crossbones" style="font-size:2.5rem; color:#7F1D1D;"></i>
        <h4 style="font-weight:800; margin-top:0.75rem;">Riesgo de Mortalidad</h4>
        <p style="font-size:0.8rem; color:#64748B;">Score de riesgo en tiempo real</p>
    </div>
</div>
@endsection
