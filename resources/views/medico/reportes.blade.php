@extends('medico.layout')
@section('title', 'Reportes')
@section('nav-reportes', 'active')
@section('content')
<div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); margin-bottom:1.5rem;">
    <h3 style="font-weight:800;"><i class="fas fa-chart-bar" style="color:#3B82F6;"></i> Reportes</h3>
</div>
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem;">
    <div style="background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); text-align:center; cursor:pointer;" onmouseover="this.style.borderTop='4px solid #3B82F6'" onmouseout="this.style.borderTop='none'">
        <i class="fas fa-users" style="font-size:2rem; color:#3B82F6; margin-bottom:1rem;"></i>
        <h4 style="font-weight:800;">Pacientes Atendidos</h4>
    </div>
    <div style="background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); text-align:center; cursor:pointer;" onmouseover="this.style.borderTop='4px solid #2D9E6A'" onmouseout="this.style.borderTop='none'">
        <i class="fas fa-prescription" style="font-size:2rem; color:#2D9E6A; margin-bottom:1rem;"></i>
        <h4 style="font-weight:800;">Recetas Emitidas</h4>
    </div>
    <div style="background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 6px rgba(0,0,0,0.04); text-align:center; cursor:pointer;" onmouseover="this.style.borderTop='4px solid #F59E0B'" onmouseout="this.style.borderTop='none'">
        <i class="fas fa-chart-line" style="font-size:2rem; color:#F59E0B; margin-bottom:1rem;"></i>
        <h4 style="font-weight:800;">Estadísticas</h4>
    </div>
</div>
@endsection
