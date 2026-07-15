@extends('superadmin.layout')

@section('title', 'Caja - Cuentas Abiertas')
@section('nav-caja', 'active')

@section('content')
<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); margin-top: 1.5rem;">
    <h3 style="font-weight: 800; margin-bottom: 1.5rem; color: #1E1A17;">
        <i class="fas fa-cash-register" style="color: #2D9E6A; margin-right: 0.5rem;"></i> Cuentas de Pacientes Abiertas
    </h3>

    @if(session('success')) <div style="background:#D1FAE5; color:#2D9E6A; padding:1rem; border-radius:8px; margin-bottom:1rem;">{{ session('success') }}</div> @endif

    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
        @foreach($cuentas as $cuenta)
        <div style="border: 1px solid #E5E7EB; padding: 1.5rem; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 0.8rem; color: #736860; font-weight: 700;">CUENTA #{{ $cuenta->id }}</div>
                <div style="font-size: 1.2rem; font-weight: 800; color: #1E1A17; margin-top: 0.3rem;">Paciente ID: {{ $cuenta->patient_id }}</div>
                <div style="font-size: 0.85rem; color: #736860; margin-top: 0.3rem; text-transform: capitalize;">{{ $cuenta->encounter_type }} - Abierta: {{ $cuenta->opened_at->format('d/m/Y H:i') }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 1.5rem; font-weight: 800; color: #C7291C;">$ {{ number_format($cuenta->total, 2) }}</div>
                <a href="{{ route('caja.show', $cuenta->id) }}" style="display: inline-block; margin-top: 0.5rem; background: #1E1A17; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 700;">
                    <i class="fas fa-eye"></i> Detalle y Cobrar
                </a>
            </div>
        </div>
        @endforeach
        @if($cuentas->isEmpty())
        <div style="grid-column: span 2; text-align: center; color: #736860; padding: 2rem;">No hay cuentas abiertas. Las ventas directas en Farmacia no generan cuenta.</div>
        @endif
    </div>
</div>
@endsection
