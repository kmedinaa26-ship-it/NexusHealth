@extends('superadmin.layout')
@section('title', 'Alertas ML')
@section('nav-alertas-ml', 'active')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #DC2626;text-align:center;">
        <div style="font-size:1.8rem;font-weight:900;color:#DC2626;">{{ $noLeidas }}</div>
        <div style="font-size:0.75rem;font-weight:700;color:#78716C;">Sin Leer</div>
    </div>
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #F97316;text-align:center;">
        <div style="font-size:1.8rem;font-weight:900;color:#F97316;">{{ $alertas->where('tipo','riesgo_alto')->count() }}</div>
        <div style="font-size:0.75rem;font-weight:700;color:#78716C;">Riesgo Alto</div>
    </div>
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;text-align:center;">
        <div style="font-size:1.8rem;font-weight:900;color:#7C3AED;">{{ $alertas->where('tipo','costo_excedido')->count() }}</div>
        <div style="font-size:0.75rem;font-weight:700;color:#78716C;">Costo Excedido</div>
    </div>
    <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #EA580C;text-align:center;">
        <div style="font-size:1.8rem;font-weight:900;color:#EA580C;">{{ $alertas->where('tipo','modelo_degradado')->count() }}</div>
        <div style="font-size:0.75rem;font-weight:700;color:#78716C;">Modelo Degradado</div>
    </div>
</div>

<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #F97316;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:1rem;"><i class="fas fa-exclamation-triangle" style="color:#F97316"></i> Todas las Alertas ({{ $alertas->count() }})</h3>
    @if($alertas->count() > 0)
    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead><tr style="background:#FFF7ED"><th style="padding:0.6rem;text-align:left;color:#9A3412;">Tipo</th><th style="padding:0.6rem;color:#9A3412;">Paciente</th><th style="padding:0.6rem;color:#9A3412;">Mensaje</th><th style="padding:0.6rem;color:#9A3412;">Estado</th><th style="padding:0.6rem;color:#9A3412;">Fecha</th></tr></thead>
        <tbody>
        @foreach($alertas as $a)
        <?php
            $tipos = [
                'riesgo_alto' => ['bg'=>'#FEF2F2','border'=>'#DC2626','color'=>'#991B1B','icon'=>'fa-skull-crossbones','label'=>'Riesgo Alto'],
                'costo_excedido' => ['bg'=>'#FFF7ED','border'=>'#F97316','color'=>'#9A3412','icon'=>'fa-dollar-sign','label'=>'Costo Excedido'],
                'modelo_degradado' => ['bg'=>'#FEF2F2','border'=>'#DC2626','color'=>'#991B1B','icon'=>'fa-chart-line','label'=>'Modelo Degradado'],
                'error_prediccion' => ['bg'=>'#F5F3FF','border'=>'#7C3AED','color'=>'#5B21B6','icon'=>'fa-bug','label'=>'Error Prediccion'],
            ];
            $t = $tipos[$a->tipo] ?? $tipos['error_prediccion'];
            $fecha = is_object($a->created_at) ? $a->created_at->format('d/m H:i') : substr($a->created_at, 0, 16);
        ?>
        <tr style="border-bottom:1px solid #F3F4F6;{{ !$a->leida ? 'background:#FFFBFB;' : '' }}">
            <td style="padding:0.5rem;"><span style="background:{{$t['bg']}};color:{{$t['color']}};padding:0.15rem 0.5rem;border-radius:4px;font-size:0.7rem;font-weight:800;"><i class="fas {{$t['icon']}}"></i> {{$t['label']}}</span></td>
            <td style="padding:0.5rem;color:#57534E;">{{ $a->patient_id ?? 'Sistema' }}</td>
            <td style="padding:0.5rem;color:#1E1A17;font-size:0.8rem;max-width:400px;">{{ Str::limit($a->mensaje, 80) }}</td>
            <td style="padding:0.5rem;"><span style="background:{{ $a->leida ? '#F0FDF4' : '#FEF2F2' }};color:{{ $a->leida ? '#16A34A' : '#DC2626' }};padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{ $a->leida ? 'Leida' : 'Nueva' }}</span></td>
            <td style="padding:0.5rem;color:#A8A29E;font-size:0.75rem;">{{ $fecha }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#78716C;padding:2rem;">Sin alertas registradas</p>
    @endif
</div>
@endsection
