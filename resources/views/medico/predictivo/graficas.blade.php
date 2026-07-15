@extends('medico.layout')
@section('title', 'Graficas de Riesgo')
@section('nav-graficas-pred', 'active')

@section('content')
<h2 style="font-weight:900;color:#9A3412;margin-bottom:1.5rem"><i class="fas fa-chart-line" style="color:#F97316"></i> Graficas de Riesgo y Signos Vitales</h2>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
    <!-- Riesgo por dia -->
    <div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #F97316;">
        <h3 style="font-weight:900;color:#9A3412;margin-bottom:1rem;"><i class="fas fa-chart-area" style="color:#F97316"></i> Riesgo por Dia</h3>
        <div style="height:250px;display:flex;align-items:flex-end;gap:4px;padding:1rem 0;border-bottom:2px solid #E7E5E4;">
            <div style="flex:1;background:linear-gradient(to top,#16A34A,#22C55E);height:25%;border-radius:4px 4px 0 0;position:relative;"><span style="position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#78716C;">25%</span></div>
            <div style="flex:1;background:linear-gradient(to top,#16A34A,#22C55E);height:30%;border-radius:4px 4px 0 0;position:relative;"><span style="position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#78716C;">30%</span></div>
            <div style="flex:1;background:linear-gradient(to top,#EA580C,#F97316);height:55%;border-radius:4px 4px 0 0;position:relative;"><span style="position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#78716C;">55%</span></div>
            <div style="flex:1;background:linear-gradient(to top,#EA580C,#F97316);height:62%;border-radius:4px 4px 0 0;position:relative;"><span style="position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#78716C;">62%</span></div>
            <div style="flex:1;background:linear-gradient(to top,#DC2626,#EF4444);height:78%;border-radius:4px 4px 0 0;position:relative;"><span style="position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#78716C;">78%</span></div>
            <div style="flex:1;background:linear-gradient(to top,#DC2626,#EF4444);height:85%;border-radius:4px 4px 0 0;position:relative;"><span style="position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#DC2626;">85%</span></div>
            <div style="flex:1;background:linear-gradient(to top,#7C2D12,#991B1B);height:92%;border-radius:4px 4px 0 0;position:relative;"><span style="position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:0.6rem;font-weight:800;color:#DC2626;">92%</span></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:0.6rem;color:#A8A29E;margin-top:0.3rem;">
            <span>Lun</span><span>Mar</span><span>Mie</span><span>Jue</span><span>Vie</span><span>Sab</span><span>Dom</span>
        </div>
    </div>

    <!-- Signos vitales vs prediccion -->
    <div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #DC2626;">
        <h3 style="font-weight:900;color:#9A3412;margin-bottom:1rem;"><i class="fas fa-heartbeat" style="color:#DC2626"></i> Signos Vitales vs Prediccion</h3>
        <div style="padding:0.5rem 0;">
            <div style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:700;margin-bottom:0.2rem;">
                    <span style="color:#57534E;">Saturacion O2</span><span style="color:#DC2626;">88% (Peligro)</span>
                </div>
                <div style="height:10px;background:#F5F5F4;border-radius:5px;overflow:hidden;">
                    <div style="height:100%;width:88%;background:linear-gradient(90deg,#16A34A,#EA580C,#DC2626);border-radius:5px;"></div>
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:700;margin-bottom:0.2rem;">
                    <span style="color:#57534E;">Frecuencia Cardiaca</span><span style="color:#EA580C;">105 lpm</span>
                </div>
                <div style="height:10px;background:#F5F5F4;border-radius:5px;overflow:hidden;">
                    <div style="height:100%;width:70%;background:#EA580C;border-radius:5px;"></div>
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:700;margin-bottom:0.2rem;">
                    <span style="color:#57534E;">Presion Arterial</span><span style="color:#F97316;">150/95</span>
                </div>
                <div style="height:10px;background:#F5F5F4;border-radius:5px;overflow:hidden;">
                    <div style="height:100%;width:60%;background:#F97316;border-radius:5px;"></div>
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:700;margin-bottom:0.2rem;">
                    <span style="color:#57534E;">Temperatura</span><span style="color:#16A34A;">37.8C</span>
                </div>
                <div style="height:10px;background:#F5F5F4;border-radius:5px;overflow:hidden;">
                    <div style="height:100%;width:40%;background:#16A34A;border-radius:5px;"></div>
                </div>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:0.75rem;font-weight:700;margin-bottom:0.2rem;">
                    <span style="color:#57534E;">Glasgow</span><span style="color:#DC2626;">11/15</span>
                </div>
                <div style="height:10px;background:#F5F5F4;border-radius:5px;overflow:hidden;">
                    <div style="height:100%;width:73%;background:linear-gradient(90deg,#16A34A,#DC2626);border-radius:5px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leyenda de colores -->
<div style="background:white;border-radius:14px;padding:1rem 1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);margin-top:1.5rem;display:flex;gap:2rem;justify-content:center;">
    <span style="font-size:0.75rem;font-weight:700;color:#16A34A;"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Bajo (&lt;30%)</span>
    <span style="font-size:0.75rem;font-weight:700;color:#EA580C;"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Moderado (30-60%)</span>
    <span style="font-size:0.75rem;font-weight:700;color:#DC2626;"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Alto (60-80%)</span>
    <span style="font-size:0.75rem;font-weight:700;color:#7C2D12;"><i class="fas fa-circle" style="font-size:0.5rem;"></i> Critico (&gt;80%)</span>
</div>
@endsection
