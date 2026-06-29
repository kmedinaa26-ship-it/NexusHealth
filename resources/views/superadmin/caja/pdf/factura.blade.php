<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $cuenta->id }}</title>
    <style>
        @page { margin: 20px 30px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #1E1A17; 
            position: relative; 
        }

        /* MARCA DE AGUA */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            opacity: 0.06;
            z-index: -1;
            width: 500px;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #F05A4E;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo-header { height: 50px; }
        .hospital-info h1 { margin: 0; font-size: 22px; color: #1E1A17; letter-spacing: 1px; }
        .hospital-info p { margin: 2px 0; color: #736860; font-size: 10px; }
        .factura-box { text-align: right; background: #F9FAFB; padding: 10px 15px; border-radius: 6px; border: 1px solid #E5E7EB; }
        .factura-box h2 { margin: 0; color: #F05A4E; font-size: 18px; }
        .factura-box p { margin: 2px 0; font-size: 10px; }

        /* DATOS PACIENTE */
        .patient-section {
            background: #F9FAFB;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 5px solid #2D9E6A;
            display: flex;
            justify-content: space-between;
        }
        .patient-info { flex: 2; }
        .patient-info strong { color: #1E1A17; }
        .patient-right { flex: 1; text-align: right; font-size: 10px; color: #736860; }

        /* TABLA ITEMS */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 10px; }
        thead th { background: #1E1A17; color: white; padding: 8px; text-align: left; font-weight: 600; }
        thead th:last-child { text-align: right; }
        thead th:nth-child(3) { text-align: right; }
        tbody tr { border-bottom: 1px solid #E5E7EB; }
        tbody tr:nth-child(even) { background-color: #FAFAFA; }
        tbody td { padding: 8px; vertical-align: top; }
        tbody td:last-child { text-align: right; font-weight: bold; }
        tbody td:nth-child(3) { text-align: right; }
        .module-badge { 
            background: #E5E7EB; color: #1E1A17; padding: 2px 6px; border-radius: 4px; 
            font-size: 9px; font-weight: bold; text-transform: uppercase; 
        }

        /* TOTALES Y PAGO */
        .summary-container { display: flex; justify-content: flex-end; }
        .summary-table { width: 280px; }
        .summary-table td { padding: 4px 0; font-size: 11px; }
        .summary-table .text-right { text-align: right; }
        .summary-table .text-green { color: #2D9E6A; font-weight: bold; }
        .summary-table .text-red { color: #C7291C; font-weight: bold; }
        .total-row { background: #1E1A17; color: white; }
        .total-row td { padding: 8px 10px; font-size: 14px; font-weight: 800; border-radius: 4px; }
        
        .payment-badge {
            display: inline-block; background: #2D9E6A; color: white; padding: 5px 12px; 
            border-radius: 20px; font-weight: bold; font-size: 11px; margin-top: 15px;
        }

        /* FOOTER */
        .footer { 
            position: fixed; bottom: 20px; left: 30px; right: 30px;
            text-align: center; font-size: 9px; color: #999; 
            border-top: 1px solid #E5E7EB; padding-top: 10px; 
        }
    </style>
</head>
<body>

    <!-- Marca de Agua -->
    @if(file_exists($logoPath))
    <img src="{{ $logoPath }}" class="watermark">
    @endif

    <!-- Encabezado -->
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            @if(file_exists($logoPath))
            <img src="{{ $logoPath }}" class="logo-header">
            @endif
            <div class="hospital-info">
                <h1>HealthNexus</h1>
                <p>RFC: HNX240101XXX</p>
                <p>Av. de la Salud 123, CDMX</p>
                <p>Tel: (55) 1234-5678</p>
            </div>
        </div>
        <div class="factura-box">
            <h2>FACTURA</h2>
            <p><strong>No. Cuenta:</strong> {{ $cuenta->id }}</p>
            <p><strong>Fecha Emisión:</strong> {{ \Carbon\Carbon::parse($cuenta->closed_at)->format('d/m/Y H:i') }}</p>
            <p><strong>Folio Pago:</strong> #{{ str_pad($cuenta->id + 1000, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>

    <!-- Datos del Paciente -->
    <div class="patient-section">
        <div class="patient-info">
            <h3 style="margin: 0 0 8px 0; font-size: 14px; color: #1E1A17;">
                {{ $cuenta->patient->patient_name ?? 'Paciente Desconocido' }}
            </h3>
            <p style="margin: 0 0 4px 0;"><strong>Edad:</strong> {{ $cuenta->patient->age ?? 'N/A' }} años</p>
            <p style="margin: 0 0 4px 0;"><strong>Tipo de Ingreso:</strong> {{ ucfirst($cuenta->encounter_type) }}</p>
            <p style="margin: 0;"><strong>Médico Tratante:</strong> Dr. {{ $cuenta->doctor->name ?? 'N/A' }}</p>
        </div>
        <div class="patient-right">
            <p style="margin: 0 0 4px 0;"><strong>Nivel Triage:</strong> {{ $cuenta->patient->triage_level ?? 'N/A' }}</p>
            <p style="margin: 0;"><strong>Estatus al Egreso:</strong> <span style="color:#2D9E6A; font-weight:bold;">Alta / Liquidado</span></p>
        </div>
    </div>

    <!-- Tabla de Cargos -->
    <table>
        <thead>
            <tr>
                <th width="10%">Cant.</th>
                <th width="45%">Concepto / Servicio Médico</th>
                <th width="15%">Módulo</th>
                <th width="15%">P. Unitario</th>
                <th width="15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuenta->items as $item)
            <tr>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->concept }}</td>
                <td><span class="module-badge">{{ ucfirst($item->source_module) }}</span></td>
                <td>$ {{ number_format($item->unit_price, 2) }}</td>
                <td>$ {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resumen de Costos y Pago -->
    <div class="summary-container">
        <table class="summary-table">
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">$ {{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($insuranceAmount > 0)
            <tr>
                <td class="text-green">Cobertura de Seguro ({{ $insurancePct }}%):</td>
                <td class="text-right text-green">- $ {{ number_format($insuranceAmount, 2) }}</td>
            </tr>
            @endif
            @if($discountAmount > 0)
            <tr>
                <td class="text-red">Descuento Adicional:</td>
                <td class="text-right text-red">- $ {{ number_format($discountAmount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>IVA (0% Médico):</td>
                <td class="text-right">$ 0.00</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL PAGADO:</td>
                <td class="text-right">$ {{ number_format($finalTotal, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: right; padding-right: 10px;">
        <span class="payment-badge">
            <i class="fas fa-money-bill-wave"></i> PAGADO VIA: {{ strtoupper($paymentMethod) }}
        </span>
    </div>

    <div class="footer">
        <p>Este documento ampara la liquidación de servicios médicos en HealthNexus. Para aclaraciones, presentarse con este folio.</p>
        <p>HealthNexus © {{ date('Y') }} - Acceso · Roles · Conexión · Salud</p>
    </div>

</body>
</html>
