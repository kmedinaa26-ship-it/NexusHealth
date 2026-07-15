@extends('superadmin.layout')

@section('title', 'Autorización de Controlados')
@section('nav-controlados', 'active')

@section('content')
<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); margin-top: 1.5rem;">
    <h3 style="font-weight: 800; margin-bottom: 1.5rem; color: #1E1A17;">
        <i class="fas fa-shield-alt" style="color: #DC2626; margin-right: 0.5rem;"></i> Recetas de Controlados Pendientes
    </h3>

    @if(session('success')) <div style="background:#D1FAE5; color:#2D9E6A; padding:1rem; border-radius:8px; margin-bottom:1rem;">{{ session('success') }}</div> @endif
    @if(session('error')) <div style="background:#FFF0F0; color:#C7291C; padding:1rem; border-radius:8px; margin-bottom:1rem;">{{ session('error') }}</div> @endif

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #1E1A17; color: white;">
                <th style="padding: 0.8rem; text-align: left;">Fecha</th>
                <th style="padding: 0.8rem; text-align: left;">Paciente</th>
                <th style="padding: 0.8rem; text-align: left;">Médico Solicitante</th>
                <th style="padding: 0.8rem; text-align: left;">Medicamento</th>
                <th style="padding: 0.8rem; text-align: center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recetas as $r)
            <tr style="border-bottom: 1px solid #E5E7EB;">
                <td style="padding: 1rem;">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
                <td style="padding: 1rem; font-weight: 600;">{{ $r->pat_name ?? 'N/A' }}</td>
                <td style="padding: 1rem;">{{ $r->doc_name }}</td>
                <td style="padding: 1rem; color: #DC2626; font-weight: 700;">{{ $r->med_name }} (x{{ $r->quantity }})</td>
                <td style="padding: 1rem; text-align: center;">
                    <form action="{{ route('superadmin.controlados.aprobar', $r->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" style="background: #2D9E6A; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 700;"><i class="fas fa-check"></i> Autorizar</button>
                    </form>
                    <form action="{{ route('superadmin.controlados.rechazar', $r->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" style="background: #C7291C; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 700;"><i class="fas fa-times"></i> Rechazar</button>
                    </form>
                </td>
            </tr>
            @endforeach
            @if($recetas->isEmpty())
            <tr><td colspan="5" style="text-align: center; padding: 2rem; color: #736860;">No hay recetas pendientes de autorización.</td></tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
