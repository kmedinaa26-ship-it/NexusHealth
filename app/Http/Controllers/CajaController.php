<?php

namespace App\Http\Controllers;

use App\Models\PatientAccount;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CajaController extends Controller
{
    public function index()
    {
        $cuentas = PatientAccount::where('status', 'abierta')
            ->orderBy('opened_at', 'desc')
            ->get();
        return view('superadmin.caja.index', compact('cuentas'));
    }

    public function show($id)
    {
        $cuenta = PatientAccount::with('items', 'patient')->findOrFail($id);
        return view('superadmin.caja.show', compact('cuenta'));
    }

    public function cobrar(Request $request, $id)
    {
        $request->validate([
            'method' => 'required|in:efectivo,tarjeta,transferencia',
            'insurance_pct' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $cuenta = PatientAccount::with('items', 'patient', 'doctor')->findOrFail($id);
        
        // Cálculos
        $subtotal = $cuenta->items->sum('line_total');
        $insurancePct = $request->input('insurance_pct', 0);
        $discountAmount = $request->input('discount_amount', 0);
        $insuranceAmount = $subtotal * ($insurancePct / 100);
        $totalDeductions = $insuranceAmount + $discountAmount;
        $finalTotal = max(0, $subtotal - $totalDeductions);
        $paymentMethod = $request->method;

        // Cerrar cuenta
        $cuenta->status = 'cerrada';
        $cuenta->subtotal = $subtotal;
        $cuenta->discount = $totalDeductions;
        $cuenta->taxes = 0; 
        $cuenta->total = $finalTotal;
        $cuenta->total_paid = $finalTotal;
        $cuenta->closed_at = now();
        $cuenta->save();

        // Registrar pago
        Payment::create([
            'account_id' => $cuenta->id,
            'payment_type' => 'account_close',
            'amount' => $finalTotal,
            'method' => $paymentMethod,
            'cashier_id' => auth()->id(),
        ]);

        // =====================================================================
        // 🧠 INGESTA AUTOMÁTICA A ML (ENTRENAMIENTO EN VIVO)
        // =====================================================================
        try {
            // 1. Calcular días de estancia (mínimo 1 día para evitar errores matemáticos)
            $diasEstancia = Carbon::parse($cuenta->opened_at)->diffInDays(Carbon::parse($cuenta->closed_at)) + 1;

            // 2. Recopilar TODAS las cuentas cerradas para re-entrenar el modelo
            $cuentasCerradas = PatientAccount::where('status', 'cerrada')->get();
            $x = []; // Días de estancia
            $y = []; // Costos totales

            foreach ($cuentasCerradas as $cc) {
                $dias = Carbon::parse($cc->opened_at)->diffInDays(Carbon::parse($cc->closed_at)) + 1;
                $x[] = $dias;
                $y[] = (float) $cc->total_paid;
            }

            // 3. Ejecutar el motor de Regresión Lineal
            $mlService = new \App\Services\ML\RegressionService();
            $resultadoML = $mlService->linearRegression($x, $y);

            // 4. Guardar el modelo/histórico en la base de datos de IA
            \App\Models\MLPrediction::create([
                'patient_id' => $cuenta->patient_id,
                'doctor_id'  => $cuenta->doctor_id,
                'model_type' => 'regresion_costos',
                'input_data' => [
                    'dias_estancia' => $diasEstancia, 
                    'costo_real' => (float)$finalTotal
                ],
                'output_data' => [
                    'pendiente_m' => $resultadoML['pendiente'], 
                    'intercepto_b' => $resultadoML['intercepto'], 
                    'formula' => $resultadoML['formula']
                ],
                'real_outcome' => null,
                'is_correct' => null
            ]);
        } catch (\Exception $e) {
            // Si la IA falla, no detenemos el cobro. Solo registramos el error.
            \Log::error("Error Ingesta ML: " . $e->getMessage());
        }
        // =====================================================================

        // Ruta absoluta del logo para DomPDF
        $logoPath = public_path('images/logo.png');

        // Generar PDF
        $pdf = Pdf::loadView('superadmin.caja.pdf.factura', compact(
            'cuenta', 'subtotal', 'insurancePct', 'insuranceAmount', 'discountAmount', 'finalTotal', 'paymentMethod', 'logoPath'
        ));

        return $pdf->download('Factura-HealthNxs-'.$cuenta->id.'.pdf');
    }
}
