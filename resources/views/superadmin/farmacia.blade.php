@extends('superadmin.layout')
@section('title', 'Supervisión Global de Farmacia')
@section('nav-farmacia', 'active')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h3 style="font-weight: 800; color: #1E1A17;">Inventario Farmacéutico</h3>
        <p style="color: #736860; font-size: 0.85rem;">Control de cuadro básico y medicamentos controlados.</p>
    </div>
    <button onclick="document.getElementById('modal-add-med').style.display='flex'" style="background: #F05A4E; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 700; cursor: pointer;">
        <i class="fas fa-plus"></i> Agregar Medicamento
    </button>
</div>

<div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #1E1A17; color: white; text-align: left;">
                <th style="padding: 1rem; font-size: 0.8rem;">Medicamento</th>
                <th style="padding: 1rem; font-size: 0.8rem;">Principio Activo</th>
                <th style="padding: 1rem; font-size: 0.8rem;">Tipo</th>
                <th style="padding: 1rem; font-size: 0.8rem;">Stock Actual</th>
                <th style="padding: 1rem; font-size: 0.8rem;">Mínimo Requerido</th>
                <th style="padding: 1rem; font-size: 0.8rem;">Estatus de Inventario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medications as $med)
            <tr style="border-bottom: 1px solid #E5E7EB; background: {{ $med->stock == 0 ? '#FFF1F0' : ($med->stock <= $med->min_stock ? '#FFF5EB' : 'white') }}" onmouseover="this.style.filter='brightness(0.95)'" onmouseout="this.style.filter='none'">
                <td style="padding: 1rem; font-weight: 700;">{{ $med->name }}</td>
                <td style="padding: 1rem; color: #736860;">{{ $med->active_ingredient }}</td>
                <td style="padding: 1rem;"><span style="background: {{ $med->type == 'Controlado' ? '#FFE0DC' : '#E5E7EB' }}; color: {{ $med->type == 'Controlado' ? '#C7291C' : '#1E1A17' }}; padding:0.2rem 0.5rem; border-radius:10px; font-size:0.75rem; font-weight:700;">{{ $med->type }}</span></td>
                <td style="padding: 1rem; font-weight: 800; font-size: 1.1rem; color: {{ $med->stock == 0 ? '#C7291C' : ($med->stock <= $med->min_stock ? '#FF8C42' : '#2D9E6A') }}">{{ $med->stock }}</td>
                <td style="padding: 1rem; color: #736860;">{{ $med->min_stock }}</td>
                <td style="padding: 1rem;">
                    @if($med->stock == 0)
                        <span style="background:#C7291C; color:white; padding:0.3rem 0.6rem; border-radius:10px; font-size:0.75rem; font-weight:700;">SIN STOCK</span>
                    @elseif($med->stock <= $med->min_stock)
                        <span style="background:#FF8C42; color:white; padding:0.3rem 0.6rem; border-radius:10px; font-size:0.75rem; font-weight:700;">BAJA EXISTENCIA</span>
                    @else
                        <span style="background:#2D9E6A; color:white; padding:0.3rem 0.6rem; border-radius:10px; font-size:0.75rem; font-weight:700;">ÓPTIMO</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal Agregar Medicamento -->
<div id="modal-add-med" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:100; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:12px; width:450px;">
        <h3 style="font-weight:800; margin-bottom:1rem;">Registrar Medicamento</h3>
        <form action="{{ route('superadmin.storeMedication') }}" method="POST">
            @csrf
            <div style="margin-bottom:1rem;"><label style="font-size:0.8rem; font-weight:700; color:#736860;">Nombre Comercial</label><input type="text" name="name" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
            <div style="margin-bottom:1rem;"><label style="font-size:0.8rem; font-weight:700; color:#736860;">Principio Activo</label><input type="text" name="active_ingredient" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:1rem;">
                <div><label style="font-size:0.8rem; font-weight:700; color:#736860;">Stock</label><input type="number" name="stock" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
                <div><label style="font-size:0.8rem; font-weight:700; color:#736860;">Mínimo</label><input type="number" name="min_stock" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
                <div><label style="font-size:0.8rem; font-weight:700; color:#736860;">Precio</label><input type="number" step="0.01" name="price" required style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"></div>
            </div>
            <div style="margin-bottom:1rem;"><label style="font-size:0.8rem; font-weight:700; color:#736860;">Tipo</label>
                <select name="type" style="width:100%; padding:0.5rem; border:1px solid #E5E7EB; border-radius:6px;"><option>Cuadro Básico</option><option>Controlado</option><option>Generico</option><option>Patente</option></select>
            </div>
            <div style="display:flex; gap:10px; margin-top:1.5rem;">
                <button type="button" onclick="document.getElementById('modal-add-med').style.display='none'" style="flex:1; padding:0.6rem; border:1px solid #E5E7EB; background:white; border-radius:6px; cursor:pointer;">Cancelar</button>
                <button type="submit" style="flex:1; padding:0.6rem; background:#F05A4E; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:700;">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
