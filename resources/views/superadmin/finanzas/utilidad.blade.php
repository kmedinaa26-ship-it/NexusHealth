@extends('superadmin.layout')
@section('title', 'Utilidad y Margen')
@section('nav-fin-utilidad', 'active')

@section('content')
<!-- KPIs principales -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #DC2626;text-align:center;">
        <div style="font-size:1.5rem;font-weight:900;color:#DC2626;">${{ number_format($costosInsumos, 0) }}</div>
        <div style="font-size:0.7rem;font-weight:700;color:#78716C;">Costos (Gasto Real)</div>
    </div>
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #16A34A;text-align:center;">
        <div style="font-size:1.5rem;font-weight:900;color:#16A34A;">${{ number_format($cobros, 0) }}</div>
        <div style="font-size:0.7rem;font-weight:700;color:#78716C;">Cobros (Ingreso)</div>
    </div>
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;text-align:center;">
        <div style="font-size:1.5rem;font-weight:900;color:{{ $utilidad >= 0 ? '#16A34A' : '#DC2626' }};">${{ number_format($utilidad, 0) }}</div>
        <div style="font-size:0.7rem;font-weight:700;color:#78716C;">Utilidad</div>
    </div>
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #EA580C;text-align:center;">
        <div style="font-size:1.5rem;font-weight:900;color:#EA580C;">{{ $margen }}%</div>
        <div style="font-size:0.7rem;font-weight:700;color:#78716C;">Margen</div>
    </div>
</div>

<!-- Formula visual -->
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #059669;margin-bottom:1.5rem;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:1rem;"><i class="fas fa-balance-scale" style="color:#059669"></i> Formula: Utilidad = Cobros - Costos</h3>
    <div style="display:grid;grid-template-columns:1fr auto 1fr auto 1fr;gap:1rem;align-items:center;padding:1rem;background:#F9FAFB;border-radius:10px;">
        <div style="text-align:center;">
            <div style="font-size:1.3rem;font-weight:900;color:#DC2626;">${{ number_format($costosInsumos, 0) }}</div>
            <div style="font-size:0.65rem;color:#78716C;">COSTOS</div>
        </div>
        <div style="font-size:1.5rem;font-weight:900;color:#A8A29E;">-</div>
        <div style="text-align:center;">
            <div style="font-size:1.3rem;font-weight:900;color:#16A34A;">${{ number_format($cobros, 0) }}</div>
            <div style="font-size:0.65rem;color:#78716C;">COBROS</div>
        </div>
        <div style="font-size:1.5rem;font-weight:900;color:#A8A29E;">=</div>
        <div style="text-align:center;">
            <div style="font-size:1.3rem;font-weight:900;color:{{ $utilidad >= 0 ? '#16A34A' : '#DC2626' }};">${{ number_format($utilidad, 0) }}</div>
            <div style="font-size:0.65rem;color:#78716C;">UTILIDAD ({{ $margen }}%)</div>
        </div>
    </div>
</div>

<!-- Comparativa ML vs Real -->
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:0.5rem;"><i class="fas fa-brain" style="color:#7C3AED"></i> Precision del Modelo de Costos (ML vs Real)</h3>
    <p style="font-size:0.8rem;color:#78716C;margin-bottom:1.5rem;">Compara lo que predijo el ML vs lo que realmente costo, usando los {{ $casosConCosto }} casos cerrados con costo real registrado.</p>
    
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
        <div style="background:#F5F3FF;border-radius:10px;padding:1rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;color:#7C3AED;">${{ number_format($costoEstimadoML, 0) }}</div>
            <div style="font-size:0.7rem;font-weight:700;color:#57534E;">Costo Total Estimado (ML)</div>
        </div>
        <div style="background:#FFF7ED;border-radius:10px;padding:1rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;color:#EA580C;">${{ number_format($costoReal, 0) }}</div>
            <div style="font-size:0.7rem;font-weight:700;color:#57534E;">Costo Total Real</div>
        </div>
        <div style="background:#FEF2F2;border-radius:10px;padding:1rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;color:#DC2626;">${{ number_format($diferencia, 0) }}</div>
            <div style="font-size:0.7rem;font-weight:700;color:#57534E;">Diferencia Absoluta</div>
        </div>
        <div style="background:{{ $precisionCosto >= 80 ? '#F0FDF4' : ($precisionCosto >= 60 ? '#FFF7ED' : '#FEF2F2') }};border-radius:10px;padding:1rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;color:{{ $precisionCosto >= 80 ? '#16A34A' : ($precisionCosto >= 60 ? '#EA580C' : '#DC2626') }};">{{ $precisionCosto }}%</div>
            <div style="font-size:0.7rem;font-weight:700;color:#57534E;">Precision del Modelo</div>
        </div>
    </div>

    @if($casosConCosto > 0)
    <p style="font-size:0.75rem;font-weight:800;color:#78716C;margin-bottom:0.5rem;">DETALLE POR CASO:</p>
    <div style="max-height:300px;overflow-y:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
            <thead><tr style="background:#F5F3FF;position:sticky;top:0;"><th style="padding:0.5rem;text-align:left;color:#7C3AED;">Caso</th><th style="padding:0.5rem;color:#7C3AED;">Estimado ML</th><th style="padding:0.5rem;color:#7C3AED;">Real</th><th style="padding:0.5rem;color:#7C3AED;">Diferencia</th><th style="padding:0.5rem;color:#7C3AED;">Error %</th></tr></thead>
            <tbody>
            @php $casosML = DB::table('predicciones_clinicas')->leftJoin('resultados_reales','predicciones_clinicas.id','=','resultados_reales.prediccion_id')->where('predicciones_clinicas.estado','cerrada')->whereNotNull('resultados_reales.costo_real')->select('predicciones_clinicas.id','predicciones_clinicas.datos_entrada','resultados_reales.costo_real')->get(); @endphp
            @foreach($casosML as $cm)
            <?php
                $d = json_decode($cm->datos_entrada, true) ?: [];
                $est = $d['costo_estimado'] ?? 0;
                $real = floatval($cm->costo_real);
                $diff = $est - $real;
                $errPct = $est > 0 ? round(abs($diff / $est) * 100, 1) : 0;
                $errColor = $errPct < 20 ? '#16A34A' : ($errPct < 40 ? '#EA580C' : '#DC2626');
            ?>
            <tr style="border-bottom:1px solid #F3F4F6;">
                <td style="padding:0.4rem;font-weight:700;">#{{ $cm->id }}</td>
                <td style="padding:0.4rem;">${{ number_format($est, 0) }}</td>
                <td style="padding:0.4rem;">${{ number_format($real, 0) }}</td>
                <td style="padding:0.4rem;color:{{ $diff > 0 ? '#DC2626' : '#16A34A' }};">{{ $diff > 0 ? '+' : '' }}${{ number_format($diff, 0) }}</td>
                <td style="padding:0.4rem;font-weight:800;color:{{ $errColor }};">{{ $errPct }}%</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p style="text-align:center;color:#78716C;padding:2rem;font-size:0.85rem;">Para ver la precision del modelo, los medicos deben registrar costos reales al cerrar casos.</p>
    @endif
</div>
@endsection
