<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; width: 300px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 10px; margin-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; color: #1E1A17; }
        .header p { margin: 2px 0; font-size: 10px; color: #666; }
        .meta { font-size: 10px; margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; border-bottom: 1px solid #ddd; padding: 4px 0; font-size: 11px; }
        td { padding: 4px 0; border-bottom: 1px dotted #eee; }
        .totals { margin-top: 10px; text-align: right; }
        .totals .total { font-size: 16px; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #999; border-top: 2px dashed #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HealthNxs Hospital</h1>
        <p>Farmacia Central - Venta Directa</p>
        <p>Tel: 555-0100 | RFC: HNX123456</p>
    </div>

    <div class="meta">
        <p><strong>Folio:</strong> {{ $payment->id }}</p>
        <p><strong>Fecha:</strong> {{ $payment->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Cajero:</strong> {{ $payment->cashier_id }}</p>
        <p><strong>Método:</strong> {{ ucfirst($payment->method) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->concept }}</td>
                <td>{{ $item->quantity }}</td>
                <td>$ {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p style="font-size: 14px; font-weight: bold;">TOTAL: $ {{ number_format($payment->amount, 2) }}</p>
    </div>

    <div class="footer">
        <p>¡Gracias por su compra!</p>
        <p>Conserve este ticket para cualquier aclaración.</p>
    </div>
</body>
</html>
