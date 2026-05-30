@extends('farmacia.layout')
@section('title', 'Carga Masiva de Medicamentos')
@section('nav-carga', 'active')

@section('content')
<div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); margin-bottom: 1.5rem;">
    <h3 style="font-weight: 800; color: #1E1A17;"><i class="fas fa-upload" style="color:#2D9E6A;"></i> Carga Masiva desde CSV</h3>
    <p style="color: #736860; font-size: 0.85rem;">Sube un archivo CSV con los medicamentos a registrar.</p>
</div>

@if(session('success'))
<div style="background:#EBF9F2; color:#065F46; padding:1rem; border-radius:8px; margin-bottom:1rem; border-left:4px solid #2D9E6A;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <h4 style="font-weight: 800; margin-bottom: 1rem;">Subir Archivo</h4>
        <form action="{{ route('farmacia.uploadCSV') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 1rem;">
                <input type="file" name="csv_file" accept=".csv,.txt" required style="width: 100%; padding: 1rem; border: 2px dashed #2D9E6A; border-radius: 8px; background: #EBF9F2; cursor: pointer;">
            </div>
            <button type="submit" style="width:100%; background:#2D9E6A; color:white; border:none; padding:0.8rem; border-radius:8px; font-weight:700; cursor:pointer;">
                <i class="fas fa-upload"></i> Cargar Medicamentos
            </button>
        </form>
    </div>

    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <h4 style="font-weight: 800; margin-bottom: 1rem;">Formato del CSV</h4>
        <div style="background: #F4F6F8; padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.8rem; margin-bottom: 1rem;">
            nombre, principio_activo, stock, min_stock, precio, nivel(A/B/C/Enfermera), origen, lote, caducidad, ubicacion, proveedor
        </div>
        <div style="font-size: 0.85rem; color: #736860;">
            <p style="margin-bottom: 0.5rem;"><i class="fas fa-info-circle" style="color:#3B82F6;"></i> La primera fila debe ser el encabezado.</p>
            <p style="margin-bottom: 0.5rem;"><i class="fas fa-info-circle" style="color:#3B82F6;"></i> Nivel debe ser: A, B, C o Enfermera.</p>
            <p><i class="fas fa-info-circle" style="color:#3B82F6;"></i> Origen: Central, Hospitalaria, Quirurgico, Urgencias.</p>
        </div>
    </div>
</div>
@endsection
