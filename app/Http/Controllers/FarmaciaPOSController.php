<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Payment;
use App\Models\AccountItem;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class FarmaciaPOSController extends Controller
{
    public function index()
    {
        $medicamentos = Medication::orderBy('name', 'asc')->get();
        return view('farmacia.pos', compact('medicamentos'));
    }

    public function processSale(Request $request, BillingService $billingService)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:medications,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia'
        ]);

        $cashierId = Auth::id();
        $totalAmount = 0;
        $itemsToCharge = [];

        foreach ($request->items as $item) {
            $med = Medication::find($item['id']);
            
            if ($med->required_level == 'A' || strtolower($med->type) == 'controlado') {
                return back()->withErrors(['error' => "SECURIDAD: {$med->name} es controlado. Requiere receta y dispensación especial."])->withInput();
            }

            if ($med->stock < $item['quantity']) {
                return back()->withErrors(['error' => "Stock insuficiente para {$med->name}"])->withInput();
            }

            $precioVenta = $med->venta_price;
            $lineTotal = $precioVenta * $item['quantity'];
            $totalAmount += $lineTotal;

            $itemsToCharge[] = [
                'type' => 'producto',
                'concept' => $med->name,
                'reference_type' => Medication::class,
                'reference_id' => $med->id,
                'quantity' => $item['quantity'],
                'unit_price' => $precioVenta,
                'line_total' => $lineTotal,
                'source_module' => 'farmacia',
                'dispensed_by' => $cashierId
            ];

            $med->stock -= $item['quantity'];
            $med->save();
        }

        $payment = $billingService->directSale($itemsToCharge, $request->payment_method, $cashierId, $totalAmount);

        return redirect()->route('farmacia.pos')->with('success', "¡Venta realizada! Total: $" . number_format($totalAmount, 2))->with('ticket_id', $payment->id);
    }

    public function downloadTicket($id)
    {
        $payment = Payment::findOrFail($id);
        $items = AccountItem::where('payment_id', $id)->get();
        
        $pdf = Pdf::loadView('farmacia.pdf.ticket', compact('payment', 'items'));
        return $pdf->stream('ticket-' . $payment->id . '.pdf');
    }
}
