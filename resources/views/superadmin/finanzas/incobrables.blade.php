@extends('superadmin.layout')
@section('title', 'Incobrables')
@section('nav-fin-incobrables', 'active')

@section('content')
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #DC2626;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:0.5rem;"><i class="fas fa-ban" style="color:#DC2626"></i> Incobrables y Riesgo de Impago</h3>
    <p style="font-size:0.8rem;color:#78716C;margin-bottom:1.5rem;">Cruce con ML de impago para predecir que pacientes no pagaran</p>
    <div style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:10px;padding:2rem;text-align:center;">
        <i class="fas fa-brain" style="font-size:2rem;color:#DC2626;margin-bottom:0.5rem;"></i>
        <p style="font-weight:800;color:#991B1B;">Modelo de Impago (Proximo)</p>
        <p style="font-size:0.85rem;color:#78716C;">Se entrenara un modelo de clasificacion para predecir probabilidad de impago usando historial del paciente, monto y tipo de servicio.</p>
    </div>
</div>
@endsection
