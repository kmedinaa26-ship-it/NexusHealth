@extends('superadmin.layout')
@section('title', 'Costos ML')
@section('nav-fin-costos', 'active')

@section('content')
<div style="display:grid;grid-template-columns:repeat({{ max(1, $porTipo->count() + 1) }},1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #DC2626;text-align:center;">
        <div style="font-size:1.8rem;font-weight:900;color:#DC2626;">${{ number_format($total, 0) }}</div>
        <div style="font-size:0.75rem;font-weight:700;color:#78716C;">Costo Total</div>
    </div>
    @foreach($porTipo as $tipo => $monto)
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #F97316;text-align:center;">
        <div style="font-size:1.8rem;font-weight:900;color:#EA580C;">${{ number_format($monto, 0) }}</div>
        <div style="font-size:0.75rem;font-weight:700;color:#78716C;">{{ ucfirst(str_replace('_', ' ', $tipo)) }}</div>
    </div>
    @endforeach
</div>
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #059669;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:1rem;"><i class="fas fa-file-invoice-dollar" style="color:#059669"></i> Detalle de Costos</h3>
    @if($costos->count() > 0)
    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead><tr style="background:#F0FDF4"><th style="padding:0.6rem;text-align:left;color:#166534;">Paciente</th><th style="padding:0.6rem;color:#166534;">Doctor</th><th style="padding:0.6rem;color:#166534;">Descripcion</th><th style="padding:0.6rem;color:#166534;">Tipo</th><th style="padding:0.6rem;color:#166534;">Cant.</th><th style="padding:0.6rem;color:#166534;">Total</th><th style="padding:0.6rem;color:#166534;">Fecha</th></tr></thead>
        <tbody>
        @foreach($costos as $c)
        <?php $fecha = is_object($c->created_at) ? $c->created_at->format('d/m H:i') : substr($c->created_at, 0, 16); ?>
        <tr style="border-bottom:1px solid #DCFCE7;">
            <td style="padding:0.5rem;font-weight:700;">{{ $c->patient_id }}</td>
            <td style="padding:0.5rem;color:#57534E;">{{ $c->doctor_name ?? '-' }}</td>
            <td style="padding:0.5rem;color:#57534E;">{{ $c->descripcion }}</td>
            <td style="padding:0.5rem;"><span style="background:#F0FDF4;color:#166534;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{ $c->tipo }}</span></td>
            <td style="padding:0.5rem;color:#57534E;">{{ $c->cantidad }}</td>
            <td style="padding:0.5rem;font-weight:800;color:#166534;">${{ number_format($c->costo_total, 2) }}</td>
            <td style="padding:0.5rem;color:#A8A29E;font-size:0.75rem;">{{ $fecha }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#78716C;padding:2rem;">Sin costos</p>
    @endif
</div>
@endsection
