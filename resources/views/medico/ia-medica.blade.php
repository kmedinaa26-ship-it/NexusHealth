@extends('medico.layout')
@section('title', 'IA Medica - Motor Predictivo')
@section('nav-ia', 'active')
@section('content')
<style>
/* ── TOKENS DEL SISTEMA ─────────────────────────────────────────────────── */
:root{
  --c1:#DC2626;--c2:#EA580C;--c3:#F97316;
  --bg:#F9F8F6;--card:#fff;--border:#E7E5E4;
  --txt:#1C1917;--sub:#57534E;--muted:#A8A29E;
  --radius:14px;--shadow:0 1px 4px rgba(0,0,0,.06);
  --shadow-hover:0 8px 24px rgba(0,0,0,.1);
}

/* ── LAYOUT ─────────────────────────────────────────────────────────────── */
.ia-wrap{max-width:1380px;margin:0 auto}

/* ── TOPBAR ─────────────────────────────────────────────────────────────── */
.ia-top{background:var(--card);border-radius:var(--radius);padding:1.1rem 1.4rem;
  box-shadow:var(--shadow);border-left:4px solid var(--c1);margin-bottom:1.2rem;
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.8rem}
.ia-top h1{font-size:1.15rem;font-weight:800;color:var(--txt);margin:0;display:flex;align-items:center;gap:.5rem}
.ia-top p{font-size:.75rem;color:var(--sub);margin:.2rem 0 0}
.badge-mejor{display:inline-flex;align-items:center;gap:.3rem;
  background:#FEF9C3;border:1px solid #FDE047;color:#854D0E;
  padding:.25rem .7rem;border-radius:20px;font-size:.72rem;font-weight:700}
.badge-src{display:inline-flex;align-items:center;gap:.3rem;
  background:#F0FDF4;border:1px solid #86EFAC;color:#166534;
  padding:.25rem .7rem;border-radius:20px;font-size:.72rem;font-weight:700}

/* ── PIPELINE ───────────────────────────────────────────────────────────── */
.pipe-card{background:var(--card);border-radius:var(--radius);
  box-shadow:var(--shadow);margin-bottom:1.2rem;overflow:hidden}
.pipe-card-head{padding:.8rem 1.2rem;background:linear-gradient(135deg,#1C1917,#292524);
  display:flex;align-items:center;gap:.5rem}
.pipe-card-head h2{font-size:.88rem;font-weight:800;color:#fff;margin:0}
.pipe-card-head span{font-size:.7rem;color:#A8A29E;margin-left:.3rem}
.pipe-body{padding:1rem 1.2rem}
.pipe-steps{display:flex;align-items:flex-start;gap:0;position:relative}
.pipe-steps::before{content:'';position:absolute;top:20px;left:20px;right:20px;
  height:2px;background:linear-gradient(90deg,#DC2626,#EA580C,#F97316,#EAB308,#10B981,#6366F1,#14B8A6);
  z-index:0}
.pipe-step{flex:1;text-align:center;position:relative;z-index:1}
.pipe-dot{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;
  justify-content:center;margin:0 auto .5rem;font-size:.85rem;color:#fff;
  box-shadow:0 4px 12px rgba(0,0,0,.15);border:3px solid #fff}
.pipe-step-label{font-size:.68rem;font-weight:700;color:var(--txt)}
.pipe-step-sub{font-size:.62rem;color:var(--sub);margin-top:.1rem;line-height:1.3}
.pipe-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;margin-top:1rem;
  padding-top:1rem;border-top:1px solid var(--border)}
.pipe-stat{background:#F9F8F6;border-radius:10px;padding:.7rem;text-align:center}
.pipe-stat .psv{font-size:1.4rem;font-weight:800;color:var(--txt)}
.pipe-stat .psl{font-size:.68rem;color:var(--sub);font-weight:600}

/* ── DATASET ────────────────────────────────────────────────────────────── */
.ds-card{background:var(--card);border-radius:var(--radius);
  box-shadow:var(--shadow);margin-bottom:1.2rem;overflow:hidden}
.ds-head{padding:.8rem 1.2rem;display:flex;align-items:center;justify-content:space-between;
  border-bottom:1px solid var(--border)}
.ds-head h2{font-size:.88rem;font-weight:800;color:var(--txt);margin:0;display:flex;align-items:center;gap:.4rem}
.ds-tabla{width:100%;border-collapse:collapse;font-size:.75rem}
.ds-tabla th{padding:.5rem .8rem;text-align:left;font-weight:700;color:var(--sub);
  background:#F9F8F6;border-bottom:2px solid var(--border);font-size:.68rem;text-transform:uppercase;letter-spacing:.5px}
.ds-tabla td{padding:.45rem .8rem;border-bottom:1px solid #F5F5F4;color:var(--txt)}
.ds-tabla tr:hover td{background:#FAFAF9}
.nivel-pill{padding:.15rem .55rem;border-radius:20px;font-size:.65rem;font-weight:700}

/* ── SECCION MODELOS ────────────────────────────────────────────────────── */
.sec-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem}
.sec-head h2{font-size:.95rem;font-weight:800;color:var(--txt);display:flex;align-items:center;gap:.4rem}
.modelos-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1rem;margin-bottom:1.2rem}

/* ── CARD MODELO ────────────────────────────────────────────────────────── */
.mc{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);
  overflow:hidden;transition:transform .2s,box-shadow .2s;position:relative}
.mc:hover{transform:translateY(-3px);box-shadow:var(--shadow-hover)}
.mc-stripe{height:4px;width:100%}
.mc-header{padding:.9rem 1.1rem;display:flex;align-items:center;justify-content:space-between;
  border-bottom:1px solid var(--border)}
.mc-header h3{font-size:.9rem;font-weight:800;color:var(--txt);margin:0}
.mc-icon-wrap{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;
  justify-content:center;font-size:.85rem;color:#fff}
.mc-body{padding:1rem 1.1rem}
.mc-desc{font-size:.75rem;color:var(--sub);margin-bottom:.8rem;line-height:1.5}

/* ── FORMULA BOX ────────────────────────────────────────────────────────── */
.fbox{border-radius:8px;padding:.6rem .8rem;margin-bottom:.8rem;
  font-family:'Courier New',monospace;font-size:.72rem;border-left:3px solid;
  background:#F9F8F6}
.fbox .fbox-title{font-size:.6rem;font-weight:700;text-transform:uppercase;
  letter-spacing:.5px;margin-bottom:.3rem;opacity:.7}
.fbox .fbox-main{font-weight:700;font-size:.78rem}
.fbox .fbox-sub{font-size:.65rem;opacity:.75;margin-top:.2rem;font-family:'Inter',sans-serif}

/* ── COEFICIENTES ───────────────────────────────────────────────────────── */
.coef-grid{display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.8rem}
.coef-item{background:#F9F8F6;border:1px solid var(--border);border-radius:6px;
  padding:.2rem .5rem;font-family:monospace;font-size:.68rem;color:var(--txt)}

/* ── METRICAS CLASIFICACION ─────────────────────────────────────────────── */
.met-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.3rem;margin-bottom:.6rem}
.met-box{background:#F9F8F6;border-radius:8px;padding:.5rem .3rem;text-align:center;
  border-bottom:2px solid transparent;transition:all .2s}
.met-box:hover{background:#fff;box-shadow:var(--shadow)}
.met-box .mv{font-size:1rem;font-weight:800}
.met-box .ml{font-size:.6rem;color:var(--sub);font-weight:700;text-transform:uppercase;letter-spacing:.3px}

/* ── FORMULA METRICAS ───────────────────────────────────────────────────── */
.met-formulas{background:#1C1917;border-radius:8px;padding:.6rem .8rem;
  font-family:monospace;font-size:.64rem;color:#D6D3D1;margin-bottom:.8rem;line-height:1.8}
.met-formulas .mf-row{display:flex;gap:.5rem;flex-wrap:wrap}
.met-formulas span{color:#F97316;font-weight:700}

/* ── MATRIZ CONFUSION ───────────────────────────────────────────────────── */
.matriz-sec{margin-bottom:.8rem}
.matriz-sec h4{font-size:.72rem;font-weight:700;color:var(--sub);text-transform:uppercase;
  letter-spacing:.5px;margin-bottom:.4rem;display:flex;align-items:center;gap:.3rem}
.m-grid{display:grid;grid-template-columns:1fr 1fr;gap:3px;width:140px}
.m-cell{border-radius:6px;padding:.45rem;text-align:center}
.m-cell .mcv{font-size:1.1rem;font-weight:800}
.m-cell .mcl{font-size:.6rem;font-weight:700}
.m-leyenda{font-size:.62rem;color:var(--sub);line-height:1.9;margin-left:.6rem}
.m-leyenda b{font-weight:700}

/* ── GINI / ENTROPIA ────────────────────────────────────────────────────── */
.ge-grid{display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-bottom:.8rem}
.ge-box{border-radius:8px;padding:.6rem;text-align:center;border:1px solid var(--border)}
.ge-box .gev{font-size:1.1rem;font-weight:800}
.ge-box .gel{font-size:.62rem;font-weight:700;color:var(--sub);text-transform:uppercase}
.ge-box .gef{font-size:.58rem;color:var(--muted);margin-top:.15rem;font-family:monospace}

/* ── ARBOL VISUAL ───────────────────────────────────────────────────────── */
.tree-viz{background:#F9F8F6;border-radius:8px;padding:.7rem;margin-bottom:.8rem}
.tree-node{text-align:center;font-size:.7rem;font-weight:700;padding:.25rem .5rem;
  border-radius:6px;display:inline-block}
.tree-row{display:flex;justify-content:center;gap:.4rem;margin:.3rem 0;flex-wrap:wrap}
.tree-connector{text-align:center;color:var(--muted);font-size:.65rem;margin:.1rem 0}

/* ── RF ARBOLES ─────────────────────────────────────────────────────────── */
.rf-arbol{display:flex;align-items:center;gap:.5rem;padding:.35rem .5rem;
  background:#F9F8F6;border-radius:6px;margin-bottom:.3rem;font-size:.7rem}
.rf-arbol i{font-size:.75rem}
.rf-vote{display:inline-flex;align-items:center;gap:.2rem;padding:.15rem .4rem;
  border-radius:10px;font-size:.62rem;font-weight:700;margin-left:auto}

/* ── BARRAS IMPORTANCIA ─────────────────────────────────────────────────── */
.fi-row{margin-bottom:.4rem}
.fi-label{display:flex;justify-content:space-between;font-size:.7rem;font-weight:700;
  color:var(--txt);margin-bottom:.2rem}
.fi-bar{height:7px;background:#F3F2F1;border-radius:4px;overflow:hidden}
.fi-fill{height:100%;border-radius:4px;transition:width 1s ease}

/* ── SVM ────────────────────────────────────────────────────────────────── */
.kernel-tags{display:flex;gap:.3rem;flex-wrap:wrap;margin-bottom:.6rem}
.k-tag{padding:.2rem .5rem;border-radius:10px;font-size:.67rem;font-weight:700;
  background:#F3F2F1;color:var(--sub);border:1px solid var(--border)}
.k-tag.active{color:#fff}
.vs-item{display:flex;justify-content:space-between;align-items:center;
  padding:.3rem 0;border-bottom:1px solid #F5F5F4;font-size:.68rem}
.vs-item:last-child{border:none}

/* ── REGRESION LINEAL ───────────────────────────────────────────────────── */
.reg-met-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.3rem;margin-bottom:.6rem}
.reg-met{background:#F9F8F6;border-radius:8px;padding:.5rem;text-align:center;border-bottom:2px solid transparent}
.reg-met .rv{font-size:1rem;font-weight:800}
.reg-met .rl{font-size:.6rem;color:var(--sub);font-weight:700;text-transform:uppercase}
.reg-tabla{width:100%;border-collapse:collapse;font-size:.7rem}
.reg-tabla th{padding:.35rem .5rem;text-align:left;font-weight:700;color:var(--sub);
  background:#F9F8F6;border-bottom:1px solid var(--border);font-size:.65rem}
.reg-tabla td{padding:.32rem .5rem;border-bottom:1px solid #F5F5F4;color:var(--txt)}
.interp-box{background:#F5F3FF;border:1px solid #DDD6FE;border-radius:8px;
  padding:.5rem .7rem;font-size:.72rem;color:#5B21B6;margin-bottom:.6rem;
  display:flex;align-items:center;gap:.4rem}

/* ── VENTAJAS/DESVENTAJAS ───────────────────────────────────────────────── */
.vd-grid{display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.7rem;
  padding-top:.7rem;border-top:1px solid var(--border)}
.vd-box{border-radius:8px;padding:.5rem .6rem}
.vd-box h5{font-size:.65rem;font-weight:800;margin:0 0 .3rem;text-transform:uppercase;
  letter-spacing:.4px;display:flex;align-items:center;gap:.3rem}
.vd-box ul{margin:0;padding-left:1rem;font-size:.67rem;color:var(--sub);line-height:1.7}

/* ── MEJOR BADGE EN CARD ────────────────────────────────────────────────── */
.mejor-tag{position:absolute;top:.7rem;right:.7rem;background:#FEF9C3;
  border:1px solid #FDE047;color:#854D0E;padding:.15rem .45rem;
  border-radius:8px;font-size:.62rem;font-weight:700;display:flex;align-items:center;gap:.2rem}

/* ── ANIMACION BARRAS ───────────────────────────────────────────────────── */
@keyframes fillBar{from{width:0}to{width:var(--w)}}
.fi-fill{animation:fillBar .8s ease forwards}
</style>

<div class="ia-wrap">

{{-- ═══ TOPBAR ═══════════════════════════════════════════════════════════ --}}
<div class="ia-top">
  <div>
    <h1>
      <i class="fas fa-brain" style="color:var(--c1)"></i>
      IA Medica Predictiva
    </h1>
    <p>Motor de 5 modelos ML &mdash; Regresion Logistica &middot; Arbol de Decision &middot; Random Forest &middot; SVM &middot; Regresion Lineal</p>
  </div>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
    @if(isset($resultados['mejor_modelo']))
    <span class="badge-mejor">
      <i class="fas fa-trophy"></i>
      Mejor: {{ $resultados['mejor_modelo'] }} &mdash; {{ $resultados['mejor_accuracy'] }}% accuracy
    </span>
    @endif
    <span class="badge-src">
      <i class="fas fa-database"></i>
      {{ $resultados['fuente_datos'] ?? 'BD Real' }}
    </span>
  </div>
</div>

{{-- ═══ PIPELINE ══════════════════════════════════════════════════════════ --}}
<div class="pipe-card">
  <div class="pipe-card-head">
    <i class="fas fa-project-diagram" style="color:#F97316"></i>
    <h2>Pipeline ML &mdash; 7 Pasos</h2>
    <span>Unidad 3 · Analisis Supervisado</span>
  </div>
  <div class="pipe-body">
    @php
    $pasos=[
      ['bg'=>'#DC2626','icon'=>'fas fa-database',        'label'=>'1. Datos',      'sub'=>($resultados['pipeline']['raw_total']).' registros'],
      ['bg'=>'#EA580C','icon'=>'fas fa-broom',           'label'=>'2. Limpieza',   'sub'=>($resultados['pipeline']['eliminados']).' outliers'],
      ['bg'=>'#F97316','icon'=>'fas fa-filter',          'label'=>'3. Variables',  'sub'=>'FC · SpO2 · Temp · Edad'],
      ['bg'=>'#EAB308','icon'=>'fas fa-cut',             'label'=>'4. Train/Test', 'sub'=>'80% / 20%'],
      ['bg'=>'#10B981','icon'=>'fas fa-cogs',            'label'=>'5. Modelos',    'sub'=>'5 algoritmos'],
      ['bg'=>'#6366F1','icon'=>'fas fa-chart-pie',       'label'=>'6. Evaluacion', 'sub'=>'Acc · F1 · R2'],
      ['bg'=>'#14B8A6','icon'=>'fas fa-rocket',          'label'=>'7. Produccion', 'sub'=>'Prediccion live'],
    ];
    @endphp
    <div class="pipe-steps">
      @foreach($pasos as $p)
      <div class="pipe-step">
        <div class="pipe-dot" style="background:{{ $p['bg'] }}">
          <i class="{{ $p['icon'] }}"></i>
        </div>
        <div class="pipe-step-label">{{ $p['label'] }}</div>
        <div class="pipe-step-sub">{{ $p['sub'] }}</div>
      </div>
      @endforeach
    </div>
    <div class="pipe-stats">
      <div class="pipe-stat">
        <div class="psv" style="color:var(--c1)">{{ $resultados['pipeline']['raw_total'] }}</div>
        <div class="psl">Registros crudos</div>
      </div>
      <div class="pipe-stat">
        <div class="psv" style="color:#EAB308">{{ $resultados['pipeline']['eliminados'] }}</div>
        <div class="psl">Outliers eliminados</div>
      </div>
      <div class="pipe-stat">
        <div class="psv" style="color:#10B981">{{ $resultados['pipeline']['n_train'] }}</div>
        <div class="psl">Set entrenamiento (80%)</div>
      </div>
      <div class="pipe-stat">
        <div class="psv" style="color:#6366F1">{{ $resultados['pipeline']['n_test'] }}</div>
        <div class="psl">Set prueba (20%)</div>
      </div>
    </div>
  </div>
</div>

{{-- ═══ DATASET ════════════════════════════════════════════════════════════ --}}
@if(!empty($resultados['dataset_muestra']))
<div class="ds-card">
  <div class="ds-head">
    <h2><i class="fas fa-table" style="color:#10B981"></i> Dataset &mdash; Muestra de Entrenamiento</h2>
    <span style="font-size:.72rem;color:var(--sub)">
      Variables X: FC, SpO2, Temp, Edad &nbsp;|&nbsp; Variable Y: Critico (0/1)
    </span>
  </div>
  <table class="ds-tabla">
    <thead>
      <tr>
        <th>Paciente</th><th>Edad</th><th>FC (lpm)</th>
        <th>SpO2 (%)</th><th>Temp (C)</th><th>Nivel Triage</th><th>Y Critico</th>
      </tr>
    </thead>
    <tbody>
      @foreach($resultados['dataset_muestra'] as $r)
      @php
      $nc=['Rojo'=>'background:#FEE2E2;color:#DC2626','Naranja'=>'background:#FFEDD5;color:#EA580C',
           'Amarillo'=>'background:#FEF9C3;color:#CA8A04','Verde'=>'background:#DCFCE7;color:#16A34A',
           'Azul'=>'background:#DBEAFE;color:#1D4ED8'];
      $col=$nc[$r['nivel']]?? 'background:#F3F2F1;color:#57534E';
      @endphp
      <tr>
        <td style="font-weight:600">{{ $r['nombre'] }}</td>
        <td>{{ $r['edad'] }} a</td>
        <td>
          <span style="font-weight:700;color:{{ $r['fc']>120?'#DC2626':($r['fc']>100?'#EA580C':'#16A34A') }}">
            {{ $r['fc'] }}
          </span>
        </td>
        <td>
          <span style="font-weight:700;color:{{ $r['spo2']<90?'#DC2626':($r['spo2']<95?'#EA580C':'#16A34A') }}">
            {{ $r['spo2'] }}
          </span>
        </td>
        <td>
          <span style="font-weight:700;color:{{ $r['temp']>39?'#DC2626':($r['temp']>38?'#EA580C':'#16A34A') }}">
            {{ $r['temp'] }}
          </span>
        </td>
        <td><span class="nivel-pill" style="{{ $col }}">{{ $r['nivel'] }}</span></td>
        <td>
          @if($r['critico'])
            <span class="nivel-pill" style="background:#FEE2E2;color:#DC2626">
              <i class="fas fa-exclamation-triangle" style="font-size:.6rem"></i> Critico (1)
            </span>
          @else
            <span class="nivel-pill" style="background:#DCFCE7;color:#16A34A">
              <i class="fas fa-check" style="font-size:.6rem"></i> No critico (0)
            </span>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

{{-- ═══ MODELOS ════════════════════════════════════════════════════════════ --}}
<div class="sec-head">
  <h2>
    <i class="fas fa-microchip" style="color:var(--c1)"></i>
    5 Modelos ML &mdash; Resultados con Datos Reales
  </h2>
  <span style="font-size:.72rem;color:var(--sub)">
    n = {{ $resultados['pipeline']['n_test'] }} pacientes en set de prueba
  </span>
</div>

<div class="modelos-grid">
@foreach($resultados['modelos'] as $m)
@php $esMejor = isset($resultados['mejor_modelo']) && $resultados['mejor_modelo'] === $m['nombre']; @endphp

<div class="mc">
  <div class="mc-stripe" style="background:{{ $m['color'] }}"></div>

  @if($esMejor)
  <div class="mejor-tag"><i class="fas fa-trophy"></i> MEJOR</div>
  @endif

  {{-- HEADER --}}
  <div class="mc-header">
    <h3 style="color:{{ $m['color'] }}">
      <i class="{{ $m['icono'] }}" style="margin-right:.3rem"></i>
      {{ $m['nombre'] }}
    </h3>
    <div class="mc-icon-wrap" style="background:{{ $m['color'] }}">
      <i class="{{ $m['icono'] }}"></i>
    </div>
  </div>

  <div class="mc-body">
    <p class="mc-desc">{{ $m['descripcion'] }}</p>

    {{-- ── FORMULA (Logistica) ── --}}
    @if(isset($m['formula']) && $m['tipo']==='clasificacion' && isset($m['z_formula']))
    <div class="fbox" style="border-color:{{ $m['color'] }}">
      <div class="fbox-title" style="color:{{ $m['color'] }}">
        <i class="fas fa-superscript"></i> Funcion Sigmoide
      </div>
      <div class="fbox-main" style="color:{{ $m['color'] }}">{{ $m['formula'] }}</div>
      <div class="fbox-sub">{{ $m['z_formula'] }}</div>
      <div class="fbox-sub" style="margin-top:.3rem;color:var(--muted)">
        Umbral: P &ge; 0.5 &rarr; Critico &nbsp;|&nbsp; P &lt; 0.5 &rarr; No critico
      </div>
    </div>
    {{-- Coeficientes --}}
    @if(isset($m['coeficientes']))
    <div style="margin-bottom:.8rem">
      <div style="font-size:.65rem;font-weight:700;color:var(--sub);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.3rem">
        <i class="fas fa-sliders-h" style="margin-right:.2rem"></i> Coeficientes aprendidos
      </div>
      <div class="coef-grid">
        @foreach($m['coeficientes'] as $k=>$v)
        <span class="coef-item">{{ $k }} = <b style="color:{{ $m['color'] }}">{{ $v }}</b></span>
        @endforeach
      </div>
    </div>
    @endif
    @endif

    {{-- ── GINI + ENTROPIA (Arbol) ── --}}
    @if(isset($m['gini']))
    <div class="ge-grid">
      <div class="ge-box" style="border-color:#D1FAE5">
        <div class="gev" style="color:#10B981">{{ $m['gini'] }}</div>
        <div class="gel">Indice Gini</div>
        <div class="gef">Gini = 1 - Sum(Pi^2)</div>
        <div class="gef" style="color:var(--muted)">{{ $m['formula_gini'] }}</div>
      </div>
      <div class="ge-box" style="border-color:#D1FAE5">
        <div class="gev" style="color:#10B981">{{ $m['entropia'] }}</div>
        <div class="gel">Entropia</div>
        <div class="gef">H = -Sum Pi*log2(Pi)</div>
        <div class="gef" style="color:var(--muted)">Desorden del conjunto</div>
      </div>
    </div>
    {{-- Arbol visual --}}
    <div class="tree-viz">
      <div class="tree-row">
        <span class="tree-node" style="background:#10B981;color:#fff">
          <i class="fas fa-circle" style="font-size:.45rem"></i>
          Raiz: {{ $m['nodo_raiz'] }}
        </span>
      </div>
      <div class="tree-connector">
        <i class="fas fa-long-arrow-alt-down"></i> Si / No
      </div>
      <div class="tree-row">
        @foreach($m['nodos_internos'] as $ni)
        <span class="tree-node" style="background:#D1FAE5;color:#065F46;border:1px solid #6EE7B7">
          {{ $ni }}
        </span>
        @endforeach
      </div>
      <div class="tree-connector">
        <i class="fas fa-long-arrow-alt-down"></i> Hojas
      </div>
      <div class="tree-row">
        @foreach($m['hojas'] as $h)
        <span class="tree-node" style="background:#F3F2F1;color:#57534E;font-size:.62rem">
          {{ $h }}
        </span>
        @endforeach
      </div>
    </div>
    @endif

    {{-- ── RANDOM FOREST: importancia + arboles ── --}}
    @if(isset($m['importancia']))
    <div style="margin-bottom:.7rem">
      <div style="font-size:.65rem;font-weight:700;color:var(--sub);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.4rem">
        <i class="fas fa-sort-amount-down" style="margin-right:.2rem"></i> Feature Importance
      </div>
      @foreach($m['importancia'] as $feat=>$pct)
      <div class="fi-row">
        <div class="fi-label"><span>{{ $feat }}</span><span style="color:{{ $m['color'] }}">{{ $pct }}%</span></div>
        <div class="fi-bar">
          <div class="fi-fill" style="--w:{{ $pct }}%;background:{{ $m['color'] }}"></div>
        </div>
      </div>
      @endforeach
    </div>
    @if(isset($m['arboles']))
    <div style="margin-bottom:.7rem">
      <div style="font-size:.65rem;font-weight:700;color:var(--sub);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.4rem">
        <i class="fas fa-vote-yea" style="margin-right:.2rem"></i> {{ $m['n_arboles'] }} Arboles Votando (mayoria)
      </div>
      @foreach($m['arboles'] as $i=>$a)
      <div class="rf-arbol">
        <i class="fas fa-tree" style="color:{{ $m['color'] }}"></i>
        <span style="font-weight:700;font-size:.7rem">{{ $a['nombre'] }}</span>
        <span class="rf-vote" style="background:#FEF3C7;color:#92400E">
          SpO2&lt;{{ $a['spo2_th'] }} | FC&gt;{{ $a['fc_th'] }} | T&gt;{{ $a['temp_th'] }}
        </span>
      </div>
      @endforeach
      <div style="font-size:.65rem;color:var(--sub);margin-top:.3rem;padding:.3rem .5rem;background:#F9F8F6;border-radius:6px">
        <i class="fas fa-info-circle" style="margin-right:.2rem;color:#F59E0B"></i>
        Decision final: &ge;2 votos &rarr; Critico
      </div>
    </div>
    @endif
    @endif

    {{-- ── SVM: hiperplano + kernels + vectores ── --}}
    @if(isset($m['hiperplano']))
    <div class="fbox" style="border-color:{{ $m['color'] }}">
      <div class="fbox-title" style="color:{{ $m['color'] }}">
        <i class="fas fa-minus"></i> Hiperplano Optimo
      </div>
      <div class="fbox-main" style="color:{{ $m['color'] }}">{{ $m['hiperplano'] }}</div>
      <div class="fbox-sub">w1={{ $m['w1'] }} (FC) &nbsp; w2={{ $m['w2'] }} (SpO2) &nbsp; b={{ $m['b_svm'] }}</div>
      <div class="fbox-sub" style="margin-top:.2rem">
        Decision &gt; 0 &rarr; ALTO RIESGO &nbsp;|&nbsp; Decision &le; 0 &rarr; BAJO RIESGO
      </div>
    </div>
    <div style="margin-bottom:.5rem">
      <div style="font-size:.65rem;font-weight:700;color:var(--sub);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.3rem">
        <i class="fas fa-layer-group" style="margin-right:.2rem"></i> Tipos de Kernel
      </div>
      <div class="kernel-tags">
        @foreach($m['tipos_kernel'] as $k)
        <span class="k-tag {{ $k===$m['kernel']?'active':'' }}"
          style="{{ $k===$m['kernel']?'background:'.$m['color'].';border-color:'.$m['color'] :'' }}">
          @if($k===$m['kernel'])<i class="fas fa-check" style="font-size:.55rem"></i>@endif
          {{ $k }}
        </span>
        @endforeach
      </div>
    </div>
    @if(!empty($m['vectores_soporte']))
    <div style="margin-bottom:.7rem">
      <div style="font-size:.65rem;font-weight:700;color:var(--sub);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.3rem">
        <i class="fas fa-map-marker-alt" style="margin-right:.2rem"></i> Vectores de Soporte
      </div>
      @foreach($m['vectores_soporte'] as $vs)
      <div class="vs-item">
        <span style="font-weight:600;color:var(--txt)">{{ $vs['nombre'] }}</span>
        <span style="color:var(--sub)">FC:{{ $vs['fc'] }} SpO2:{{ $vs['spo2'] }}</span>
        <span style="font-family:monospace;font-size:.65rem;
          color:{{ $vs['decision']>0?'#DC2626':'#16A34A' }};font-weight:700">
          d={{ $vs['decision'] }}
        </span>
      </div>
      @endforeach
    </div>
    @endif
    @endif

    {{-- ── FORMULA REGRESION LINEAL ── --}}
    @if($m['tipo']==='regresion' && isset($m['formula']))
    <div class="fbox" style="border-color:{{ $m['color'] }}">
      <div class="fbox-title" style="color:{{ $m['color'] }}">
        <i class="fas fa-chart-line"></i> Regresion Lineal Simple
      </div>
      <div class="fbox-main" style="color:{{ $m['color'] }}">y = mx + b</div>
      <div class="fbox-sub">{{ $m['formula'] }}</div>
      <div class="fbox-sub">Pendiente m = {{ $m['pendiente'] }} &nbsp;|&nbsp; Intercepto b = {{ $m['intercepto'] }}</div>
    </div>
    @if(isset($m['interpretacion']))
    <div class="interp-box">
      <i class="fas fa-lightbulb"></i>
      {{ $m['interpretacion'] }}
    </div>
    @endif
    @endif

    {{-- ── METRICAS CLASIFICACION ── --}}
    @if($m['tipo']==='clasificacion')
    <div class="met-grid">
      <div class="met-box" style="border-color:{{ $m['color'] }}">
        <div class="mv" style="color:{{ $m['color'] }}">{{ $m['accuracy'] }}%</div>
        <div class="ml">Accuracy</div>
      </div>
      <div class="met-box" style="border-color:{{ $m['color'] }}">
        <div class="mv" style="color:{{ $m['color'] }}">{{ $m['precision'] }}%</div>
        <div class="ml">Precision</div>
      </div>
      <div class="met-box" style="border-color:{{ $m['color'] }}">
        <div class="mv" style="color:{{ $m['color'] }}">{{ $m['recall'] }}%</div>
        <div class="ml">Recall</div>
      </div>
      <div class="met-box" style="border-color:{{ $m['color'] }}">
        <div class="mv" style="color:{{ $m['color'] }}">{{ $m['f1'] }}%</div>
        <div class="ml">F1 Score</div>
      </div>
    </div>
    <div class="met-formulas">
      <div class="mf-row">
        <span>Accuracy</span> = (TP+TN)/Total
        &nbsp;&nbsp;
        <span>Precision</span> = TP/(TP+FP)
      </div>
      <div class="mf-row">
        <span>Recall</span> = TP/(TP+FN)
        &nbsp;&nbsp;
        <span>F1</span> = 2*(P*R)/(P+R)
      </div>
    </div>
    {{-- MATRIZ --}}
    @if(isset($m['matriz']))
    <div class="matriz-sec">
      <h4><i class="fas fa-th"></i> Matriz de Confusion</h4>
      <div style="display:flex;align-items:flex-start;gap:.6rem">
        <div>
          <div style="display:flex;gap:2px;margin-bottom:2px">
            <div style="width:65px;text-align:center;font-size:.58rem;color:var(--muted);font-weight:700">Pred +</div>
            <div style="width:65px;text-align:center;font-size:.58rem;color:var(--muted);font-weight:700">Pred -</div>
          </div>
          <div class="m-grid">
            <div class="m-cell" style="background:#DCFCE7">
              <div class="mcv" style="color:#16A34A">{{ $m['matriz']['TP'] }}</div>
              <div class="mcl" style="color:#16A34A">TP</div>
            </div>
            <div class="m-cell" style="background:#FEE2E2">
              <div class="mcv" style="color:#DC2626">{{ $m['matriz']['FN'] }}</div>
              <div class="mcl" style="color:#DC2626">FN</div>
            </div>
            <div class="m-cell" style="background:#FEF9C3">
              <div class="mcv" style="color:#CA8A04">{{ $m['matriz']['FP'] }}</div>
              <div class="mcl" style="color:#CA8A04">FP</div>
            </div>
            <div class="m-cell" style="background:#DCFCE7">
              <div class="mcv" style="color:#16A34A">{{ $m['matriz']['TN'] }}</div>
              <div class="mcl" style="color:#16A34A">TN</div>
            </div>
          </div>
        </div>
        <div class="m-leyenda">
          <div><b style="color:#16A34A">TP</b> Verdadero Positivo</div>
          <div><b style="color:#16A34A">TN</b> Verdadero Negativo</div>
          <div><b style="color:#CA8A04">FP</b> Falso Positivo</div>
          <div><b style="color:#DC2626">FN</b> Falso Negativo</div>
          <div style="margin-top:.2rem;color:var(--muted)">n={{ $m['matriz']['total'] }}</div>
        </div>
      </div>
    </div>
    @endif
    @endif

    {{-- ── METRICAS REGRESION ── --}}
    @if($m['tipo']==='regresion')
    <div class="reg-met-grid">
      <div class="reg-met" style="border-color:{{ $m['color'] }}">
        <div class="rv" style="color:{{ $m['color'] }}">{{ $m['mse'] }}</div>
        <div class="rl">MSE</div>
      </div>
      <div class="reg-met" style="border-color:{{ $m['color'] }}">
        <div class="rv" style="color:{{ $m['color'] }}">{{ $m['rmse'] }}</div>
        <div class="rl">RMSE</div>
      </div>
      <div class="reg-met" style="border-color:{{ $m['color'] }}">
        <div class="rv" style="color:{{ $m['color'] }}">{{ $m['mae'] }}</div>
        <div class="rl">MAE</div>
      </div>
      <div class="reg-met" style="border-color:{{ $m['color'] }}">
        <div class="rv" style="color:{{ $m['color'] }}">{{ $m['r2'] }}</div>
        <div class="rl">R2</div>
      </div>
    </div>
    <div class="met-formulas">
      <div class="mf-row">
        <span>MSE</span> = Sum(y-yh)^2 / n
        &nbsp;&nbsp;
        <span>RMSE</span> = sqrt(MSE)
      </div>
      <div class="mf-row">
        <span>MAE</span> = Sum|y-yh| / n
        &nbsp;&nbsp;
        <span>R2</span> = 1 - SSres/SStot
      </div>
    </div>
    @if(!empty($m['tabla']))
    <div style="margin-bottom:.6rem">
      <div style="font-size:.65rem;font-weight:700;color:var(--sub);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.3rem">
        <i class="fas fa-table" style="margin-right:.2rem"></i> Predicciones vs Real
      </div>
      <table class="reg-tabla">
        <thead>
          <tr><th>Paciente</th><th>FC</th><th>SpO2 Real</th><th>SpO2 Pred</th><th>Error |y-yh|</th></tr>
        </thead>
        <tbody>
          @foreach($m['tabla'] as $row)
          <tr>
            <td style="font-weight:600">{{ $row['nombre'] }}</td>
            <td>{{ $row['fc'] }}</td>
            <td>{{ $row['spo2_real'] }}</td>
            <td style="color:{{ $m['color'] }};font-weight:700">{{ $row['spo2_pred'] }}</td>
            <td>
              <span style="font-weight:700;color:{{ $row['error']>3?'#DC2626':'#16A34A' }}">
                {{ $row['error'] }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
    @endif

    {{-- ── VENTAJAS / DESVENTAJAS ── --}}
    <div class="vd-grid">
      <div class="vd-box" style="background:#F0FDF4;border:1px solid #BBF7D0">
        <h5 style="color:#16A34A"><i class="fas fa-check-circle"></i> Ventajas</h5>
        <ul>@foreach($m['ventajas'] as $v)<li>{{ $v }}</li>@endforeach</ul>
      </div>
      <div class="vd-box" style="background:#FEF2F2;border:1px solid #FECACA">
        <h5 style="color:#DC2626"><i class="fas fa-times-circle"></i> Desventajas</h5>
        <ul>@foreach($m['desventajas'] as $d)<li>{{ $d }}</li>@endforeach</ul>
      </div>
    </div>

  </div>{{-- mc-body --}}
</div>{{-- mc --}}
@endforeach
</div>{{-- modelos-grid --}}

</div>{{-- ia-wrap --}}
@endsection
