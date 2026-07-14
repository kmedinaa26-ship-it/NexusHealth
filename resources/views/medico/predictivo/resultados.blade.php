@extends('medico.layout')
@section('title', 'Cerrar Caso - Resultado Real')
@section('nav-resultados', 'active')

@section('content')
<h2 style="font-weight:900;color:#9A3412;margin-bottom:1.5rem"><i class="fas fa-clipboard-check" style="color:#F97316"></i> Cerrar Caso / Registrar Resultado Real</h2>

<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #DC2626;margin-bottom:1.5rem;">
    <h3 style="font-weight:900;color:#991B1B;margin-bottom:1rem;"><i class="fas fa-clock" style="color:#DC2626"></i> Casos Pendientes ({{ $pendientes->count() }})</h3>
    @if($pendientes->count() > 0)
    @foreach($pendientes as $p)
    <?php
        $datos = json_decode($p->datos_entrada, true) ?: [];
        $riesgoPct = round($p->probabilidad * 100, 1);
        $diasEst = $datos['dias_estimados'] ?? '?';
        $costoEst = $datos['costo_estimado'] ?? '?';
        $fecha = is_object($p->created_at) ? $p->created_at->format('d/m/Y H:i') : substr($p->created_at, 0, 16);
    ?>
    <div style="border:1px solid #FEE2E2;border-radius:10px;padding:1rem;margin-bottom:0.8rem;background:#FFFBFB;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem;">
            <div>
                <span style="font-weight:800;color:#1C1917;">Caso #{{ $p->id }}</span>
                <span style="margin-left:0.5rem;color:#78716C;font-size:0.8rem;">Paciente: {{ $p->patient_id ?? 'N/A' }}</span>
                <span style="margin-left:0.5rem;background:#FEE2E2;color:#DC2626;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{ $riesgoPct }}%</span>
            </div>
            <span style="font-size:0.7rem;color:#A8A29E;">{{ $fecha }}</span>
        </div>
        <form action="{{ route('medico.predictivo.guardarResultado') }}" method="POST" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:0.8rem;align-items:end;">
            @csrf
            <input type="hidden" name="prediccion_id" value="{{ $p->id }}">
            <div>
                <label style="display:block;font-size:0.7rem;font-weight:700;color:#78716C;margin-bottom:0.2rem;">Resultado Real</label>
                <select name="resultado_real" style="width:100%;padding:0.4rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.8rem;outline:none;background:white;">
                    <option value="vivo">Vivo</option>
                    <option value="fallecio">Fallecio</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:0.7rem;font-weight:700;color:#78716C;margin-bottom:0.2rem;">Dias Reales (est: {{ $diasEst }})</label>
                <input type="number" name="dias_reales" value="{{ $diasEst }}" style="width:100%;padding:0.4rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.8rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:0.7rem;font-weight:700;color:#78716C;margin-bottom:0.2rem;">Costo Real (est: ${{ is_numeric($costoEst) ? number_format($costoEst,0) : $costoEst }})</label>
                <input type="number" name="costo_real" value="{{ $costoEst }}" step="0.01" style="width:100%;padding:0.4rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.8rem;outline:none;">
            </div>
            <div>
                <label style="display:block;font-size:0.7rem;font-weight:700;color:#78716C;margin-bottom:0.2rem;">Clasificacion ML</label>
                <input type="text" readonly value="{{ $p->prediccion === 'alto_riesgo' ? 'Alto Riesgo' : 'Bajo Riesgo' }}" style="width:100%;padding:0.4rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.8rem;outline:none;background:#F5F5F4;color:#78716C;">
            </div>
            <button type="submit" style="padding:0.4rem 1rem;background:#DC2626;color:white;border:none;border-radius:6px;font-weight:800;font-size:0.8rem;cursor:pointer;"><i class="fas fa-check"></i> Cerrar</button>
        </form>
    </div>
    @endforeach
    @else
    <p style="text-align:center;color:#78716C;padding:1rem;font-size:0.85rem;">Sin casos pendientes</p>
    @endif
</div>

<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #16A34A;">
    <h3 style="font-weight:900;color:#166534;margin-bottom:1rem;"><i class="fas fa-check-double" style="color:#16A34A"></i> Casos Cerrados ({{ $cerrados->count() }}) - Alimentan el ML</h3>
    @if($cerrados->count() > 0)
    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead>
            <tr style="background:#F0FDF4">
                <th style="padding:0.6rem;text-align:left;color:#166534;">Caso</th>
                <th style="padding:0.6rem;color:#166534;">Prediccion</th>
                <th style="padding:0.6rem;color:#166534;">Real</th>
                <th style="padding:0.6rem;color:#166534;">Dias Est/Real</th>
                <th style="padding:0.6rem;color:#166534;">Costo Est/Real</th>
                <th style="padding:0.6rem;color:#166534;">Tipo ML</th>
            </tr>
        </thead>
        <tbody>
        @foreach($cerrados as $c)
        <?php
            $datos = json_decode($c->datos_entrada, true) ?: [];
            $riesgoPct = round($c->probabilidad * 100, 1);
            $predAlto = $c->prediccion === 'alto_riesgo';
            $realAlto = $c->resultado_real === 'fallecio';
            $tipo = ($predAlto && $realAlto) ? 'VP' : ((!$predAlto && !$realAlto) ? 'VN' : (($predAlto && !$realAlto) ? 'FP' : 'FN'));
            $tipoColor = ($tipo === 'VP' || $tipo === 'VN') ? '#16A34A' : '#DC2626';
            $tipoBg = ($tipo === 'VP' || $tipo === 'VN') ? '#F0FDF4' : '#FEF2F2';
            $tipoLabel = ['VP'=>'Verdadero Positivo','VN'=>'Verdadero Negativo','FP'=>'Falso Positivo','FN'=>'Falso Negativo'][$tipo];
            $diasEst = $datos['dias_estimados'] ?? '?';
            $costoEst = $datos['costo_estimado'] ?? 0;
        ?>
        <tr style="border-bottom:1px solid #DCFCE7;">
            <td style="padding:0.5rem;font-weight:700;">#{{ $c->id }}</td>
            <td style="padding:0.5rem;font-weight:800;color:{{ $predAlto ? '#DC2626' : '#16A34A' }};">{{ $riesgoPct }}%</td>
            <td style="padding:0.5rem;font-weight:800;color:{{ $realAlto ? '#DC2626' : '#16A34A' }};">{{ $c->resultado_real === 'fallecio' ? 'Fallecio' : 'Vivo' }}</td>
            <td style="padding:0.5rem;color:#57534E;font-size:0.8rem;">{{ $diasEst }} / {{ $c->dias_hospitalizacion ?? '?' }}</td>
            <td style="padding:0.5rem;color:#57534E;font-size:0.8rem;">${{ is_numeric($costoEst) ? number_format($costoEst,0) : $costoEst }} / ${{ is_numeric($c->costo_real) ? number_format($c->costo_real,0) : $c->costo_real }}</td>
            <td style="padding:0.5rem;"><span style="background:{{$tipoBg}};color:{{$tipoColor}};padding:0.15rem 0.5rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{$tipo}} - {{$tipoLabel}}</span></td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#78716C;padding:1rem;font-size:0.85rem;">Sin casos cerrados aun</p>
    @endif
</div>
@endsection
