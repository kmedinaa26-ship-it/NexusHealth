@extends('superadmin.layout')
@section('title', 'Cobros')
@section('nav-fin-cobros', 'active')

@section('content')
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #059669;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:0.5rem;"><i class="fas fa-hand-holding-usd" style="color:#059669"></i> Cobros a Pacientes</h3>
    <p style="font-size:0.8rem;color:#78716C;margin-bottom:1.5rem;">Lo que le facturamos al paciente (se cruza con costos para calcular utilidad)</p>
    <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:2rem;text-align:center;">
        <i class="fas fa-link" style="font-size:2rem;color:#16A34A;margin-bottom:0.5rem;"></i>
        <p style="font-weight:800;color:#166534;">Modulo conectado con Caja y Facturacion</p>
        <p style="font-size:0.85rem;color:#78716C;">Los cobros se registran en el modulo de Caja existente. Aqui se mostrara el cruce con costos ML.</p>
    </div>
</div>
@endsection
