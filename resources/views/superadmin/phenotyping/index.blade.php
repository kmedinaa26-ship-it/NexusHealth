@extends('superadmin.layout')

@section('title', 'Fenotipado Clinico')

@section('content')
<style>
    .ph-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1rem; }
    .ph-kpi { text-align: center; padding: 0.75rem; border-radius: 10px; border: 1px solid #FED7AA; background: white; }
    .ph-kpi .label { font-size: 0.6rem; color: #78716C; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .ph-kpi .value { font-size: 1.2rem; font-weight: 800; margin-top: 0.15rem; }
    .ph-kpi i { font-size: 0.9rem; margin-bottom: 0.15rem; display: block; }
    .ph-card { background: white; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #FED7AA; margin-bottom: 1rem; }
    .ph-section { font-size: 0.8rem; font-weight: 800; color: #1E1A17; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem; }
    .ph-section i { color: #E85D3A; }
    .ph-vars-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
    .ph-var-item { background: #FFF7ED; border: 1px solid #FED7AA; border-radius: 10px; padding: 0.6rem 0.8rem; display: flex; align-items: center; gap: 0.6rem; }
    .ph-var-num { width: 26px; height: 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.62rem; font-weight: 800; color: white; flex-shrink: 0; }
    .ph-var-text { font-size: 0.75rem; font-weight: 700; color: #1E1A17; }
    .ph-tab-bar { display: flex; gap: 0.25rem; margin-bottom: 1rem; border-bottom: 2px solid #FED7AA; }
    .ph-tab { padding: 0.4rem 0.8rem; font-size: 0.7rem; font-weight: 700; color: #78716C; cursor: pointer; border: none; background: none; border-bottom: 2px solid transparent; margin-bottom: -2px; }
    .ph-tab.active { color: #E85D3A; border-bottom-color: #E85D3A; }
    .ph-tab:hover { color: #C2410C; }
    .ph-tab-panel { display: none; }
    .ph-tab-panel.active { display: block; }
    .ph-select { padding: 0.4rem 0.6rem; border: 1px solid #FED7AA; border-radius: 8px; font-size: 0.8rem; font-weight: 700; color: #1E1A17; }
    .ph-select:focus { outline: none; border-color: #E85D3A; box-shadow: 0 0 0 3px rgba(232,93,58,0.1); }
    .btn-ph { padding: 0.35rem 0.9rem; border-radius: 8px; font-size: 0.7rem; font-weight: 700; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; background: #E85D3A; color: white; transition: 0.2s; }
    .btn-ph:hover { background: #C2410C; }
    .btn-ph:disabled { opacity: 0.4; cursor: not-allowed; }
    .btn-ph-blue { background: #3B82F6; }
    .btn-ph-blue:hover { background: #2563EB; }
    .btn-ph-green { background: #059669; }
    .btn-ph-green:hover { background: #047857; }
    .ph-score-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    .ph-score-card { border-radius: 12px; padding: 1rem; text-align: center; border: 1px solid #FED7AA; background: white; }
    .ph-score-card .label { font-size: 0.6rem; color: #78716C; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .ph-score-card .value { font-size: 1.8rem; font-weight: 800; margin-top: 0.2rem; }
    .ph-score-card .sub { font-size: 0.6rem; color: #A8A29E; margin-top: 0.15rem; }
    .ph-chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; }
    .ph-phenotype-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    .ph-phenotype { border-radius: 12px; padding: 1rem; border: 2px solid; background: white; }
    .ph-phenotype .name { font-size: 0.75rem; font-weight: 800; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.4rem; }
    .ph-phenotype .dot { width: 10px; height: 10px; border-radius: 50%; }
    .ph-phenotype .row { display: flex; justify-content: space-between; font-size: 0.72rem; padding: 0.2rem 0; }
    .ph-phenotype .row .k { color: #78716C; }
    .ph-phenotype .row .v { font-weight: 700; color: #1E1A17; }
    .ph-phenotype .tri { margin-top: 0.4rem; padding-top: 0.4rem; border-top: 1px solid #F5F5F4; font-size: 0.65rem; color: #78716C; }
    .ph-tbl { width: 100%; border-collapse: collapse; font-size: 0.72rem; }
    .ph-tbl th { background: #E85D3A; color: white; padding: 0.35rem 0.5rem; text-align: left; font-size: 0.6rem; text-transform: uppercase; }
    .ph-tbl th.c { text-align: center; }
    .ph-tbl td { padding: 0.3rem 0.5rem; border-bottom: 1px solid #FFF7ED; }
    .ph-tbl tr:hover td { background: #FFF7ED; }
    .ph-tbl td.c { text-align: center; }
    .badge { padding: 0.1rem 0.45rem; border-radius: 20px; font-size: 0.58rem; font-weight: 700; display: inline-block; }
    .badge-rojo { background: #FEE2E2; color: #991B1B; }
    .badge-amarillo { background: #FEF3C7; color: #92400E; }
    .badge-verde { background: #D1FAE5; color: #065F46; }
    .badge-negro { background: #1C1917; color: #F5F5F4; }
    .ph-pca-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem; }
    .ph-pca-card { background: white; border-radius: 12px; padding: 1rem; border: 1px solid #FED7AA; border-top: 3px solid; }
    .ph-pca-card .pc-head { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.6rem; }
    .ph-pca-card .pc-badge { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 0.68rem; }
    .ph-pca-card .pc-name { font-weight: 800; font-size: 0.78rem; color: #1E1A17; }
    .ph-pca-card .pc-pct { font-size: 0.65rem; color: #78716C; }
    .ph-ltbl { width: 100%; border-collapse: collapse; font-size: 0.72rem; }
    .ph-ltbl th { background: #E85D3A; color: white; padding: 0.35rem 0.5rem; text-align: center; font-size: 0.6rem; text-transform: uppercase; }
    .ph-ltbl th:first-child { text-align: left; }
    .ph-ltbl td { padding: 0.3rem 0.5rem; text-align: center; border-bottom: 1px solid #FFF7ED; }
    .ph-ltbl td:first-child { text-align: left; font-weight: 700; }
    .ph-ltbl tr:hover td { background: #FFF7ED; }
    .ph-loading { display: none; align-items: center; gap: 0.4rem; font-size: 0.72rem; color: #78716C; font-weight: 600; }
    .ph-loading.show { display: inline-flex; }
    .ph-loading i { animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .ph-wrap { background: white; border-radius: 12px; border: 1px solid #FED7AA; overflow: hidden; margin-bottom: 1rem; }
    .ph-wrap-head { padding: 0.6rem 1rem; background: #FFF7ED; border-bottom: 1px solid #FED7AA; }
    .ph-wrap-head h3 { margin: 0; font-size: 0.8rem; font-weight: 800; color: #1E1A17; display: flex; align-items: center; gap: 0.35rem; }
    .ph-wrap-head h3 i { color: #E85D3A; }
    .ph-scroll { max-height: 400px; overflow-y: auto; }
    .flow-box { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem; border: 1px solid #D1FAE5; background: #F0FDF4; border-radius: 12px; padding: 0.75rem 1rem; }
    .flow-box .flow-icon { display: flex; align-items: center; gap: 0.5rem; }
    .flow-box .flow-icon i { font-size: 1.2rem; color: #059669; }
    .flow-box .flow-text .ft-title { font-size: 0.78rem; font-weight: 800; color: #1E1A17; }
    .flow-box .flow-text .ft-sub { font-size: 0.65rem; color: #78716C; }
    .flow-metrics { display: flex; align-items: center; gap: 1.5rem; }
    .flow-metric { text-align: center; }
    .flow-metric .fm-num { font-size: 1.3rem; font-weight: 800; color: #059669; line-height: 1; }
    .flow-metric .fm-label { font-size: 0.6rem; color: #78716C; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
</style>

<!-- KPIs -->
<div class="ph-grid">
    <div class="ph-kpi" style="border-left:3px solid #E85D3A;"><i class="fas fa-users" style="color:#E85D3A;"></i><div class="label">Pacientes</div><div class="value" style="color:#E85D3A;">{{ $totalPacientes }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid #3B82F6;"><i class="fas fa-procedures" style="color:#3B82F6;"></i><div class="label">Alta Fenotipado</div><div class="value" style="color:#3B82F6;">{{ $totalAdmisiones }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid #DC2626;"><i class="fas fa-heartbeat" style="color:#DC2626;"></i><div class="label">Signos Vitales</div><div class="value" style="color:#DC2626;">{{ $totalVitales }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid #059669;"><i class="fas fa-pills" style="color:#059669;"></i><div class="label">Recetas</div><div class="value" style="color:#059669;">{{ $totalRecetas }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid #F59E0B;"><i class="fas fa-flask" style="color:#F59E0B;"></i><div class="label">Estudios Lab</div><div class="value" style="color:#F59E0B;">{{ $totalLabs }}</div></div>
    <div class="ph-kpi" style="border-left:3px solid #7C3AED;"><i class="fas fa-x-ray" style="color:#7C3AED;"></i><div class="label">Imagenes</div><div class="value" style="color:#7C3AED;">{{ $totalImg }}</div></div>
</div>

<!-- FLUJO DE DATOS EN TIEMPO REAL -->
<div class="flow-box">
    <div class="flow-icon"><i class="fas fa-sync-alt"></i></div>
    <div class="flow-text">
        <div class="ft-title">Flujo de Datos en Tiempo Real</div>
        <div class="ft-sub">Los datos se alimentan de admisiones reales dadas de alta. El fenotipado siempre lee las tablas vivas del hospital.</div>
    </div>
    <div class="flow-metrics">
        <div class="flow-metric"><div class="fm-num">{{ $newAdmisions }}</div><div class="fm-label">Nuevas altas</div></div>
        <div class="flow-metric"><div class="fm-num" style="color:#3B82F6;">{{ $newVitals }}</div><div class="fm-label">Nuevos vitales</div></div>
        @if($lastRun)
        <div style="font-size:0.6rem;color:#A8A29E;">Ultima ejecucion: {{ $lastRun }}</div>
        @endif
    </div>
</div>

<!-- VECTOR CLINICO -->
<div class="ph-card">
    <div class="ph-section"><i class="fas fa-dna"></i> Vector Clinico — 8 Variables</div>
    <div class="ph-vars-grid">
        <div class="ph-var-item"><div class="ph-var-num" style="background:#E85D3A;">01</div><div class="ph-var-text">Edad</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#DC2626;">02</div><div class="ph-var-text">Severidad Entrada</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#3B82F6;">03</div><div class="ph-var-text">Horas Estancia</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#EC4899;">04</div><div class="ph-var-text">Variabilidad FC</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#F59E0B;">05</div><div class="ph-var-text">Variabilidad Temp</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#059669;">06</div><div class="ph-var-text">Medicamentos Distintos</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#7C3AED;">07</div><div class="ph-var-text">Intensidad Monitoreo</div></div>
        <div class="ph-var-item"><div class="ph-var-num" style="background:#2563EB;">08</div><div class="ph-var-text">Carga Diagnostica</div></div>
    </div>
</div>

<!-- TABS -->
<div class="ph-tab-bar">
    <button class="ph-tab active" onclick="switchPhTab('kmeans')"><i class="fas fa-project-diagram" style="margin-right:0.3rem;"></i>K-Means</button>
    <button class="ph-tab" onclick="switchPhTab('pca')"><i class="fas fa-compress-arrows-alt" style="margin-right:0.3rem;"></i>PCA</button>
</div>

<!-- ====== K-MEANS ====== -->
<div id="ph-tab-kmeans" class="ph-tab-panel active">
    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
        <span style="font-size:0.75rem;font-weight:700;color:#57534E;">Clusters (K):</span>
        <select id="k-value" class="ph-select">
            <option value="2">2</option><option value="3">3</option><option value="4" selected>4</option><option value="5">5</option><option value="6">6</option>
        </select>
        <button id="btn-km" class="btn-ph" onclick="runKm()"><i class="fas fa-bolt"></i> Ejecutar K-Means</button>
        <div class="ph-loading" id="km-load"><i class="fas fa-spinner"></i> Procesando...</div>
    </div>

    <div id="km-out" style="display:none;">
        <div class="ph-score-grid">
            <div class="ph-score-card"><div class="label">Silhouette Score</div><div class="value" style="color:#E85D3A;" id="km-sil">—</div><div class="sub">-1 a 1 (mayor = mejor)</div></div>
            <div class="ph-score-card"><div class="label">Inercia (SSE)</div><div class="value" style="color:#3B82F6;" id="km-ine">—</div><div class="sub">Suma distancias al centroide</div></div>
            <div class="ph-score-card"><div class="label">Iteraciones</div><div class="value" style="color:#059669;" id="km-iter">—</div><div class="sub">Para converger</div></div>
        </div>
        <div class="ph-chart-grid">
            <div class="ph-card"><div class="ph-section"><i class="fas fa-chart-line"></i> Metodo del Codo</div><canvas id="ch-elbow" height="220"></canvas></div>
            <div class="ph-card"><div class="ph-section"><i class="fas fa-chart-bar"></i> Silhouette por K</div><canvas id="ch-sil" height="220"></canvas></div>
        </div>
        <div id="ph-cards" class="ph-phenotype-grid"></div>
        <div class="ph-card"><div class="ph-section"><i class="fas fa-braille"></i> Distribucion de Clusters</div><canvas id="ch-scatter" height="280"></canvas></div>
        <div class="ph-wrap">
            <div class="ph-wrap-head"><h3><i class="fas fa-table"></i> Detalle por Paciente</h3></div>
            <div class="ph-scroll">
                <table class="ph-tbl">
                    <thead><tr><th>Paciente</th><th class="c">Edad</th><th class="c">Triage</th><th class="c">Horas</th><th class="c">Var FC</th><th class="c">Meds</th><th class="c">Fenotipo</th></tr></thead>
                    <tbody id="km-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ====== PCA ====== -->
<div id="ph-tab-pca" class="ph-tab-panel">
    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
        <span style="font-size:0.75rem;font-weight:700;color:#57534E;">Componentes:</span>
        <select id="pca-comp" class="ph-select">
            <option value="2">2</option><option value="3" selected>3</option><option value="4">4</option>
        </select>
        <button id="btn-pca" class="btn-ph btn-ph-blue" onclick="runPca()"><i class="fas fa-bolt"></i> Ejecutar PCA</button>
        <div class="ph-loading" id="pca-load"><i class="fas fa-spinner"></i> Procesando...</div>
    </div>

    <div id="pca-out" style="display:none;">
        <div class="ph-card"><div class="ph-section"><i class="fas fa-chart-bar"></i> Varianza Explicada</div><canvas id="ch-var" height="180"></canvas></div>
        <div id="pca-cards" class="ph-pca-grid"></div>
        <div class="ph-wrap">
            <div class="ph-wrap-head"><h3><i class="fas fa-th"></i> Matriz de Loadings</h3></div>
            <div style="overflow-x:auto;padding:0.5rem;"><table class="ph-ltbl" id="ld-tbl"></table></div>
        </div>
        <div class="ph-card"><div class="ph-section"><i class="fas fa-braille"></i> Proyeccion de Pacientes</div><canvas id="ch-pca-sc" height="320"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const C=['#DC2626','#059669','#F59E0B','#3B82F6','#7C3AED','#EC4899'];
const CB=['rgba(220,38,38,0.15)','rgba(5,150,105,0.15)','rgba(245,158,11,0.15)','rgba(59,130,246,0.15)','rgba(124,58,237,0.15)','rgba(236,72,153,0.15)'];
const VN=['Edad','Severidad','Horas Est.','Var. FC','Var. Temp','Medicamentos','Monitoreo','Carga Diag.'];
let ch={};
function switchPhTab(t){
    document.querySelectorAll('.ph-tab').forEach(e=>e.classList.remove('active'));
    document.querySelectorAll('.ph-tab-panel').forEach(e=>e.classList.remove('active'));
    document.getElementById('ph-tab-'+t).classList.add('active');
    event.target.closest('.ph-tab').classList.add('active');
}
function dc(id){if(ch[id]){ch[id].destroy();delete ch[id];}}
function tBadge(l){return 'badge-'+(l||'verde');}

async function runKm(){
    const k=document.getElementById('k-value').value,btn=document.getElementById('btn-km'),ld=document.getElementById('km-load');
    btn.disabled=true;ld.classList.add('show');
    try{const r=await fetch('/superadmin/phenotyping/kmeans?k='+k);const d=await r.json();if(d.error){alert(d.error);return;}renderKM(d);}
    catch(e){alert('Error: '+e.message);}finally{btn.disabled=false;ld.classList.remove('show');}
}

function renderKM(d){
    document.getElementById('km-out').style.display='block';
    document.getElementById('km-sil').textContent=d.silhouette;
    document.getElementById('km-ine').textContent=d.inertia.toLocaleString();
    document.getElementById('km-iter').textContent=d.n_iter;
    dc('elbow');ch.elbow=new Chart(document.getElementById('ch-elbow'),{type:'line',data:{labels:d.elbow.map(e=>'K='+e.k),datasets:[{data:d.elbow.map(e=>e.inertia),borderColor:'#7C3AED',backgroundColor:'rgba(124,58,237,0.08)',fill:true,tension:0.4,pointRadius:6,pointBackgroundColor:'#7C3AED',borderWidth:2.5}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{grid:{color:'#FED7AA'},ticks:{color:'#78716C',font:{weight:'bold'}}},x:{grid:{display:false},ticks:{color:'#78716C',font:{weight:'bold'}}}}}});
    dc('sil');ch.sil=new Chart(document.getElementById('ch-sil'),{type:'bar',data:{labels:d.elbow.map(e=>'K='+e.k),datasets:[{data:d.elbow.map(e=>e.silhouette),backgroundColor:d.elbow.map(e=>e.silhouette>0.5?'#059669':(e.silhouette>0.25?'#F59E0B':'#DC2626')),borderRadius:8,borderSkipped:false}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{min:-0.1,max:1,grid:{color:'#FED7AA'},ticks:{color:'#78716C',font:{weight:'bold'}}},x:{grid:{display:false},ticks:{color:'#78716C',font:{weight:'bold'}}}}}});
    document.getElementById('ph-cards').innerHTML=d.cluster_stats.map((s,i)=>`
        <div class="ph-phenotype" style="border-color:${C[i]};">
            <div class="name"><div class="dot" style="background:${C[i]};"></div><span style="color:${C[i]};">${s.name}</span></div>
            <div class="row"><span class="k">Pacientes</span><span class="v">${s.n} (${s.pct}%)</span></div>
            <div class="row"><span class="k">Edad prom.</span><span class="v">${s.avg_age} anos</span></div>
            <div class="row"><span class="k">Estancia</span><span class="v">${s.avg_days} dias</span></div>
            <div class="row"><span class="k">Rango</span><span class="v">${Math.round(s.min_hours/24)}-${Math.round(s.max_hours/24)}d</span></div>
            <div class="tri">Triage: ${Object.entries(s.triage_dist).map(([k,v])=>k+': '+v).join(', ')}</div>
        </div>`).join('');
    dc('scatter');const sds=[];for(let c=0;c<d.k;c++){sds.push({label:d.cluster_names[c],data:d.patients.filter(p=>p.cluster===c).map(p=>({x:p.vector[2],y:p.vector[3]})),backgroundColor:CB[c],borderColor:C[c],borderWidth:2,pointRadius:5,pointHoverRadius:8});}
    ch.scatter=new Chart(document.getElementById('ch-scatter'),{type:'scatter',data:{datasets:sds},options:{responsive:true,plugins:{legend:{position:'top',labels:{font:{weight:'bold',size:11},usePointStyle:true,pointStyle:'circle',color:'#57534E'}}},scales:{x:{title:{display:true,text:'Horas de Estancia',color:'#57534E,font:{weight:'bold'}},grid:{color:'#FED7AA'},ticks:{color:'#78716C'}},y:{title:{display:true,text:'Variabilidad FC (DE)',color:'#57534E,font:{weight:'bold'}},grid:{color:'#FED7AA'},ticks:{color:'#78716C'}}}}});
    document.getElementById('km-tbody').innerHTML=d.patients.sort((a,b)=>a.cluster-b.cluster).map(p=>`<tr>
        <td style="font-weight:700;">${p.patient_name}</td>
        <td class="c">${p.age}</td>
        <td class="c"><span class="badge ${tBadge(p.triage_level)}">${p.triage_level}</span></td>
        <td class="c" style="font-family:monospace;color:#78716C;">${p.hours_stay}h</td>
        <td class="c" style="font-family:monospace;">${p.vector[3].toFixed(1)}</td>
        <td class="c">${p.vector[5]}</td>
        <td class="c"><span class="badge" style="background:${CB[p.cluster]};color:${C[p.cluster]};">${p.cluster_name}</span></td>
    </tr>`).join('');
}

async function runPca(){
    const comp=document.getElementById('pca-comp').value,btn=document.getElementById('btn-pca'),ld=document.getElementById('pca-load');
    btn.disabled=true;ld.classList.add('show');
    try{const r=await fetch('/superadmin/phenotyping/pca?components='+comp);const d=await r.json();if(d.error){alert(d.error);return;}renderPCA(d);}
    catch(e){alert('Error: '+e.message);}finally{btn.disabled=false;ld.classList.remove('show');}
}

function renderPCA(d){
    document.getElementById('pca-out').style.display='block';
    const labels=d.explained_variance_pct.map((v,i)=>'PC'+(i+1));
    dc('var');ch.var=new Chart(document.getElementById('ch-var'),{type:'bar',data:{labels,datasets:[
        {label:'Varianza %',data:d.explained_variance_pct,backgroundColor:C.slice(0,labels.length),borderRadius:8,borderSkipped:false},
        {label:'Acumulado %',data:d.cumulative_variance_pct,type:'line',borderColor:'#1E1A17',pointRadius:6,pointBackgroundColor:'#1E1A17',borderWidth:2,tension:0.15}
    ]},options:{responsive:true,scales:{y:{max:100,ticks:{callback:v=>v+'%',color:'#78716C',font:{weight:'bold'}},grid:{color:'#FED7AA'}},x:{grid:{display:false},ticks:{color:'#78716C',font:{weight:'bold'}}}}}});
    document.getElementById('pca-cards').innerHTML=d.component_names.map((name,i)=>`
        <div class="ph-pca-card" style="border-top-color:${C[i]};">
            <div class="pc-head"><div class="pc-badge" style="background:${C[i]};">PC${i+1}</div><div><div class="pc-name">${name}</div><div class="pc-pct">${d.explained_variance_pct[i]}% varianza</div></div></div>
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
                ${d.loadings[i].map((l,j)=>{const color=l>0?'#059669':'#DC2626';const w=Math.abs(l)*100;return`<div style="display:flex;align-items:center;gap:0.35rem;font-size:0.7rem;"><span style="width:68px;color:#78716C;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${VN[j]}</span><div style="flex:1;height:5px;background:#FED7AA;border-radius:3px;overflow:hidden;"><div style="height:100%;width:${Math.min(w,100)}%;background:${color};border-radius:3px;"></div></div><span style="width:34px;text-align:right;font-family:monospace;font-weight:700;font-size:0.65rem;color:${color};">${l>0?'+':''}${l.toFixed(2)}</span></div>`;}).join('')}
            </div>
        </div>`).join('');
    document.getElementById('ld-tbl').innerHTML=`<thead><tr><th>Variable</th>${d.component_names.map((n,i)=>'<th style="color:white;">PC'+(i+1)+'</th>').join('')}</tr></thead>
    <tbody>${VN.map((v,j)=>`<tr><td>${v}</td>${d.loadings.map((comp,i)=>{const val=comp[j];const bg=val>0.4?'background:#D1FAE5':(val<-0.4?'background:#FEE2E2':'');const color=val>0?'#059669':'#DC2626';return`<td style="${bg}"><span style="color:${color};font-weight:700;font-family:monospace;">${val>0?'+:''}${val.toFixed(3)}</span></td>`;}).join('')}</tr>`).join('')}</tbody>`;
    dc('pca-sc');ch['pca-sc']=new Chart(document.getElementById('ch-pca-sc'),{type:'scatter',data:{datasets:[{label:'Pacientes',data:d.patients.map(p=>({x:p.cluster_projection[0],y:p.cluster_projection[1]})),backgroundColor:'rgba(59,130,246,0.2)',borderColor:'#3B82F6',borderWidth:1.5,pointRadius:5,pointHoverRadius:8}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{title:{display:true,text:'PC1 ('+d.explained_variance_pct[0]+'%)',color:'#57534E,font:{weight:'bold'}},grid:{color:'#FED7AA'},ticks:{color:'#78716C'}},y:{title:{display:true,text:'PC2 ('+d.explained_variance_pct[1]+'%)',color:'#57534E,font:{weight:'bold'}},grid:{color:'#FED7AA'},ticks:{color:'#78716C'}}}}});
}
</script>
@endsection
