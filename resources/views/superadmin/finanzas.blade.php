@extends('superadmin.layout')
@section('title', 'Centro Financiero Hospitalario')
@section('nav-finanzas', 'active')

@section('content')
<div style="background: #1E1A17; color: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div><h3 style="font-weight: 800;"><i class="fas fa-lock"></i> Centro Financiero (Protegido)</h3><p style="opacity:0.7; font-size:0.85rem;">Sesión verificada. Todas las acciones están siendo auditadas.</p></div>
    <form action="{{ route('superadmin.finanzas.lock') }}" method="POST">@csrf <button type="submit" style="background:#C7291C; color:white; border:none; padding:0.5rem 1rem; border-radius:6px; cursor:pointer; font-weight:700;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión Financiera</button></form>
</div>

<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
    <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); border-left: 5px solid #2D9E6A;">
        <div style="font-size: 0.8rem; color: #736860; font-weight: 700;"><i class="fas fa-arrow-down"></i> Ingresos (Pagado)</div>
        <div style="font-size: 2rem; font-weight: 800; color: #2D9E6A;">${{ number_format($paid, 2) }}</div>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); border-left: 5px solid #FF8C42;">
        <div style="font-size: 0.8rem; color: #736860; font-weight: 700;"><i class="fas fa-clock"></i> Pendientes de Cobro</div>
        <div style="font-size: 2rem; font-weight: 800; color: #FF8C42;">${{ number_format($pending, 2) }}</div>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); border-left: 5px solid #3B82F6;">
        <div style="font-size: 0.8rem; color: #736860; font-weight: 700;"><i class="fas fa-shield-alt"></i> Cobertura Seguros</div>
        <div style="font-size: 2rem; font-weight: 800; color: #3B82F6;">${{ number_format($insurance, 2) }}</div>
    </div>
    <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); border-left: 5px solid #C7291C;">
        <div style="font-size: 0.8rem; color: #736860; font-weight: 700;"><i class="fas fa-pills"></i> Valor Inventario Farmacia</div>
        <div style="font-size: 2rem; font-weight: 800; color: #1E1A17;">${{ number_format($pharma_value, 2) }}</div>
    </div>
</div>

<!-- Exportación y Altos Costos -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <h4 style="font-weight: 800; margin-bottom: 1.5rem;"><i class="fas fa-file-export" style="color: #F05A4E;"></i> Exportación Inteligente</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <a href="{{ route('superadmin.finanzas.export.pdf') }}" target="_blank" style="text-decoration:none; background:#C7291C; color:white; padding:1rem; border-radius:8px; text-align:center; font-weight:700;"><i class="fas fa-file-pdf"></i> Exportar PDF</a>
            <a href="{{ route('superadmin.finanzas.export.csv') }}" style="text-decoration:none; background:#2D9E6A; color:white; padding:1rem; border-radius:8px; text-align:center; font-weight:700;"><i class="fas fa-file-csv"></i> Exportar CSV</a>
        </div>
        <p style="margin-top: 1rem; font-size: 0.8rem; color: #736860;"><i class="fas fa-info-circle"></i> Toda exportación queda registrada en la auditoría con su IP.</p>
    </div>

    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <h4 style="font-weight: 800; margin-bottom: 1.5rem;"><i class="fas fa-chart-bar" style="color: #FF8C42;"></i> Análisis de Altos Costos</h4>
        @foreach($top_invoices as $inv)
        <div style="margin-bottom: 0.75rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600;"><span>{{ $inv->concept }}</span> <span>${{ number_format($inv->amount, 2) }}</span></div>
            <div style="background:#E5E7EB; border-radius:4px; height:8px; overflow:hidden;"><div style="width: {{ min(($inv->amount / 80000) * 100, 100) }}%; background:{{ $inv->amount > 50000 ? '#C7291C' : '#FF8C42' }}; height:100%;"></div></div>
        </div>
        @endforeach
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Facturación -->
    <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <h4 style="font-weight: 800; margin-bottom: 1rem;">Facturación Hospitalaria</h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead><tr style="border-bottom: 2px solid #E5E7EB;"><th style="padding:0.5rem; text-align:left;">Paciente</th><th style="padding:0.5rem;">Concepto</th><th style="padding:0.5rem;">Monto</th><th style="padding:0.5rem;">Estatus</th><th style="padding:0.5rem;">Acción</th></tr></thead>
            <tbody>
                @foreach($invoices as $inv)
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding:0.5rem; font-weight:600;">{{ $inv->patient_name }}</td>
                    <td style="padding:0.5rem; color:#736860;">{{ $inv->concept }}</td>
                    <td style="padding:0.5rem; font-weight:700;">${{ number_format($inv->amount, 2) }}</td>
                    <td style="padding:0.5rem;"><span style="background: {{ $inv->status == 'Pagado' ? '#EBF9F2' : ($inv->status == 'Vencido' ? '#FFF1F0' : '#FFF5EB') }}; color: {{ $inv->status == 'Pagado' ? '#065F46' : ($inv->status == 'Vencido' ? '#8C1A11' : '#9a3412') }}; padding:0.1rem 0.4rem; border-radius:10px; font-size:0.75rem; font-weight:700;">{{ $inv->status }}</span></td>
                    <td style="padding:0.5rem;">
                        @if($inv->status != 'Pagado' && $inv->status != 'Cancelado')
                        <button onclick="requestCancel({{ $inv->id }})" style="background: #8C1A11; color:white; border:none; padding:0.3rem 0.6rem; border-radius:4px; cursor:pointer; font-size:0.75rem;"><i class="fas fa-ban"></i> Cancelar</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Auditoría Financiera y Riesgo -->
    <div>
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); margin-bottom: 1.5rem;">
            <h4 style="font-weight: 800; margin-bottom: 1rem;"><i class="fas fa-skull-crossbones" style="color:#C7291C;"></i> Score Riesgo Financiero</h4>
            @php $fraud_risk = $fake_insurances > 0 ? 80 : 15; $color = $fraud_risk > 50 ? '#C7291C' : '#2D9E6A'; @endphp
            <div style="text-align: center; margin-bottom: 1rem;">
                <div style="font-size: 2rem; font-weight: 800; color: {{ $color }};">{{ $fraud_risk > 50 ? 'ALTO RIESGO' : 'ESTABLE' }}</div>
                <div style="background:#E5E7EB; border-radius:10px; height:10px; overflow:hidden;"><div style="width: {{ $fraud_risk }}%; background:{{ $color }}; height:100%;"></div></div>
            </div>
            @if($fake_insurances > 0)<p style="color:#C7291C; font-size:0.85rem; font-weight:600;"><i class="fas fa-exclamation-triangle"></i> Se detectaron {{ $fake_insurances }} pólizas falsas.</p>@endif
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
            <h4 style="font-weight: 800; margin-bottom: 1rem;"><i class="fas fa-history" style="color:#3B82F6;"></i> Trazabilidad Financiera</h4>
            @foreach($finance_logs as $log)
            <div style="border-left: 3px solid #1E1A17; padding-left: 0.5rem; margin-bottom: 1rem; background:#F9FAFB; padding:0.5rem; border-radius:4px;">
                <div style="font-size:0.75rem; color:#736860;">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m H:i') }} - {{ $log->ip_address }}</div>
                <div style="font-size:0.85rem; font-weight:700;">{{ $log->user_name }} ({{ $log->user_role }})</div>
                <div style="font-size:0.8rem; color:#1E1A17;">{{ $log->details }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Doble Autenticación (Cancelar Factura) -->
<div id="modal-cancel" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:200; align-items:center; justify-content:center;">
    <div style="background:white; padding:2rem; border-radius:12px; width:400px; border-top:6px solid #C7291C;">
        <h3 style="font-weight:800; margin-bottom:1rem; color:#C7291C;"><i class="fas fa-exclamation-circle"></i> Autorización Requerida</h3>
        <p style="font-size:0.9rem; color:#736860; margin-bottom:1.5rem;">Cancelar una factura requiere firma digital. Ingrese su PIN de seguridad.</p>
        <form id="cancel-form" action="" method="POST">
            @csrf @method('PUT')
            <input type="password" name="finance_pin" maxlength="6" required style="width: 100%; padding: 1rem; border: 2px solid #1E1A17; border-radius: 8px; font-size: 1.5rem; text-align: center; letter-spacing: 10px; margin-bottom: 1rem;" placeholder="PIN">
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="document.getElementById('modal-cancel').style.display='none'" style="flex:1; padding:0.8rem; border:1px solid #E5E7EB; background:white; border-radius:6px; cursor:pointer; font-weight:700;">Abortar</button>
                <button type="submit" style="flex:1; padding:0.8rem; background:#C7291C; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:700;"><i class="fas fa-signature"></i> Firmar y Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function requestCancel(invoiceId) {
    document.getElementById('cancel-form').action = '/superadmin/finanzas/' + invoiceId + '/cancel';
    document.getElementById('modal-cancel').style.display = 'flex';
}
</script>
@endsection
