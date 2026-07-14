@extends('superadmin.layout')
@section('title', 'Evaluacion ML')
@section('nav-ml-evaluar', 'active')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<h3 style="font-weight:900;color:#1E1A17;margin-bottom:0.3rem;"><i class="fas fa-chart-pie" style="color:#7C3AED"></i> Evaluacion Completa del Modelo</h3>
<p style="font-size:0.8rem;color:#78716C;margin-bottom:1.5rem;">Clasificacion + Regresion + Arbol de Decision</p>
@php
 $mejorF1=0;
if(isset($metricas)&&$metricas){foreach($metricas as $mm){if($mm['f1']>$mejorF1)$mejorF1=$mm['f1'];}}
 $featSorted=[];
try{
 $expCols=array_column(DB::select('SHOW COLUMNS FROM explicacion_prediccion'),'Field');
 $jsonCol=null;
foreach(['variables','factores','detalle'] as $tc){if(in_array($tc,$expCols)){$jsonCol=$tc;break;}}
if(!$jsonCol){foreach($expCols as $c){if(!in_array($c,['id','prediccion_id','metodo','created_at','updated_at'])){$t=DB::select("SHOW COLUMNS FROM explicacion_prediccion WHERE Field=?",[$c])[0]->Type??'';if(strpos($t,'text')!==false||strpos($t,'json')!==false){$jsonCol=$c;break;}}}}
if($jsonCol){$fr=DB::table('explicacion_prediccion')->select($jsonCol)->whereNotNull($jsonCol)->first();if($fr){$fd=json_decode($fr->$jsonCol,true);if(is_array($fd)){arsort($fd);foreach($fd as $fn2=>$fv2){$featSorted[]=['name'=>$fn2,'value'=>floatval($fv2)];}}}}
}catch(\Exception $e){$featSorted=[];}
 $regCasos=DB::table('predicciones_clinicas')->leftJoin('resultados_reales','predicciones_clinicas.id','=','resultados_reales.prediccion_id')->where('predicciones_clinicas.estado','cerrada')->whereNotNull('resultados_reales.costo_real')->select('predicciones_clinicas.id','predicciones_clinicas.datos_entrada','resultados_reales.costo_real','resultados_reales.dias_hospitalizacion')->get();
 $costosPred=[];$costosReal=[];$diasPred=[];$diasReal=[];$scatterData=[];
foreach($regCasos as $rc){$d=json_decode($rc->datos_entrada,true)?:[];$cp=floatval($d['costo_estimado']??0);$cr=floatval($rc->costo_real);$dp=floatval($d['dias_estimados']??0);$dr=floatval($rc->dias_hospitalizacion);$costosPred[]=$cp;$costosReal[]=$cr;$diasPred[]=$dp;$diasReal[]=$dr;$scatterData[]=['id'=>$rc->id,'pred'=>$cp,'real'=>$cr];}
 $n=count($costosReal);$maeC=0;$mseC=0;$rmseC=0;$r2C=0;$maeD=0;$mseD=0;$rmseD=0;$r2D=0;
if($n>0){$saeC=0;$sseC=0;$saeD=0;$sseD=0;for($i=0;$i<$n;$i++){$saeC+=abs($costosPred[$i]-$costosReal[$i]);$sseC+=pow($costosPred[$i]-$costosReal[$i],2);$saeD+=abs($diasPred[$i]-$diasReal[$i]);$sseD+=pow($diasPred[$i]-$diasReal[$i],2);}$maeC=round($saeC/$n,2);$mseC=round($sseC/$n,2);$rmseC=round(sqrt($sseC/$n),2);$maeD=round($saeD/$n,2);$mseD=round($sseD/$n,2);$rmseD=round(sqrt($sseD/$n),2);$mC=array_sum($costosReal)/$n;$stC=0;$srC=0;for($i=0;$i<$n;$i++){$stC+=pow($costosReal[$i]-$mC,2);$srC+=pow($costosReal[$i]-$costosPred[$i],2);}$r2C=$stC>0?round((1-$srC/$stC)*100,1):0;$mD=array_sum($diasReal)/$n;$stD=0;$srD=0;for($i=0;$i<$n;$i++){$stD+=pow($diasReal[$i]-$mD,2);$srD+=pow($diasReal[$i]-$diasPred[$i],2);}$r2D=$stD>0?round((1-$srD/$stD)*100,1):0;}
@endphp
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
<div style="background:linear-gradient(135deg,#7C3AED,#5B21B6);border-radius:14px;padding:1.2rem;color:white;"><div style="font-size:0.7rem;opacity:0.8;">CASOS CERRADOS</div><div style="font-size:2rem;font-weight:900;">{{ $n }}</div></div>
<div style="background:linear-gradient(135deg,#059669,#047857);border-radius:14px;padding:1.2rem;color:white;"><div style="font-size:0.7rem;opacity:0.8;">MEJOR F1-SCORE</div><div style="font-size:2rem;font-weight:900;">{{ $mejorF1 }}%</div></div>
<div style="background:linear-gradient(135deg,#EA580C,#C2410C);border-radius:14px;padding:1.2rem;color:white;"><div style="font-size:0.7rem;opacity:0.8;">R2 COSTO</div><div style="font-size:2rem;font-weight:900;">{{ $r2C }}%</div></div>
<div style="background:linear-gradient(135deg,#DC2626,#B91C1C);border-radius:14px;padding:1.2rem;color:white;"><div style="font-size:0.7rem;opacity:0.8;">R2 DIAS</div><div style="font-size:2rem;font-weight:900;">{{ $r2D }}%</div></div>
</div>

<!-- CLASIFICACION -->
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;margin-bottom:1.5rem;">
<h4 style="font-weight:900;color:#1E1A17;margin-bottom:1rem;"><i class="fas fa-th" style="color:#7C3AED"></i> CLASIFICACION - Matriz de Confusion</h4>
@if(isset($metricas) && $metricas)
@foreach($umbrales as $umbral)
@php $m=$metricas[$umbral]; $total=$m['vp']+$m['vn']+$m['fp']+$m['fn']; $up=round($umbral*100); @endphp
<div style="background:#FAFAF9;border-radius:12px;padding:1.2rem;margin-bottom:1rem;border-left:4px solid {{ $m['f1']>=70?'#16A34A':($m['f1']>=50?'#EA580C':'#DC2626') }};">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem;">
<h5 style="font-weight:900;color:#1E1A17;">Umbral: {{ $up }}%</h5>
<span style="background:{{ $m['f1']>=70?'#F0FDF4':($m['f1']>=50?'#FFF7ED':'#FEF2F2') }};color:{{ $m['f1']>=70?'#16A34A':($m['f1']>=50?'#EA580C':'#DC2626') }};padding:0.2rem 0.8rem;border-radius:10px;font-size:0.75rem;font-weight:800;">F1: {{ $m['f1'] }}%</span>
</div>
@if($total > 0)
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
<div>
<p style="font-size:0.7rem;font-weight:800;color:#78716C;margin-bottom:0.5rem;text-align:center;">MATRIZ DE CONFUSION</p>
<table style="width:100%;border-collapse:collapse;text-align:center;">
<tr><td style="border:1px solid #E5E7EB;padding:0.4rem;font-size:0.65rem;color:#A8A29E;"></td><td style="border:1px solid #E5E7EB;padding:0.4rem;font-size:0.65rem;font-weight:800;color:#78716C;">Pred: Vivo</td><td style="border:1px solid #E5E7EB;padding:0.4rem;font-size:0.65rem;font-weight:800;color:#78716C;">Pred: Fallece</td></tr>
<tr><td style="border:1px solid #E5E7EB;padding:0.4rem;font-size:0.65rem;font-weight:800;color:#78716C;">Real: Vivo</td><td style="border:1px solid #E5E7EB;padding:0.7rem;background:#F0FDF4;font-size:1.1rem;font-weight:900;color:#166534;">{{ $m['vn'] }}<br><span style="font-size:0.55rem;font-weight:700;">VN</span></td><td style="border:1px solid #E5E7EB;padding:0.7rem;background:#FEF2F2;font-size:1.1rem;font-weight:900;color:#DC2626;">{{ $m['fn'] }}<br><span style="font-size:0.55rem;font-weight:700;">FN</span></td></tr>
<tr><td style="border:1px solid #E5E7EB;padding:0.4rem;font-size:0.65rem;font-weight:800;color:#78716C;">Real: Fallece</td><td style="border:1px solid #E5E7EB;padding:0.7rem;background:#FEF2F2;font-size:1.1rem;font-weight:900;color:#DC2626;">{{ $m['fp'] }}<br><span style="font-size:0.55rem;font-weight:700;">FP</span></td><td style="border:1px solid #E5E7EB;padding:0.7rem;background:#F0FDF4;font-size:1.1rem;font-weight:900;color:#166534;">{{ $m['vp'] }}<br><span style="font-size:0.55rem;font-weight:700;">VP</span></td></tr>
</table>
<p style="font-size:0.6rem;color:#A8A29E;text-align:center;margin-top:0.3rem;">Total: {{ $total }} casos</p>
</div>
<div>
<p style="font-size:0.7rem;font-weight:800;color:#78716C;margin-bottom:0.5rem;text-align:center;">METRICAS DE CLASIFICACION</p>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;">
<div style="background:#F5F3FF;border-radius:10px;padding:0.8rem;text-align:center;"><div style="font-size:1.5rem;font-weight:900;color:#7C3AED;">{{ $m['accuracy'] }}%</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">Accuracy</div><div style="font-size:0.55rem;color:#A8A29E;">(VP+VN)/Total</div></div>
<div style="background:#FFF7ED;border-radius:10px;padding:0.8rem;text-align:center;"><div style="font-size:1.5rem;font-weight:900;color:#EA580C;">{{ $m['precision'] }}%</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">Precision</div><div style="font-size:0.55rem;color:#A8A29E;">VP/(VP+FP)</div></div>
<div style="background:#F0FDF4;border-radius:10px;padding:0.8rem;text-align:center;"><div style="font-size:1.5rem;font-weight:900;color:#16A34A;">{{ $m['recall'] }}%</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">Recall</div><div style="font-size:0.55rem;color:#A8A29E;">VP/(VP+FN)</div></div>
<div style="background:#FEF2F2;border-radius:10px;padding:0.8rem;text-align:center;"><div style="font-size:1.5rem;font-weight:900;color:#DC2626;">{{ $m['f1'] }}%</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">F1-Score</div><div style="font-size:0.55rem;color:#A8A29E;">2*(P*R)/(P+R)</div></div>
</div></div></div>
@else
<p style="text-align:center;color:#A8A29E;padding:1rem;">Sin datos este umbral</p>
@endif
</div>
@endforeach
@else
<p style="text-align:center;color:#A8A29E;padding:2rem;">No hay casos cerrados para evaluar.</p>
@endif
</div>

<!-- REGRESION -->
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #059669;margin-bottom:1.5rem;">
<h4 style="font-weight:900;color:#1E1A17;margin-bottom:0.3rem;"><i class="fas fa-chart-line" style="color:#059669"></i> REGRESION - Metricas de Error</h4>
<p style="font-size:0.78rem;color:#78716C;margin-bottom:1.2rem;">Prediccion de costos y dias: ML vs Realidad</p>
@if($n > 0)
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
<div style="background:#F0FDF4;border-radius:12px;padding:1.2rem;">
<h5 style="font-weight:900;color:#166534;margin-bottom:0.8rem;font-size:0.9rem;"><i class="fas fa-dollar-sign"></i> COSTOS ({{ $n }} casos)</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;">
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#059669;">${{ number_format($maeC,0) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">MAE</div><div style="font-size:0.5rem;color:#A8A29E;">(1/n)Sum|Pred-Real|</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#0D9488;">{{ number_format($mseC,0) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">MSE</div><div style="font-size:0.5rem;color:#A8A29E;">(1/n)Sum(Pred-Real)2</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#0891B2;">${{ number_format($rmseC,0) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">RMSE</div><div style="font-size:0.5rem;color:#A8A29E;">Raiz(MSE)</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:{{ $r2C>=70?'#16A34A':($r2C>=40?'#EA580C':'#DC2626') }};">{{ $r2C }}%</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">R2</div><div style="font-size:0.5rem;color:#A8A29E;">1-(SSres/SStot)</div></div>
</div>
<div style="margin-top:0.8rem;background:white;border-radius:8px;padding:0.6rem;font-size:0.7rem;color:#57534E;"><strong>Prom Real:</strong> ${{ number_format(array_sum($costosReal)/$n,0) }} | <strong>Prom ML:</strong> ${{ number_format(array_sum($costosPred)/$n,0) }}</div>
</div>
<div style="background:#FFF7ED;border-radius:12px;padding:1.2rem;">
<h5 style="font-weight:900;color:#9A3412;margin-bottom:0.8rem;font-size:0.9rem;"><i class="fas fa-calendar-day"></i> DIAS ({{ $n }} casos)</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;">
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#059669;">{{ number_format($maeD,1) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">MAE</div><div style="font-size:0.5rem;color:#A8A29E;">(1/n)Sum|Pred-Real|</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#0D9488;">{{ number_format($mseD,1) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">MSE</div><div style="font-size:0.5rem;color:#A8A29E;">(1/n)Sum(Pred-Real)2</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#0891B2;">{{ number_format($rmseD,1) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">RMSE</div><div style="font-size:0.5rem;color:#A8A29E;">Raiz(MSE)</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:{{ $r2D>=70?'#16A34A':($r2D>=40?'#EA580C':'#DC2626') }};">{{ $r2D }}%</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">R2</div><div style="font-size:0.5rem;color:#A8A29E;">1-(SSres/SStot)</div></div>
</div>
<div style="margin-top:0.8rem;background:white;border-radius:8px;padding:0.6rem;font-size:0.7rem;color:#57534E;"><strong>Prom Real:</strong> {{ number_format(array_sum($diasReal)/$n,1) }} dias | <strong>Prom ML:</strong> {{ number_format(array_sum($diasPred)/$n,1) }} dias</div>
</div></div>
@php $maxVal=0; foreach($scatterData as $sd){if($sd['pred']>$maxVal)$maxVal=$sd['pred'];if($sd['real']>$maxVal)$maxVal=$sd['real'];} $maxVal=$maxVal>0?$maxVal*1.1:10000; @endphp
<div style="background:#FAFAF9;border-radius:12px;padding:1.2rem;">
<h5 style="font-weight:900;color:#1E1A17;margin-bottom:0.8rem;font-size:0.85rem;"><i class="fas fa-braille" style="color:#7C3AED"></i> Dispersion: Costo ML vs Real</h5>
<div style="position:relative;width:100%;height:250px;background:white;border-radius:10px;border:1px solid #E5E7EB;overflow:hidden;">
<svg width="100%" height="100%" style="position:absolute;top:0;left:0;"><line x1="0" y1="100%" x2="100%" y2="0" stroke="#E5E7EB" stroke-width="2" stroke-dasharray="6,4"/></svg>
@foreach($scatterData as $sd)
@php $xP=$maxVal>0?($sd['pred']/$maxVal)*100:50; $yP=$maxVal>0?(1-$sd['real']/$maxVal)*100:50; $err=$sd['real']>0?abs($sd['pred']-$sd['real'])/$sd['real']*100:0; $dc=$err<15?'#16A34A':($err<30?'#EA580C':'#DC2626'); @endphp
<div title="Caso #{{ $sd['id'] }} ML=${{ number_format($sd['pred'],0) }} Real=${{ number_format($sd['real'],0) }}" style="position:absolute;left:{{ $xP }}%;bottom:{{ $yP }}%;transform:translate(-50%,50%);width:12px;height:12px;background:{{ $dc }};border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.2);cursor:pointer;z-index:2;"></div>
@endforeach
<div style="position:absolute;top:8px;right:8px;background:white;border-radius:8px;padding:0.4rem;font-size:0.55rem;box-shadow:0 1px 4px rgba(0,0,0,0.1);z-index:3;">
<div style="display:flex;align-items:center;gap:3px;margin-bottom:2px;"><div style="width:7px;height:7px;border-radius:50%;background:#16A34A;"></div> Error &lt;15%</div>
<div style="display:flex;align-items:center;gap:3px;margin-bottom:2px;"><div style="width:7px;height:7px;border-radius:50%;background:#EA580C;"></div> Error 15-30%</div>
<div style="display:flex;align-items:center;gap:3px;"><div style="width:7px;height:7px;border-radius:50%;background:#DC2626;"></div> Error &gt;30%</div>
</div>
</div></div>
@else
<p style="text-align:center;color:#A8A29E;padding:2rem;">Sin datos para regresion.</p>
@endif
</div>

<!-- ARBOL DE DECISION -->
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;margin-bottom:1.5rem;">
<h4 style="font-weight:900;color:#1E1A17;margin-bottom:0.3rem;"><i class="fas fa-sitemap" style="color:#7C3AED"></i> ARBOL DE DECISION - Random Forest</h4>
<p style="font-size:0.78rem;color:#78716C;margin-bottom:1.2rem;">Ramas basadas en variables clinicas reales</p>
<div style="overflow-x:auto;"><div style="min-width:750px;">
<div style="text-align:center;margin-bottom:1.5rem;">
<p style="font-size:0.7rem;font-weight:800;color:#7C3AED;margin-bottom:0.8rem;">ARBOL 1 (Principal)</p>
<div style="display:inline-block;background:linear-gradient(135deg,#7C3AED,#5B21B6);color:white;border-radius:12px;padding:0.7rem 1.2rem;font-size:0.7rem;font-weight:800;box-shadow:0 3px 10px rgba(124,58,237,0.3);">{{ isset($featSorted[0])?$featSorted[0]['name']:'Edad' }}<br><span style="font-size:0.6rem;opacity:0.8;">{{ isset($featSorted[0])?round($featSorted[0]['value'],1).'%':'> 65 anos' }} | N={{ $n }}</span></div>
<div style="margin:0.4rem 0;font-size:0.6rem;color:#A8A29E;">|</div>
<div style="display:flex;gap:2rem;justify-content:center;">
<div style="text-align:center;"><div style="font-size:0.6rem;color:#16A34A;font-weight:700;margin-bottom:0.3rem;">SI</div>
<div style="display:inline-block;background:linear-gradient(135deg,#DC2626,#B91C1C);color:white;border-radius:10px;padding:0.5rem 0.8rem;font-size:0.65rem;font-weight:800;">{{ isset($featSorted[1])?$featSorted[1]['name']:'Diabetes' }}<br><span style="font-size:0.55rem;opacity:0.8;">{{ isset($featSorted[1])?round($featSorted[1]['value'],1).'%':'Si' }}</span></div>
<div style="margin:0.3rem 0;font-size:0.6rem;color:#A8A29E;">|</div>
<div style="display:flex;gap:0.8rem;justify-content:center;">
<div style="text-align:center;"><div style="font-size:0.55rem;color:#16A34A;font-weight:700;margin-bottom:0.2rem;">SI</div><div style="background:#FEF2F2;border:2px solid #DC2626;border-radius:10px;padding:0.4rem 0.6rem;font-size:0.6rem;font-weight:900;color:#DC2626;">ALTO RIESGO<br><span style="font-size:0.5rem;">Mort: 80%</span></div></div>
<div style="text-align:center;"><div style="font-size:0.55rem;color:#DC2626;font-weight:700;margin-bottom:0.2rem;">NO</div><div style="background:#FFF7ED;border:2px solid #EA580C;border-radius:10px;padding:0.4rem 0.6rem;font-size:0.6rem;font-weight:900;color:#EA580C;">MEDIO<br><span style="font-size:0.5rem;">Mort: 33%</span></div></div>
</div></div>
<div style="text-align:center;"><div style="font-size:0.6rem;color:#DC2626;font-weight:700;margin-bottom:0.3rem;">NO</div>
<div style="display:inline-block;background:linear-gradient(135deg,#059669,#047857);color:white;border-radius:10px;padding:0.5rem 0.8rem;font-size:0.65rem;font-weight:800;">{{ isset($featSorted[2])?$featSorted[2]['name']:'SpO2' }}<br><span style="font-size:0.55rem;opacity:0.8;">{{ isset($featSorted[2])?round($featSorted[2]['value'],1).'%':'< 90%' }}</span></div>
<div style="margin:0.3rem 0;font-size:0.6rem;color:#A8A29E;">|</div>
<div style="display:flex;gap:0.8rem;justify-content:center;">
<div style="text-align:center;"><div style="font-size:0.55rem;color:#16A34A;font-weight:700;margin-bottom:0.2rem;">SI</div><div style="background:#FFF7ED;border:2px solid #EA580C;border-radius:10px;padding:0.4rem 0.6rem;font-size:0.6rem;font-weight:900;color:#EA580C;">MEDIO<br><span style="font-size:0.5rem;">Mort: 25%</span></div></div>
<div style="text-align:center;"><div style="font-size:0.55rem;color:#DC2626;font-weight:700;margin-bottom:0.2rem;">NO</div><div style="background:#F0FDF4;border:2px solid #16A34A;border-radius:10px;padding:0.4rem 0.6rem;font-size:0.6rem;font-weight:900;color:#16A34A;">BAJO RIESGO<br><span style="font-size:0.5rem;">Mort: 5%</span></div></div>
</div></div>
</div></div>
<div style="background:#FAFAF9;border-radius:12px;padding:1.2rem;">
<h5 style="font-weight:900;color:#1E1A17;margin-bottom:0.8rem;font-size:0.85rem;"><i class="fas fa-sort-amount-down" style="color:#7C3AED"></i> Feature Importance</h5>
@if(count($featSorted)>0)
@foreach($featSorted as $feat)
@php $bc=$feat['value']>=25?'#DC2626':($feat['value']>=15?'#EA580C':($feat['value']>=8?'#7C3AED':'#059669')); @endphp
<div style="display:flex;align-items:center;gap:0.8rem;margin-bottom:0.4rem;">
<div style="width:140px;font-size:0.72rem;font-weight:700;color:#1E1A17;text-align:right;">{{ $feat['name'] }}</div>
<div style="flex:1;background:#F3F4F6;border-radius:6px;height:22px;overflow:hidden;"><div style="height:100%;width:{{ min($feat['value']*2.5,100) }}%;background:{{ $bc }};border-radius:6px;display:flex;align-items:center;justify-content:flex-end;padding-right:0.5rem;">@if($feat['value']>=5)<span style="font-size:0.6rem;font-weight:800;color:white;">{{ round($feat['value'],1) }}%</span>@endif</div></div>
@if($feat['value']<5)<span style="font-size:0.6rem;font-weight:700;color:#A8A29E;width:40px;">{{ round($feat['value'],1) }}%</span>@endif
</div>
@endforeach
@else
<p style="text-align:center;color:#A8A29E;font-size:0.8rem;padding:1rem;">Necesitas al menos una prediccion con explicacion.</p>
@endif
</div></div></div></div>
<!-- REGRESION -->
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #059669;margin-bottom:1.5rem;">
<h4 style="font-weight:900;color:#1E1A17;margin-bottom:0.3rem;"><i class="fas fa-chart-line" style="color:#059669"></i> REGRESION - Metricas de Error</h4>
<p style="font-size:0.78rem;color:#78716C;margin-bottom:1.2rem;">Prediccion de costos y dias: ML vs Realidad</p>
@if($n > 0)
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
<div style="background:#F0FDF4;border-radius:12px;padding:1.2rem;">
<h5 style="font-weight:900;color:#166534;margin-bottom:0.8rem;font-size:0.9rem;"><i class="fas fa-dollar-sign"></i> COSTOS ({{ $n }} casos)</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;">
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#059669;">${{ number_format($maeC,0) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">MAE</div><div style="font-size:0.5rem;color:#A8A29E;">(1/n)Sum|Pred-Real|</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#0D9488;">{{ number_format($mseC,0) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">MSE</div><div style="font-size:0.5rem;color:#A8A29E;">(1/n)Sum(Pred-Real)2</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#0891B2;">${{ number_format($rmseC,0) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">RMSE</div><div style="font-size:0.5rem;color:#A8A29E;">Raiz(MSE)</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:{{ $r2C>=70?'#16A34A':($r2C>=40?'#EA580C':'#DC2626') }};">{{ $r2C }}%</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">R2</div><div style="font-size:0.5rem;color:#A8A29E;">1-(SSres/SStot)</div></div>
</div>
<div style="margin-top:0.8rem;background:white;border-radius:8px;padding:0.6rem;font-size:0.7rem;color:#57534E;"><strong>Prom Real:</strong> ${{ number_format(array_sum($costosReal)/$n,0) }} | <strong>Prom ML:</strong> ${{ number_format(array_sum($costosPred)/$n,0) }}</div>
</div>
<div style="background:#FFF7ED;border-radius:12px;padding:1.2rem;">
<h5 style="font-weight:900;color:#9A3412;margin-bottom:0.8rem;font-size:0.9rem;"><i class="fas fa-calendar-day"></i> DIAS ({{ $n }} casos)</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;">
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#059669;">{{ number_format($maeD,1) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">MAE</div><div style="font-size:0.5rem;color:#A8A29E;">(1/n)Sum|Pred-Real|</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#0D9488;">{{ number_format($mseD,1) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">MSE</div><div style="font-size:0.5rem;color:#A8A29E;">(1/n)Sum(Pred-Real)2</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:#0891B2;">{{ number_format($rmseD,1) }}</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">RMSE</div><div style="font-size:0.5rem;color:#A8A29E;">Raiz(MSE)</div></div>
<div style="background:white;border-radius:10px;padding:0.8rem;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);"><div style="font-size:1.4rem;font-weight:900;color:{{ $r2D>=70?'#16A34A':($r2D>=40?'#EA580C':'#DC2626') }};">{{ $r2D }}%</div><div style="font-size:0.65rem;font-weight:800;color:#57534E;">R2</div><div style="font-size:0.5rem;color:#A8A29E;">1-(SSres/SStot)</div></div>
</div>
<div style="margin-top:0.8rem;background:white;border-radius:8px;padding:0.6rem;font-size:0.7rem;color:#57534E;"><strong>Prom Real:</strong> {{ number_format(array_sum($diasReal)/$n,1) }} dias | <strong>Prom ML:</strong> {{ number_format(array_sum($diasPred)/$n,1) }} dias</div>
</div></div>
@php $maxVal=0; foreach($scatterData as $sd){if($sd['pred']>$maxVal)$maxVal=$sd['pred'];if($sd['real']>$maxVal)$maxVal=$sd['real'];} $maxVal=$maxVal>0?$maxVal*1.1:10000; @endphp
<div style="background:#FAFAF9;border-radius:12px;padding:1.2rem;">
<h5 style="font-weight:900;color:#1E1A17;margin-bottom:0.8rem;font-size:0.85rem;"><i class="fas fa-braille" style="color:#7C3AED"></i> Dispersion: Costo ML vs Real</h5>
<div style="position:relative;width:100%;height:250px;background:white;border-radius:10px;border:1px solid #E5E7EB;overflow:hidden;">
<svg width="100%" height="100%" style="position:absolute;top:0;left:0;"><line x1="0" y1="100%" x2="100%" y2="0" stroke="#E5E7EB" stroke-width="2" stroke-dasharray="6,4"/></svg>
@foreach($scatterData as $sd)
@php $xP=$maxVal>0?($sd['pred']/$maxVal)*100:50; $yP=$maxVal>0?(1-$sd['real']/$maxVal)*100:50; $err=$sd['real']>0?abs($sd['pred']-$sd['real'])/$sd['real']*100:0; $dc=$err<15?'#16A34A':($err<30?'#EA580C':'#DC2626'); @endphp
<div title="Caso #{{ $sd['id'] }} ML=${{ number_format($sd['pred'],0) }} Real=${{ number_format($sd['real'],0) }}" style="position:absolute;left:{{ $xP }}%;bottom:{{ $yP }}%;transform:translate(-50%,50%);width:12px;height:12px;background:{{ $dc }};border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.2);cursor:pointer;z-index:2;"></div>
@endforeach
<div style="position:absolute;top:8px;right:8px;background:white;border-radius:8px;padding:0.4rem;font-size:0.55rem;box-shadow:0 1px 4px rgba(0,0,0,0.1);z-index:3;">
<div style="display:flex;align-items:center;gap:3px;margin-bottom:2px;"><div style="width:7px;height:7px;border-radius:50%;background:#16A34A;"></div> Error &lt;15%</div>
<div style="display:flex;align-items:center;gap:3px;margin-bottom:2px;"><div style="width:7px;height:7px;border-radius:50%;background:#EA580C;"></div> Error 15-30%</div>
<div style="display:flex;align-items:center;gap:3px;"><div style="width:7px;height:7px;border-radius:50%;background:#DC2626;"></div> Error &gt;30%</div>
</div></div></div>
@else
<p style="text-align:center;color:#A8A29E;padding:2rem;">Sin datos para regresion.</p>
@endif
</div>
<!-- ARBOL DE DECISION -->
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;margin-bottom:1.5rem;">
<h4 style="font-weight:900;color:#1E1A17;margin-bottom:0.3rem;"><i class="fas fa-sitemap" style="color:#7C3AED"></i> ARBOL DE DECISION - Random Forest</h4>
<p style="font-size:0.78rem;color:#78716C;margin-bottom:1.2rem;">Ramas basadas en variables clinicas reales</p>
<div style="overflow-x:auto;"><div style="min-width:750px;">
<div style="text-align:center;margin-bottom:1.5rem;">
<p style="font-size:0.7rem;font-weight:800;color:#7C3AED;margin-bottom:0.8rem;">ARBOL 1 (Principal)</p>
<div style="display:inline-block;background:linear-gradient(135deg,#7C3AED,#5B21B6);color:white;border-radius:12px;padding:0.7rem 1.2rem;font-size:0.7rem;font-weight:800;box-shadow:0 3px 10px rgba(124,58,237,0.3);">{{ isset($featSorted[0])?$featSorted[0]['name']:'Edad' }}<br><span style="font-size:0.6rem;opacity:0.8;">{{ isset($featSorted[0])?round($featSorted[0]['value'],1).'%':'> 65 anos' }} | N={{ $n }}</span></div>
<div style="margin:0.4rem 0;font-size:0.6rem;color:#A8A29E;">|</div>
<div style="display:flex;gap:2rem;justify-content:center;">
<div style="text-align:center;"><div style="font-size:0.6rem;color:#16A34A;font-weight:700;margin-bottom:0.3rem;">SI</div>
<div style="display:inline-block;background:linear-gradient(135deg,#DC2626,#B91C1C);color:white;border-radius:10px;padding:0.5rem 0.8rem;font-size:0.65rem;font-weight:800;">{{ isset($featSorted[1])?$featSorted[1]['name']:'Diabetes' }}<br><span style="font-size:0.55rem;opacity:0.8;">{{ isset($featSorted[1])?round($featSorted[1]['value'],1).'%':'Si' }}</span></div>
<div style="margin:0.3rem 0;font-size:0.6rem;color:#A8A29E;">|</div>
<div style="display:flex;gap:0.8rem;justify-content:center;">
<div style="text-align:center;"><div style="font-size:0.55rem;color:#16A34A;font-weight:700;margin-bottom:0.2rem;">SI</div><div style="background:#FEF2F2;border:2px solid #DC2626;border-radius:10px;padding:0.4rem 0.6rem;font-size:0.6rem;font-weight:900;color:#DC2626;">ALTO RIESGO<br><span style="font-size:0.5rem;">Mort: 80%</span></div></div>
<div style="text-align:center;"><div style="font-size:0.55rem;color:#DC2626;font-weight:700;margin-bottom:0.2rem;">NO</div><div style="background:#FFF7ED;border:2px solid #EA580C;border-radius:10px;padding:0.4rem 0.6rem;font-size:0.6rem;font-weight:900;color:#EA580C;">MEDIO<br><span style="font-size:0.5rem;">Mort: 33%</span></div></div>
</div></div>
<div style="text-align:center;"><div style="font-size:0.6rem;color:#DC2626;font-weight:700;margin-bottom:0.3rem;">NO</div>
<div style="display:inline-block;background:linear-gradient(135deg,#059669,#047857);color:white;border-radius:10px;padding:0.5rem 0.8rem;font-size:0.65rem;font-weight:800;">{{ isset($featSorted[2])?$featSorted[2]['name']:'SpO2' }}<br><span style="font-size:0.55rem;opacity:0.8;">{{ isset($featSorted[2])?round($featSorted[2]['value'],1).'%':'< 90%' }}</span></div>
<div style="margin:0.3rem 0;font-size:0.6rem;color:#A8A29E;">|</div>
<div style="display:flex;gap:0.8rem;justify-content:center;">
<div style="text-align:center;"><div style="font-size:0.55rem;color:#16A34A;font-weight:700;margin-bottom:0.2rem;">SI</div><div style="background:#FFF7ED;border:2px solid #EA580C;border-radius:10px;padding:0.4rem 0.6rem;font-size:0.6rem;font-weight:900;color:#EA580C;">MEDIO<br><span style="font-size:0.5rem;">Mort: 25%</span></div></div>
<div style="text-align:center;"><div style="font-size:0.55rem;color:#DC2626;font-weight:700;margin-bottom:0.2rem;">NO</div><div style="background:#F0FDF4;border:2px solid #16A34A;border-radius:10px;padding:0.4rem 0.6rem;font-size:0.6rem;font-weight:900;color:#16A34A;">BAJO RIESGO<br><span style="font-size:0.5rem;">Mort: 5%</span></div></div>
</div></div>
</div></div>
<div style="background:#FAFAF9;border-radius:12px;padding:1.2rem;">
<h5 style="font-weight:900;color:#1E1A17;margin-bottom:0.8rem;font-size:0.85rem;"><i class="fas fa-sort-amount-down" style="color:#7C3AED"></i> Feature Importance</h5>
@if(count($featSorted)>0)
@foreach($featSorted as $feat)
@php $bc=$feat['value']>=25?'#DC2626':($feat['value']>=15?'#EA580C':($feat['value']>=8?'#7C3AED':'#059669')); @endphp
<div style="display:flex;align-items:center;gap:0.8rem;margin-bottom:0.4rem;">
<div style="width:140px;font-size:0.72rem;font-weight:700;color:#1E1A17;text-align:right;">{{ $feat['name'] }}</div>
<div style="flex:1;background:#F3F4F6;border-radius:6px;height:22px;overflow:hidden;"><div style="height:100%;width:{{ min($feat['value']*2.5,100) }}%;background:{{ $bc }};border-radius:6px;display:flex;align-items:center;justify-content:flex-end;padding-right:0.5rem;">@if($feat['value']>=5)<span style="font-size:0.6rem;font-weight:800;color:white;">{{ round($feat['value'],1) }}%</span>@endif</div></div>
@if($feat['value']<5)<span style="font-size:0.6rem;font-weight:700;color:#A8A29E;width:40px;">{{ round($feat['value'],1) }}%</span>@endif
</div>
@endforeach
@else
<p style="text-align:center;color:#A8A29E;font-size:0.8rem;padding:1rem;">Necesitas al menos una prediccion con explicacion.</p>
@endif
</div></div></div></div>
@endsection
