@extends('superadmin.layout')

@section('title', 'Detalle de Cuenta')
@section('nav-caja', 'active')

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">

    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04);">
        <h3 style="font-weight: 800; margin-bottom: 1.5rem; color: #1E1A17;">
            <i class="fas fa-file-invoice" style="color: #F05A4E; margin-right: 0.5rem;"></i> Detalle de Cargos (Cuenta #{{ $cuenta->id }})
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #E5E7EB;">
                    <th style="padding: 0.8rem; text-align: left;">Concepto</th>
                    <th style="padding: 0.8rem; text-align: center;">Cant.</th>
                    <th style="padding: 0.8rem; text-align: right;">P. Unitario</th>
                    <th style="padding: 0.8rem; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cuenta->items as $item)
                <tr style="border-bottom: 1px solid #F3F4F6;">
                    <td style="padding: 0.8rem;">{{ $item->concept }} <br><small style="color:#736860;">({{ $item->source_module }})</small></td>
                    <td style="padding: 0.8rem; text-align: center;">{{ $item->quantity }}</td>
                    <td style="padding: 0.8rem; text-align: right;">$ {{ number_format($item->unit_price, 2) }}</td>
                    <td style="padding: 0.8rem; text-align: right; font-weight: 700;">$ {{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); height: fit-content;">
        <h3 style="font-weight: 800; margin-bottom: 1.5rem; color: #1E1A17;">
            <i class="fas fa-credit-card" style="color: #2D9E6A; margin-right: 0.5rem;"></i> Cobro y Facturación
        </h3>

        <form action="{{ route('caja.cobrar', $cuenta->id) }}" method="POST" id="paymentForm">
            @csrf
            
            <div style="background: #F9FAFB; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                    <span>Subtotal:</span> 
                    <span id="subtotal-display">$ {{ number_format($cuenta->total, 2) }}</span>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: #736860; font-weight: 700;">Cobertura de Seguro (%)</label>
                    <input type="number" id="insurance_pct" name="insurance_pct" value="0" min="0" max="100" step="1" 
                           style="width: 100%; padding: 0.6rem; border: 1px solid #E5E7EB; border-radius: 6px; margin-top: 0.3rem;">
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem; color: #2D9E6A;">
                    <span>Aporta Seguro:</span> 
                    <span id="insurance-display">$ 0.00</span>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.8rem; color: #736860; font-weight: 700;">Descuento Adicional ($)</label>
                    <input type="number" id="discount_amount" name="discount_amount" value="0" min="0" step="0.01" 
                           style="width: 100%; padding: 0.6rem; border: 1px solid #E5E7EB; border-radius: 6px; margin-top: 0.3rem;">
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem; color: #C7291C;">
                    <span>Descuento:</span> 
                    <span id="discount-display">$ 0.00</span>
                </div>

                <hr style="border-top: 1px dashed #CBD5E1; margin: 1rem 0;">

                <div style="display: flex; justify-content: space-between;">
                    <span style="font-weight: 800;">TOTAL A PAGAR:</span> 
                    <span id="total-display" style="font-weight: 800; font-size: 1.5rem; color: #C7291C;">$ {{ number_format($cuenta->total, 2) }}</span>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="font-size: 0.8rem; color: #736860; font-weight: 700;">Método de Pago</label>
                <select name="method" style="width: 100%; padding: 0.8rem; border: 1px solid #E5E7EB; border-radius: 8px; margin-top: 0.3rem;" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            <button type="submit" style="width: 100%; background: #F05A4E; color: white; padding: 1rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
                <i class="fas fa-file-pdf"></i> Cobrar y Generar Factura PDF
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const subtotal = parseFloat("{{ $cuenta->total }}"); // Total original de la cuenta
        const insuranceInput = document.getElementById('insurance_pct');
        const discountInput = document.getElementById('discount_amount');
        const totalDisplay = document.getElementById('total-display');
        const insuranceDisplay = document.getElementById('insurance-display');
        const discountDisplay = document.getElementById('discount-display');

        function calculateTotals() {
            const insPct = parseFloat(insuranceInput.value) || 0;
            const discAmt = parseFloat(discountInput.value) || 0;
            
            const insAmt = subtotal * (insPct / 100);
            const finalTotal = Math.max(0, subtotal - insAmt - discAmt);

            insuranceDisplay.innerText = '$ ' + insAmt.toFixed(2);
            discountDisplay.innerText = '$ ' + discAmt.toFixed(2);
            totalDisplay.innerText = '$ ' + finalTotal.toFixed(2);
        }

        insuranceInput.addEventListener('input', calculateTotals);
        discountInput.addEventListener('input', calculateTotals);
    });
</script>
@endsection
