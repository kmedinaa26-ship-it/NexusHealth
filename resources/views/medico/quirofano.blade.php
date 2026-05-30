@extends('medico.layout')
@section('title', 'Quirófano')
@section('nav-quirofano', 'active')
@section('content')
<div style="background:#FEF2F2; padding:1.5rem; border-radius:12px; border:2px solid #FCA5A5; margin-bottom:1.5rem;">
    <h3 style="font-weight:800; color:#991B1B;"><i class="fas fa-cut"></i> Quirófano</h3>
    <p style="font-size:0.85rem; color:#7F1D1D;">Acceso exclusivo Médico A - Autorización quirúrgica</p>
</div>
@if($scheduled->isEmpty())
<div style="background:#F0FDF4; padding:3rem; border-radius:12px; text-align:center; border:2px solid #BBF7D0;">
    <i class="fas fa-check-circle" style="font-size:3rem; color:#2D9E6A;"></i>
    <h3 style="font-weight:800; color:#166534; margin-top:1rem;">Sin cirugías programadas</h3>
</div>
@endif
@endsection
