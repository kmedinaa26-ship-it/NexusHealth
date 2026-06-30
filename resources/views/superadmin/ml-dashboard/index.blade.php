@extends('superadmin.layout')
@section('title', 'ML Dashboard — Regresion Multiple de Costos')
@section('nav-ml-dashboard', 'active')
@section('content')
@php $d = '$'; @endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
.mc{background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 2px 4px rgba(0,0,0,.04);margin-bottom:1.5rem}
.badge{display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:700}
.ps{display:flex;align-items:flex-start;overflow-x:auto;padding-bottom:.5rem}
.pst{text-align:center;flex:1;min-width:0}
.pst .ic{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin:0 auto .4rem;font-size:1rem;font-weight:800;color:#fff}
.pst .lb{font-size:.68rem;font-weight:700;color:#1E1A17}
.pst .dt{font-size:.58rem;color:#A8A29E;margin-top:.1rem}
.pa{display:flex;align-items:center;color:#E5E7EB;font-size:1rem;flex-shrink:0}
.mt{background:#fff;border-radius:12px;padding:1rem;text-align:center;box-shadow:0 2px 4px rgba(0,0,0,.04)}
.mt .v{font-size:1.6rem;font-weight:800;margin:.3rem 0}
.mt .l{font-size:.7rem;font-weight:600;color:#736860}
.mt .d{font-size:.6rem;color:#A8A29E}
.fbox{background:#1E1A17;color:#fff;border-radius:10px;padding:1rem 1.5rem;font-family:monospace;text-align:center;font-size:.88rem}
.dt{width:100%;border-collapse:collapse;font-size:.75rem}
.dt th{background:#F9FAFB;padding:.5rem;text-align:left;font-weight:700;color:#1E1A17;border-bottom:2px solid #E5E7EB;white-space:nowrap}
.dt td{padding:.45rem;border-bottom:1px solid #F3F4F6;color:#374151}
.dt tr:hover td{background:#FFF8F5}
.mx{padding:.8rem;text-align:center;font-weight:800;font-size:1rem}
.alert-card{border-radius:10px;padding:1rem 1.2rem;display:flex;align-items:flex-start;gap:.8rem;margin-bottom:.6rem}
.alert-critico{background:#FEF2F2;border:1px solid #FECACA}
.alert-advertencia{background:#FFF7ED;border:1px solid #FED7AA}
.alert-ok{background:#F0FDF4;border:1px solid #BBF7D0}
.info-row{display:grid;grid-template-columns:auto 1fr;gap:.2rem .8rem;font-size:.8rem;padding:.4rem 0;border-bottom:1px solid #F3F4F6}
.info-row:last-child{border-bottom:none}
.info-label{font-weight:700;color:#736860}
.info-val{color:#1E1A17}
.chart-box{position:relative;height:260px}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
    <div>
        <h2 style="font-weight:800;color:#1E1A17;margin:0;font-size:1.4rem">
            <i class="fas fa-brain" style="color:#7C3AED"></i> ML Dashboard — Regresion Multiple de Costos
        </h2>
        <p style="color:#736860;margin:.2rem 0 0;font-size:.82rem">Prediccion del costo total hospitalario + modelo predictivo LAG</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <span class="badge" style="background:#F3E8FF;color:#7C3AED"><i class="fas fa-database"></i> {{number_format($n)}} dias</span>
        <span class="badge" style="background:#F0FDF4;color:#2D9E6A"><i class="fas fa-check-circle"></i> Train: {{number_format($sp)}} | Test: {{number_format($n-$sp)}}</span>
        <span class="badge" style="background:#FFF7ED;color:#FF8C42"><i class="fas fa-clock"></i> {{ $lastTraining }}</span>
        <a href="{{ route('superadmin.mlDashboard') }}" style="background:#7C3AED;color:#fff;padding:.3rem .8rem;border-radius:20px;font-size:.75rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem"><i class="fas fa-redo"></i> Reentrenar</a>
    </div>
</div>



<!-- FEED EN VIVO -->
<div class="mc" style="border:2px solid #2D9E6A;border-radius:14px;background:linear-gradient(135deg,#F0FDF4 0%,#FFFFFF 100%)">
    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem">
        <div style="width:10px;height:10px;background:#2D9E6A;border-radius:50%;animation:pulse-dot 1.5s infinite"></div>
        <h3 style="font-weight:800;color:#1E1A17;margin:0;font-size:.95rem"><i class="fas fa-satellite-dish" style="color:#2D9E6A"></i> Datos en Vivo</h3>
        <span style="font-size:.7rem;color:#A8A29E;margin-left:auto">Cada operacion del hospital alimenta el modelo</span>
    </div>
    @if(count($mlFeeds) > 0)
    <div style="max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:.4rem" id="feedList">
        @foreach($mlFeeds as $i => $feed)
        @php
            $modIcon = match($feed->source_module) {
                'farmacia' => 'fa-pills',
                'quirofano' => 'fa-cut',
                'medico' => 'fa-stethoscope',
                'enfermeria' => 'fa-bed-pulse',
                default => 'fa-receipt',
            };
            $modColor = match($feed->source_module) {
                'farmacia' => '#2D9E6A',
                'quirofano' => '#F05A4E',
                'medico' => '#3B82F6',
                'enfermeria' => '#FF8C42',
                default => '#736860',
            };
            $ago = $feed->created_at->diffForHumans();
        @endphp
        <div class="feed-item" style="display:flex;align-items:center;gap:.8rem;padding:.6rem .8rem;background:#fff;border-radius:10px;border:1px solid #E5E7EB;animation:feed-in .4s ease-out both;animation-delay:{{ $i * 80 }}ms">
            <div style="width:36px;height:36px;border-radius:8px;background:{{ $modColor }}15;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas {{ $modIcon }}" style="color:{{ $modColor }};font-size:.85rem"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:.5rem">
                    <span style="font-weight:700;font-size:.8rem;color:#1E1A17;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $feed->patient_name }}</span>
                    <span style="font-size:.65rem;background:{{ $modColor }}15;color:{{ $modColor }};padding:.15rem .5rem;border-radius:10px;font-weight:600">{{ $feed->concept }}</span>
                </div>
                <div style="font-size:.7rem;color:#A8A29E;margin-top:.15rem">{{ $feed->source_detail ?? '' }}</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:.85rem;font-weight:800;color:#1E1A17">{{ $d }}{{ number_format($feed->amount,0) }}</div>
                <div style="font-size:.6rem;color:#A8A29E">{{ $ago }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align:center;padding:2rem;color:#A8A29E">
        <i class="fas fa-satellite-dish" style="font-size:2rem;margin-bottom:.5rem;display:block;opacity:.3"></i>
        <p style="font-size:.82rem;font-weight:600;margin:0">Esperando datos en vivo...</p>
        <p style="font-size:.72rem;margin:.3rem 0 0">Los datos aparecen aqui cuando se realizan operaciones reales (quirofano, consultas, farmacia POS)</p>
        <a href="{{ route('superadmin.mlDashboard.generateDemo') }}" style="display:inline-block;margin-top:.8rem;background:#2D9E6A;color:#fff;padding:.4rem 1rem;border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none">Generar datos demo</a>
    </div>
    @endif
</div>
<style>
@keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.3)} }
@keyframes feed-in { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }
.feed-item:hover { border-color:#2D9E6A !important; }
</style>


<!-- INGESTA DE DATOS -->
<div class="mc" style="background:linear-gradient(135deg,#F9FAFB 0%,#F3E8FF 100%);border:2px dashed #7C3AED">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h3 style="font-weight:800;color:#1E1A17;margin:0;font-size:.95rem"><i class="fas fa-upload" style="color:#7C3AED"></i> Ingesta de Datos</h3>
            <p style="font-size:.78rem;color:#736860;margin:.15rem 0 0">Sube un CSV/TXT con nuevos datos o genera datos demo para probar el modelo</p>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <a href="{{ route('superadmin.mlDashboard.template') }}" style="background:#fff;border:1px solid #E5E7EB;color:#374151;padding:.4rem .8rem;border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem"><i class="fas fa-download" style="color:#7C3AED"></i> Descargar Plantilla</a>
            <a href="{{ route('superadmin.mlDashboard.generateDemo') }}" onclick="return confirm('Se generaran ~500 registros demo de 30 dias. Continuar?')" style="background:#2D9E6A;color:#fff;padding:.4rem .8rem;border-radius:8px;font-size:.78rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem"><i class="fas fa-magic"></i> Generar Datos Demo</a>
        </div>
    </div>
    <form method="POST" action="{{ route('superadmin.mlDashboard.upload') }}" enctype="multipart/form-data">
        @csrf
        <div style="display:flex;gap:.8rem;align-items:center;flex-wrap:wrap">
            <label style="display:flex;align-items:center;gap:.5rem;background:#fff;border:1px solid #E5E7EB;border-radius:8px;padding:.5rem 1rem;cursor:pointer;flex:1;min-width:200px">
                <i class="fas fa-file-csv" style="color:#7C3AED;font-size:1.2rem"></i>
                <span id="fileName" style="font-size:.8rem;color:#736860">Seleccionar archivo CSV o TXT...</span>
                <input type="file" name="archivo" accept=".csv,.txt" style="display:none" onchange="document.getElementById('fileName').textContent=this.files[0].name">
            </label>
            <button type="submit" style="background:#7C3AED;color:#fff;border:none;padding:.5rem 1.2rem;border-radius:8px;font-size:.8rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.3rem"><i class="fas fa-cloud-upload-alt"></i> Subir y Procesar</button>
        </div>
        <div style="margin-top:.6rem;font-size:.68rem;color:#A8A29E">Formato: patient_name, concept, amount, status &mdash; Conceptos validos: Medicamentos, Consulta Urgencias, Cirugia, UCI</div>
    </form>
    @if(session('ml_success'))
    <div style="margin-top:.8rem;padding:.6rem 1rem;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;display:flex;align-items:center;gap:.5rem">
        <i class="fas fa-check-circle" style="color:#2D9E6A"></i>
        <span style="font-size:.8rem;font-weight:600;color:#2D9E6A">{{ session('ml_success') }}</span>
    </div>
    @endif
    @if(session('ml_warning'))
    <div style="margin-top:.8rem;padding:.6rem 1rem;background:#FFF7ED;border:1px solid #FED7AA;border-radius:8px;display:flex;align-items:center;gap:.5rem">
        <i class="fas fa-exclamation-triangle" style="color:#FF8C42"></i>
        <span style="font-size:.8rem;font-weight:600;color:#FF8C42">{{ session('ml_warning') }}</span>
    </div>
    @endif
    @if(session('ml_error'))
    <div style="margin-top:.8rem;padding:.6rem 1rem;background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;display:flex;align-items:center;gap:.5rem">
        <i class="fas fa-times-circle" style="color:#F05A4E"></i>
        <span style="font-size:.8rem;font-weight:600;color:#F05A4E">{{ session('ml_error') }}</span>
    </div>
    @endif
</div>


<div class="mc">
    <h3 style="font-weight:800;color:#1E1A17;margin:0 0 1rem;font-size:.92rem"><i class="fas fa-project-diagram" style="color:#7C3AED"></i> Pipeline ML — 7 Etapas</h3>
    <div class="ps">
        <div class="pst"><div class="ic" style="background:#7C3AED">1</div><div class="lb">Datos</div><div class="dt">8,083 invoices</div></div><div class="pa">&rarr;</div>
        <div class="pst"><div class="ic" style="background:#F05A4E">2</div><div class="lb">Limpieza</div><div class="dt">Sin nulos</div></div><div class="pa">&rarr;</div>
        <div class="pst"><div class="ic" style="background:#FF8C42">3</div><div class="lb">Variables</div><div class="dt">Med, Hosp, Quir</div></div><div class="pa">&rarr;</div>
        <div class="pst"><div class="ic" style="background:#3B82F6">4</div><div class="lb">Train/Test</div><div class="dt">80 / 20</div></div><div class="pa">&rarr;</div>
        <div class="pst"><div class="ic" style="background:#7C3AED">5</div><div class="lb">Modelo</div><div class="dt">Reg. Multiple</div></div><div class="pa">&rarr;</div>
        <div class="pst"><div class="ic" style="background:#2D9E6A">6</div><div class="lb">Evaluacion</div><div class="dt">MSE, R2</div></div><div class="pa">&rarr;</div>
        <div class="pst"><div class="ic" style="background:#F05A4E">7</div><div class="lb">Produccion</div><div class="dt">Prediccion</div></div>
    </div>
</div>

<div class="mc">
    <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.92rem"><i class="fas fa-info-circle" style="color:#3B82F6"></i> Informacion utilizada por el modelo</h3>
    <p style="font-size:.8rem;color:#736860;margin:0 0 .8rem">Toda la informacion proviene de la tabla <strong>invoices</strong>, con 8,083 registros clasificados por concepto.</p>
    <table class="dt" style="max-width:700px">
        <thead><tr><th>Concepto</th><th style="text-align:right">Registros</th><th>Uso en el modelo</th></tr></thead>
        <tbody>
            <tr><td style="font-weight:700">Medicamentos</td><td style="text-align:right">3,083</td><td>Variable independiente (x1)</td></tr>
            <tr><td style="font-weight:700">UCI</td><td style="text-align:right">974</td><td>Variable independiente (x2), mostrada como Hospitalizacion</td></tr>
            <tr><td style="font-weight:700">Cirugia</td><td style="text-align:right">1,030</td><td>Variable independiente (x3), mostrada como Quirofano</td></tr>
            <tr><td style="font-weight:700">Consulta Urgencias</td><td style="text-align:right">2,996</td><td>Se incluye en el costo total pero no participa en la regresion</td></tr>
        </tbody>
    </table>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
    <div class="mc">
        <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.92rem"><i class="fas fa-superscript" style="color:#FF8C42"></i> Modelo Entrenado</h3>
        <div class="fbox">y = {{number_format($model['beta']['b0'],2)}} + {{number_format($model['beta']['b1'],4)}}*Med + {{number_format($model['beta']['b2'],4)}}*Hosp + {{number_format($model['beta']['b3'],4)}}*Quir</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;margin-top:.8rem">
            <div style="text-align:center;padding:.4rem;background:#F3E8FF;border-radius:8px"><div style="font-size:.6rem;color:#736860;font-weight:600">b0</div><div style="font-size:.9rem;font-weight:800;color:#7C3AED">{{number_format($model['beta']['b0'],2)}}</div></div>
            <div style="text-align:center;padding:.4rem;background:#F0FDF4;border-radius:8px"><div style="font-size:.6rem;color:#736860;font-weight:600">b1 Med</div><div style="font-size:.9rem;font-weight:800;color:#2D9E6A">{{number_format($model['beta']['b1'],4)}}</div></div>
            <div style="text-align:center;padding:.4rem;background:#FFF7ED;border-radius:8px"><div style="font-size:.6rem;color:#736860;font-weight:600">b2 Hosp</div><div style="font-size:.9rem;font-weight:800;color:#FF8C42">{{number_format($model['beta']['b2'],4)}}</div></div>
            <div style="text-align:center;padding:.4rem;background:#FEF2F2;border-radius:8px"><div style="font-size:.6rem;color:#736860;font-weight:600">b3 Quir</div><div style="font-size:.9rem;font-weight:800;color:#F05A4E">{{number_format($model['beta']['b3'],4)}}</div></div>
        </div>
    </div>
    <div class="mc" style="background:#FFFBF5">
        <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.92rem"><i class="fas fa-question-circle" style="color:#FF8C42"></i> Por que el R2 es tan alto?</h3>
        <p style="font-size:.78rem;color:#374151;line-height:1.5;margin:0 0 .6rem">El R2 de <strong>{{number_format($tR2,4)}}</strong> es completamente esperado. El costo total es la <strong>suma directa</strong> de los conceptos usados como variables predictoras.</p>
        <ul style="font-size:.75rem;color:#374151;padding-left:1.2rem;margin:0;line-height:1.6">
            <li>Los coeficientes son ~<strong>1</strong> (cada variable aporta su valor)</li>
            <li>El error es muy pequeno</li>
            <li>Esto <strong>valida</strong> que la regresion multiple funciona correctamente</li>
            <li>Es una buena <strong>prueba de concepto</strong> con datos reales</li>
        </ul>
        <p style="font-size:.7rem;color:#A8A29E;margin:.6rem 0 0">Para un sistema predictivo real, ver el modelo LAG mas abajo.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="mt"><div class="l">MSE</div><div class="v" style="color:#F05A4E">{{number_format($tMSE,0)}}</div><div class="d">Error Cuadratico Medio</div></div>
    <div class="mt"><div class="l">RMSE</div><div class="v" style="color:#FF8C42">{{number_format($tRMSE,0)}}</div><div class="d">Raiz del ECM</div></div>
    <div class="mt"><div class="l">MAE</div><div class="v" style="color:#7C3AED">{{number_format($tMAE,0)}}</div><div class="d">Error Absoluto Medio</div></div>
    <div class="mt"><div class="l">R2</div><div class="v" style="color:#2D9E6A">{{number_format($tR2,4)}}</div><div class="d">Coef. Determinacion</div></div>
</div>

<div class="mc" style="padding:1rem 1.5rem">
    <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.92rem"><i class="fas fa-bell" style="color:#F05A4E"></i> Alertas Automaticas</h3>
    @foreach($alertas as $a)
    <div class="alert-card alert-{{ $a['tipo'] }}">
        <i class="fas {{ $a['icono'] }}" style="margin-top:2px;color:{{ $a['tipo']=='critico'?'#F05A4E':($a['tipo']=='advertencia'?'#FF8C42':'#2D9E6A') }}"></i>
        <div><div style="font-weight:700;font-size:.82rem;color:#1E1A17">{{ $a['titulo'] }}</div><div style="font-size:.75rem;color:#736860;margin-top:.1rem">{{ $a['msg'] }}</div></div>
    </div>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
    <div class="mc"><h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.88rem"><i class="fas fa-chart-line" style="color:#7C3AED"></i> Real vs Predicho</h3><div class="chart-box"><canvas id="chartRP"></canvas></div></div>
    <div class="mc"><h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.88rem"><i class="fas fa-chart-area" style="color:#F05A4E"></i> Evolucion del Error</h3><div class="chart-box"><canvas id="chartErr"></canvas></div></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
    <div class="mc"><h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.88rem"><i class="fas fa-chart-bar" style="color:#2D9E6A"></i> Tendencia Semanal</h3><div class="chart-box"><canvas id="chartWeek"></canvas></div></div>
    <div class="mc"><h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.88rem"><i class="fas fa-chart-pie" style="color:#FF8C42"></i> Distribucion por Concepto</h3><div class="chart-box"><canvas id="chartConcept"></canvas></div></div>
</div>

<div class="mc">
    <h3 style="font-weight:800;color:#1E1A17;margin:0 0 1rem;font-size:.92rem"><i class="fas fa-table" style="color:#7C3AED"></i> Predicciones vs Real — Test ({{count($tabla)}} dias)</h3>
    <div style="max-height:350px;overflow-y:auto;border-radius:8px;border:1px solid #E5E7EB">
        <table class="dt"><thead style="position:sticky;top:0;z-index:1"><tr>
            <th>Fecha</th><th style="text-align:right">Med</th><th style="text-align:right">Hosp</th><th style="text-align:right">Quir</th><th style="text-align:right">Cons</th><th style="text-align:right">Real</th><th style="text-align:right">Pred</th><th style="text-align:right">|Error|</th>
        </tr></thead><tbody>
        @foreach($tabla as $r)
        @php $errColor = $r['error'] > $tMAE*1.5 ? '#F05A4E' : '#2D9E6A'; @endphp
        <tr>
            <td style="font-weight:600;white-space:nowrap">{{$r['fecha']}}</td>
            <td style="text-align:right">{{ $d }}{{number_format($r['med'],0)}}</td>
            <td style="text-align:right">{{ $d }}{{number_format($r['hosp'],0)}}</td>
            <td style="text-align:right">{{ $d }}{{number_format($r['quir'],0)}}</td>
            <td style="text-align:right">{{ $d }}{{number_format($r['cons'],0)}}</td>
            <td style="text-align:right;font-weight:700">{{ $d }}{{number_format($r['real'],0)}}</td>
            <td style="text-align:right;font-weight:700;color:#7C3AED">{{ $d }}{{number_format($r['pred'],0)}}</td>
            <td style="text-align:right;font-weight:700;color:{{ $errColor }}">{{ $d }}{{number_format($r['error'],0)}}</td>
        </tr>
        @endforeach
        </tbody></table>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
    <div class="mc">
        <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.88rem"><i class="fas fa-th" style="color:#FF8C42"></i> Riesgo Financiero</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.78rem;text-align:center">
            <tr style="background:#F9FAFB"><td style="padding:.4rem"></td><td style="padding:.4rem;font-weight:700">Pred: Alto</td><td style="padding:.4rem;font-weight:700">Pred: No</td></tr>
            <tr><td style="padding:.4rem;font-weight:700;background:#F9FAFB">Real: Alto</td><td class="mx" style="background:#F0FDF4;color:#2D9E6A">{{$matriz['vp']??0}}</td><td class="mx" style="background:#FEF2F2;color:#F05A4E">{{$matriz['fn']??0}}</td></tr>
            <tr><td style="padding:.4rem;font-weight:700;background:#F9FAFB">Real: No</td><td class="mx" style="background:#FFF7ED;color:#FF8C42">{{$matriz['fp']??0}}</td><td class="mx" style="background:#F0FDF4;color:#2D9E6A">{{$matriz['vn']??0}}</td></tr>
        </table>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.8rem;font-size:.72rem">
            <div style="background:#F9FAFB;padding:.4rem;border-radius:6px;text-align:center"><div style="color:#736860">Accuracy</div><div style="font-weight:800">{{number_format(($classM['accuracy']??0)*100,1)}}%</div></div>
            <div style="background:#F9FAFB;padding:.4rem;border-radius:6px;text-align:center"><div style="color:#736860">Precision</div><div style="font-weight:800">{{number_format(($classM['precision']??0)*100,1)}}%</div></div>
            <div style="background:#F9FAFB;padding:.4rem;border-radius:6px;text-align:center"><div style="color:#736860">Recall</div><div style="font-weight:800">{{number_format(($classM['recall']??0)*100,1)}}%</div></div>
            <div style="background:#F9FAFB;padding:.4rem;border-radius:6px;text-align:center"><div style="color:#736860">F1-Score</div><div style="font-weight:800">{{number_format(($classM['f1']??0)*100,1)}}%</div></div>
        </div>
    </div>
    <div class="mc">
        <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.88rem"><i class="fas fa-percentage" style="color:#7C3AED"></i> Explicabilidad</h3>
        <p style="font-size:.72rem;color:#736860;margin:0 0 .8rem">Contribucion relativa de cada variable al gasto total</p>
        @foreach($explicabilidad as $e)
        <div style="margin-bottom:.8rem">
            <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:.2rem">
                <span style="font-weight:700;color:#1E1A17">{{$e['concepto']}}</span>
                <span style="font-weight:800;color:{{$e['color']}}">{{$e['pct']}}%</span>
            </div>
            <div style="width:100%;background:#E5E7EB;border-radius:6px;height:10px">
                <div style="width:{{$e['pct']}}%;background:{{$e['color']}};height:10px;border-radius:6px;transition:width .5s"></div>
            </div>
        </div>
        @endforeach
        <div style="margin-top:.8rem;padding:.6rem;background:#F9FAFB;border-radius:8px;text-align:center">
            <div style="font-size:.65rem;color:#A8A29E">Costo promedio diario</div>
            <div style="font-size:1.1rem;font-weight:800;color:#1E1A17">{{ $d }}{{number_format($avgCost,0)}}</div>
        </div>
    </div>
    <div class="mc">
        <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.88rem"><i class="fas fa-chart-bar" style="color:#2D9E6A"></i> Stats por Concepto</h3>
        <table class="dt"><thead><tr><th>Concepto</th><th style="text-align:right">Total</th><th style="text-align:right">Prom</th></tr></thead><tbody>
        @foreach($stats as $s)
        <tr>
            <td style="font-weight:700;font-size:.72rem">{{$s['concepto']}}</td>
            <td style="text-align:right;font-size:.72rem">{{ $d }}{{number_format($s['suma'],0)}}</td>
            <td style="text-align:right;font-size:.72rem">{{ $d }}{{number_format($s['promedio'],0)}}</td>
        </tr>
        @endforeach
        </tbody></table>
    </div>
</div>

<div class="mc" style="border:2px solid #7C3AED;border-radius:14px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
        <div>
            <h3 style="font-weight:800;color:#7C3AED;margin:0;font-size:1rem"><i class="fas fa-forward"></i> Modelo Predictivo (LAG)</h3>
            <p style="font-size:.78rem;color:#736860;margin:.15rem 0 0">Usa datos del dia N-1 para predecir el dia N</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem">
            <div style="text-align:center;padding:.4rem .6rem;background:#F3E8FF;border-radius:8px"><div style="font-size:.6rem;color:#736860;font-weight:600">MSE</div><div style="font-size:.85rem;font-weight:800;color:#F05A4E">{{number_format($lMSE,0)}}</div></div>
            <div style="text-align:center;padding:.4rem .6rem;background:#F3E8FF;border-radius:8px"><div style="font-size:.6rem;color:#736860;font-weight:600">RMSE</div><div style="font-size:.85rem;font-weight:800;color:#FF8C42">{{number_format($lRMSE,0)}}</div></div>
            <div style="text-align:center;padding:.4rem .6rem;background:#F3E8FF;border-radius:8px"><div style="font-size:.6rem;color:#736860;font-weight:600">MAE</div><div style="font-size:.85rem;font-weight:800;color:#7C3AED">{{number_format($lMAE,0)}}</div></div>
            <div style="text-align:center;padding:.4rem .6rem;background:#F3E8FF;border-radius:8px"><div style="font-size:.6rem;color:#736860;font-weight:600">R2</div><div style="font-size:.85rem;font-weight:800;color:#2D9E6A">{{number_format($lR2,4)}}</div></div>
        </div>
    </div>
    <div class="fbox" style="background:#7C3AED;margin-bottom:1rem">y(t) = {{number_format($lagModel['beta']['b0'],2)}} + {{number_format($lagModel['beta']['b1'],4)}}*Med(t-1) + {{number_format($lagModel['beta']['b2'],4)}}*Hosp(t-1) + {{number_format($lagModel['beta']['b3'],4)}}*Quir(t-1)</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
        <div>
            <h4 style="font-weight:700;color:#1E1A17;margin:0 0 .6rem;font-size:.85rem">Predicciones LAG — Test</h4>
            <div style="max-height:250px;overflow-y:auto;border-radius:8px;border:1px solid #E5E7EB">
                <table class="dt"><thead style="position:sticky;top:0;z-index:1"><tr><th>Fecha</th><th style="text-align:right">Real</th><th style="text-align:right">Pred</th><th style="text-align:right">|Error|</th></tr></thead><tbody>
                @foreach($lagTabla as $r)
                @php $lErrColor = $r['error'] > $lMAE*1.5 ? '#F05A4E' : '#2D9E6A'; @endphp
                <tr>
                    <td style="font-weight:600;white-space:nowrap">{{$r['fecha']}}</td>
                    <td style="text-align:right">{{ $d }}{{number_format($r['real'],0)}}</td>
                    <td style="text-align:right;color:#7C3AED;font-weight:700">{{ $d }}{{number_format($r['pred'],0)}}</td>
                    <td style="text-align:right;font-weight:700;color:{{ $lErrColor }}">{{ $d }}{{number_format($r['error'],0)}}</td>
                </tr>
                @endforeach
                </tbody></table>
            </div>
        </div>
        <div>
            <h4 style="font-weight:700;color:#1E1A17;margin:0 0 .6rem;font-size:.85rem"><i class="fas fa-forward" style="color:#7C3AED"></i> Proximos 7 Dias</h4>
            <div style="border-radius:8px;border:1px solid #E5E7EB;overflow:hidden">
                @foreach($futuro as $f)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 1rem;{{ $loop->last ? '' : 'border-bottom:1px solid #F3F4F6' }};background:{{ $loop->index % 2 == 0 ? '#FAFAF9' : '#fff' }}">
                    <div>
                        <div style="font-weight:700;font-size:.82rem;color:#1E1A17">{{$f['fecha']}}</div>
                        <div style="font-size:.7rem;color:#A8A29E;text-transform:capitalize">{{$f['dia']}}</div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:.65rem;color:#736860">Costo estimado</div>
                        <div style="font-size:1rem;font-weight:800;color:#7C3AED">{{ $d }}{{number_format($f['pred'],0)}}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="mc" style="background:#F9FAFB">
    <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.92rem"><i class="fas fa-list-check" style="color:#2D9E6A"></i> Funcionalidades Actuales</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 2rem;font-size:.8rem">
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Pipeline ML 7 pasos</span><span class="info-val">Flujo completo de datos a produccion</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Ecuacion entrenada</span><span class="info-val">Visualizacion de coeficientes</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Metricas de evaluacion</span><span class="info-val">MSE, RMSE, MAE, R2</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Tabla predicciones vs real</span><span class="info-val">Con error absoluto por dia</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Matriz de confusion</span><span class="info-val">Clasificacion riesgo financiero</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Estadisticas por concepto</span><span class="info-val">Promedio, maximo, minimo</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Modelo LAG predictivo</span><span class="info-val">Pronostico con datos del dia anterior</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Prediccion 7 dias</span><span class="info-val">Proyeccion futura de costos</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Alertas automaticas</span><span class="info-val">Deteccion de anomalias en costos</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Explicabilidad</span><span class="info-val">Contribucion porcentual de cada variable</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Graficos interactivos</span><span class="info-val">Real vs Pred, Error, Tendencia, Distribucion</span></div>
        <div class="info-row"><span class="info-label"><i class="fas fa-check" style="color:#2D9E6A;margin-right:.4rem"></i>Reentrenamiento</span><span class="info-val">Boton de reentrenar en vivo</span></div>
    </div>
</div>

<div class="mc">
    <h3 style="font-weight:800;color:#1E1A17;margin:0 0 .8rem;font-size:.92rem"><i class="fas fa-rocket" style="color:#FF8C42"></i> Proximas Mejoras</h3>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
        <div style="padding:1rem;background:#F3E8FF;border-radius:10px">
            <div style="font-weight:800;color:#7C3AED;font-size:.85rem;margin-bottom:.4rem"><i class="fas fa-layer-group"></i> Mas Variables</div>
            <p style="font-size:.72rem;color:#374151;line-height:1.5;margin:0">Incorporar pacientes atendidos, camas disponibles, ocupacion hospitalaria, dia de la semana y festivos como variables independientes adicionales.</p>
        </div>
        <div style="padding:1rem;background:#FFF7ED;border-radius:10px">
            <div style="font-weight:800;color:#FF8C42;font-size:.85rem;margin-bottom:.4rem"><i class="fas fa-clock"></i> Entrenamiento Auto</div>
            <p style="font-size:.72rem;color:#374151;line-height:1.5;margin:0">Programar reentrenamiento automatico cada noche con un cron job de Laravel, manteniendo el modelo siempre actualizado sin intervencion del usuario.</p>
        </div>
        <div style="padding:1rem;background:#F0FDF4;border-radius:10px">
            <div style="font-weight:800;color:#2D9E6A;font-size:.85rem;margin-bottom:.4rem"><i class="fas fa-bell"></i> Alertas Avanzadas</div>
            <p style="font-size:.72rem;color:#374151;line-height:1.5;margin:0">Notificaciones por email o Slack cuando el costo estimado supere umbrales, con historial de alertas y tendencias de desviacion.</p>
        </div>
    </div>
</div>

<script>
const RP = @json($chartRP);
const ER = @json($chartErr);
const WK = @json($weekly);
const ST = @json($stats);
const DL = '$';

new Chart(document.getElementById('chartRP'),{type:'line',data:{labels:RP.map(d=>d.f),datasets:[
    {label:'Real',data:RP.map(d=>d.r),borderColor:'#1E1A17',backgroundColor:'rgba(30,26,23,.1)',fill:true,tension:.3,pointRadius:2},
    {label:'Predicho',data:RP.map(d=>d.p),borderColor:'#7C3AED',borderDash:[5,5],tension:.3,pointRadius:2}
]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{font:{size:11}}}},scales:{y:{beginAtZero:false,ticks:{callback:v=>DL+v.toLocaleString()}},x:{ticks:{maxRotation:45,font:{size:9}}}}}});

new Chart(document.getElementById('chartErr'),{type:'line',data:{labels:ER.map(d=>d.f),datasets:[
    {label:'Error',data:ER.map(d=>d.e),borderColor:'#F05A4E',backgroundColor:'rgba(240,90,78,.15)',fill:true,tension:.3,pointRadius:2},
    {label:'MAE',data:ER.map(d=>d.m),borderColor:'#2D9E6A',borderDash:[8,4],pointRadius:0}
]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{font:{size:11}}}},scales:{y:{beginAtZero:true,ticks:{callback:v=>DL+v.toLocaleString()}},x:{ticks:{maxRotation:45,font:{size:9}}}}}});

new Chart(document.getElementById('chartWeek'),{type:'bar',data:{labels:WK.map(d=>d.s),datasets:[
    {label:'Total Semanal',data:WK.map(d=>d.t),backgroundColor:'#7C3AED',borderRadius:6},
    {label:'Promedio Diario',data:WK.map(d=>d.a),backgroundColor:'#FF8C42',borderRadius:6}
]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{font:{size:11}}}},scales:{y:{beginAtZero:true,ticks:{callback:v=>DL+v.toLocaleString()}},x:{ticks:{font:{size:10}}}}}});

const cColors=['#2D9E6A','#FF8C42','#F05A4E','#7C3AED'];
new Chart(document.getElementById('chartConcept'),{type:'doughnut',data:{labels:ST.map(s=>s.concepto),datasets:[{data:ST.map(s=>s.suma),backgroundColor:cColors,borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'right',labels:{font:{size:11},padding:12}},tooltip:{callbacks:{label:ctx=>' '+DL+ctx.parsed.toLocaleString()}}}}});
</script>
@endsection
