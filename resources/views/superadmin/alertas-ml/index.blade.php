@extends('superadmin.layout')
@section('title', 'Alertas ML')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<h3 style="font-weight:900;color:#1E1A17;margin-bottom:1.5rem;"><i class="fas fa-bell" style="color:#7C3AED"></i> Alertas ML</h3>
@php
    $cols = array_column(DB::select('SHOW COLUMNS FROM alertas_ml'),'Field');
    $textCol = null;
    foreach($cols as $c){
        if(in_array($c,['id','created_at','updated_at'])) continue;
        $t = DB::select("SHOW COLUMNS FROM alertas_ml WHERE Field=?",[$c])[0]->Type ?? '';
        if(strpos($t,'text')!==false || strpos($t,'varchar')!==false){ $textCol = $c; break; }
    }
    $hasEstado = in_array('estado',$cols);
    $hasTipo = in_array('tipo',$cols);
    $alertas = DB::table('alertas_ml')->orderBy('created_at','desc')->get();
    $sinLeer = 0; $riesgoAlto = 0; $costoExc = 0; $modeloDeg = 0;
    foreach($alertas as $al){
        $tipo = $hasTipo ? $al->tipo : '';
        if($hasEstado && $al->estado !== 'leida' && $al->estado !== 'resuelta') $sinLeer++;
        if(strpos($tipo,'riesgo')!==false) $riesgoAlto++;
        if(strpos($tipo,'costo')!==false) $costoExc++;
        if(strpos($tipo,'modelo')!==false || strpos($tipo,'degrad')!==false) $modeloDeg++;
    }
@endphp
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:linear-gradient(135deg,#7C3AED,#5B21B6);border-radius:14px;padding:1.2rem;color:white;"><div style="font-size:0.7rem;opacity:0.8;">SIN LEER</div><div style="font-size:2rem;font-weight:900;">{{ $sinLeer }}</div></div>
    <div style="background:linear-gradient(135deg,#DC2626,#B91C1C);border-radius:14px;padding:1.2rem;color:white;"><div style="font-size:0.7rem;opacity:0.8;">RIESGO ALTO</div><div style="font-size:2rem;font-weight:900;">{{ $riesgoAlto }}</div></div>
    <div style="background:linear-gradient(135deg,#EA580C,#C2410C);border-radius:14px;padding:1.2rem;color:white;"><div style="font-size:0.7rem;opacity:0.8;">COSTO EXCEDIDO</div><div style="font-size:2rem;font-weight:900;">{{ $costoExc }}</div></div>
    <div style="background:linear-gradient(135deg,#0891B2,#0E7490);border-radius:14px;padding:1.2rem;color:white;"><div style="font-size:0.7rem;opacity:0.8;">MODELO DEGRADADO</div><div style="font-size:2rem;font-weight:900;">{{ $modeloDeg }}</div></div>
</div>
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;">
    <h4 style="font-weight:900;color:#1E1A17;margin-bottom:1rem;">Todas las Alertas ({{ $alertas->count() }})</h4>
    @if($alertas->count() > 0)
    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead><tr style="background:#F5F3FF">
            @if($hasTipo)<th style="padding:0.6rem;text-align:left;color:#7C3AED;">Tipo</th>@endif
            @if($textCol)<th style="padding:0.6rem;text-align:left;color:#7C3AED;">Mensaje</th>@endif
            @if($hasEstado)<th style="padding:0.6rem;text-align:left;color:#7C3AED;">Estado</th>@endif
            <th style="padding:0.6rem;text-align:left;color:#7C3AED;">Fecha</th>
        </tr></thead>
        <tbody>
        @foreach($alertas as $al)
            @php
                $tipo = $hasTipo ? $al->tipo : '';
                $msg = $textCol ? $al->$textCol : '';
                $est = $hasEstado ? $al->estado : '';
                $tipoColor = strpos($tipo,'riesgo')!==false ? '#DC2626' : (strpos($tipo,'costo')!==false ? '#EA580C' : (strpos($tipo,'modelo')!==false||strpos($tipo,'degrad')!==false ? '#0891B2' : '#7C3AED'));
                $estBg = (strpos($est,'resuel')!==false||strpos($est,'leida')!==false||strpos($est,'cerrad')!==false) ? '#F0FDF4' : '#FEF2F2';
                $estColor = (strpos($est,'resuel')!==false||strpos($est,'leida')!==false||strpos($est,'cerrad')!==false) ? '#16A34A' : '#DC2626';
            @endphp
            <tr style="border-bottom:1px solid #F3F4F6;">
                @if($hasTipo)<td style="padding:0.5rem;"><span style="background:{{ $tipoColor }}15;color:{{ $tipoColor }};padding:0.2rem 0.6rem;border-radius:8px;font-size:0.7rem;font-weight:800;">{{ ucfirst(str_replace('_',' ',$tipo)) }}</span></td>@endif
                @if($textCol)<td style="padding:0.5rem;color:#1E1A17;">{{ Str::limit($msg, 80) }}</td>@endif
                @if($hasEstado)<td style="padding:0.5rem;"><span style="background:{{ $estBg }};color:{{ $estColor }};padding:0.2rem 0.6rem;border-radius:8px;font-size:0.7rem;font-weight:800;">{{ ucfirst($est) }}</span></td>@endif
                <td style="padding:0.5rem;color:#78716C;font-size:0.8rem;">{{ $al->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#A8A29E;padding:2rem;">Sin alertas.</p>
    @endif
</div>
@endsection
