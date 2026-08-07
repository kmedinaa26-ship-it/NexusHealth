<?php

namespace App\Http\Controllers;

use App\Models\Triage;
use App\Models\Medication;
use App\Models\PatientAccount;
use App\Models\ServiceLog; // <-- NUEVO: Importamos el modelo SLA
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuirofanoController extends Controller
{
    public function index()
    {
        $pacientes = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        $insumos = Medication::where('stock', '>', 0)->orderBy('name', 'asc')->get();
        return view('superadmin.quirofano.index', compact('pacientes', 'insumos'));
    }

    public function cargar(Request $request, BillingService $billingService)
    {
        $request->validate([
            'patient_id' => 'required|exists:triages,id',
            'cirugia' => 'required|string',
            'horas_or' => 'required|numeric|min:0.5',
            'insumos' => 'nullable|array',
            'insumos.*.id' => 'required_with:insumos|exists:medications,id',
            'insumos.*.quantity' => 'required_with:insumos|integer|min:1'
        ]);

        $doctorId = Auth::id();
        $accountId = null;

        // Abrir cuenta de cirugía
        $account = PatientAccount::firstOrCreate(
            ['patient_id' => $request->patient_id, 'status' => 'abierta'],
            ['encounter_type' => 'cirugia', 'doctor_id' => $doctorId, 'opened_at' => now()]
        );
        $accountId = $account->id;

        // 1. Cargos Fijos (Quirófano y Honorarios)
        $billingService->addCharge($accountId, [
            'type' => 'sala',
            'concept' => 'Uso de Quirófano (' . $request->horas_or . ' hrs)',
            'quantity' => $request->horas_or,
            'unit_price' => 5000.00,
            'line_total' => 5000.00 * $request->horas_or,
            'source_module' => 'quirofano',
            'prescribed_by' => $doctorId,
            'dispensed_by' => $doctorId,
        ]);

        $billingService->addCharge($accountId, [
            'type' => 'honorario',
            'concept' => 'Honorarios Médicos (' . $request->cirugia . ')',
            'quantity' => 1,
            'unit_price' => 15000.00,
            'line_total' => 15000.00,
            'source_module' => 'quirofano',
            'prescribed_by' => $doctorId,
            'dispensed_by' => $doctorId,
        ]);

        // 2. Cargos Variables (Insumos)
        if ($request->has('insumos')) {
            foreach ($request->insumos as $item) {
                $med = Medication::find($item['id']);
                if ($med && $med->stock >= $item['quantity']) {
                    $med->decrement('stock', $item['quantity']);
                    
                    $billingService->addCharge($accountId, [
                        'type' => 'insumo',
                        'concept' => $med->name . ' (Insumo Quirúrgico)',
                        'reference_type' => Medication::class,
                        'reference_id' => $med->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $med->venta_price,
                        'line_total' => $med->venta_price * $item['quantity'],
                        'source_module' => 'quirofano',
                        'dispensed_by' => $doctorId,
                    ]);
                }
            }
        }

        // ==========================================
        // 🚨 NUEVO: RELOJ DEL SLA CONECTADO 🚨
        // ==========================================
        // Como el formulario no tiene "hora de inicio", asumimos que 
        // la cirugía empezó hace X horas y terminó AHORA.
        $horaFin = now();
        $horaInicio = now()->subHours($request->horas_or);

        ServiceLog::logFromEvent(
            'quirofano',
            'cirugia',
            $horaInicio,
            $horaFin,
            $doctorId,
            'PAC-' . $request->patient_id // Identificador anónimo
        );
        // ==========================================

        return redirect()->route('quirofano.index')->with('success', "¡Cirugía cargada a la cuenta del paciente exitosamente! Cuenta ID: {$accountId}");
    }
}
