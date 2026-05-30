@extends('farmacia.layout')
@section('title', 'Panel General Farmacia')
@section('nav-dashboard', 'active')

@section('content')
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
    <div style="background: white; padding: 1.5rem; border-radius: 12px; border-top: 4px solid #2D9E6A; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <div style="font-size:0.8rem; color:#736860; font-weight:700; text-transform:uppercase;">Total Medicamentos</div>
        <div style="font-size:2rem; font-weight:800; color:#1E1A17; margin-top:0.5rem;">{{ $total }}</div>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: 12px; border-top: 4px solid #FF8C42; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <div style="font-size:0.8rem; color:#736860; font-weight:700; text-transform:uppercase;">Stock Bajo</div>
        <div style="font-size:2rem; font-weight:800; color:#FF8C42; margin-top:0.5rem;">{{ $low_stock->count() }}</div>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: 12px; border-top: 4px solid #C7291C; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <div style="font-size:0.8rem; color:#736860; font-weight:700; text-transform:uppercase;">Sin Stock</div>
        <div style="font-size:2rem; font-weight:800; color:#C7291C; margin-top:0.5rem;">{{ $out_of_stock }}</div>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: 12px; border-top: 4px solid #3B82F6; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <div style="font-size:0.8rem; color:#736860; font-weight:700; text-transform:uppercase;">Acceso Enfermeria</div>
        <div style="font-size:2rem; font-weight:800; color:#3B82F6; margin-top:0.5rem;">{{ $enfermera_meds }}</div>
    </div>
</div>

@if($expiring_critical > 0)
<div style="background: #C7291C; color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; animation: pulse 3s infinite;">
    <i class="fas fa-exclamation-circle" style="font-size: 2rem;"></i>
    <div>
        <h4 style="font-weight:800;">ALERTA: {{ $expiring_critical }} medicamentos vencen en menos de 7 dias</h4>
        <p style="opacity:0.9; font-size:0.85rem;">Requiere accion inmediata para retiro de inventario.</p>
    </div>
</div>
@endif

@if($low_stock->count() > 0)
<div style="background: #FFF5EB; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 4px solid #FF8C42;">
    <h4 style="font-weight:800; color:#9a3412; margin-bottom:1rem;"><i class="fas fa-exclamation-triangle"></i> Alertas de Stock Bajo</h4>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        @foreach($low_stock as $med)
        <div style="background: white; padding: 1rem; border-radius: 8px;">
            <div style="font-weight:700;">{{ $med->name }}</div>
            <div style="font-size:0.8rem; color:#736860;">Stock: <strong style="color:#C7291C;">{{ $med->stock }}</strong> / Minimo: {{ $med->min_stock }}</div>
            <div style="display:flex; gap:5px; margin-top:0.3rem;">
                <span style="background:{{ $med->level_color }}; color:white; padding:0.1rem 0.4rem; border-radius:8px; font-size:0.65rem; font-weight:700;">{{ $med->required_level }}</span>
                @if($med->enfermera_can_administer)<span style="background:#3B82F6; color:white; padding:0.1rem 0.4rem; border-radius:8px; font-size:0.65rem; font-weight:700;">ENF</span>@endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($expiring_soon->count() > 0)
<div style="background: #FFF1F0; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 4px solid #C7291C;">
    <h4 style="font-weight:800; color:#8C1A11; margin-bottom:1rem;"><i class="fas fa-calendar-times"></i> Medicamentos por Vencer (30 dias)</h4>
    @foreach($expiring_soon as $med)
    <div style="background: white; padding: 0.75rem; border-radius: 6px; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <span style="font-weight:700;">{{ $med->name }}</span>
            <span style="font-size:0.8rem; color:#736860;">Lote: {{ $med->lot_number }}</span>
        </div>
        <span style="background:#C7291C; color:white; padding:0.2rem 0.6rem; border-radius:10px; font-size:0.75rem; font-weight:700;">Vence: {{ $med->expiry_date->format('d/m/Y') }}</span>
    </div>
    @endforeach
</div>
@endif

<div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
    <h4 style="font-weight:800; margin-bottom:1rem;"><i class="fas fa-shield-alt" style="color:#2D9E6A;"></i> Permisos por Nivel de Prescripcion</h4>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
        <div style="background:#FFF1F0; padding:1rem; border-radius:8px; border-left:4px solid #C7291C;">
            <div style="font-weight:800; color:#C7291C;">Nivel A - Especialista</div>
            <div style="font-size:0.8rem; color:#736860; margin-top:0.5rem;">Controlados, Opioides, Quirurgicos, Sedantes</div>
        </div>
        <div style="background:#FFF5EB; padding:1rem; border-radius:8px; border-left:4px solid #FF8C42;">
            <div style="font-weight:800; color:#FF8C42;">Nivel B - Hospitalizacion</div>
            <div style="font-size:0.8rem; color:#736860; margin-top:0.5rem;">Antibioticos intermedios, Medicamentos generales</div>
        </div>
        <div style="background:#EBF9F2; padding:1rem; border-radius:8px; border-left:4px solid #2D9E6A;">
            <div style="font-weight:800; color:#2D9E6A;">Nivel C - Basico</div>
            <div style="font-size:0.8rem; color:#736860; margin-top:0.5rem;">Analgésicos simples, Tratamientos limitados</div>
        </div>
        <div style="background:#EFF6FF; padding:1rem; border-radius:8px; border-left:4px solid #3B82F6;">
            <div style="font-weight:800; color:#3B82F6;">Enfermeria</div>
            <div style="font-size:0.8rem; color:#736860; margin-top:0.5rem;">Viales, Sueros, Curaciones, Oxigeno, Medicamentos autorizados</div>
        </div>
    </div>
</div>
@endsection
