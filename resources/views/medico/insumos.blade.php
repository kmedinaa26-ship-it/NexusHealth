@extends('medico.layout')
@section('title', 'Insumos Médicos')
@section('nav-insumos', 'active')
@section('content')
@php $role = session('doctor_profile', 'Médico C'); @endphp
<div style="background:white; padding:1.5rem; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:1.5rem;">
    <h3 style="font-weight:800;"><i class="fas fa-box-open" style="color:#EA580C;"></i> Insumos Médicos</h3>
</div>
@if($insumos->isEmpty())
<p style="color:#A8A29E; text-align:center; padding:2rem;">Sin insumos registrados</p>
@else
@foreach($insumos as $i)
<div style="background:white; padding:0.75rem; border-radius:8px; margin-bottom:0.5rem; border-left:3px solid #F97316; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <span style="font-weight:700;">{{ $i->name }}</span>
        <span style="font-weight:700; color:{{ $i->stock < 10 ? '#DC2626' : '#166534' }};">Stock: {{ $i->stock }}</span>
    </div>
</div>
@endforeach
@endif
@endsection
