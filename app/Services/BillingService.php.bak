<?php

namespace App\Services;

use App\Models\PatientAccount;
use App\Models\AccountItem;
use App\Models\Payment;

class BillingService
{
    public function openAccount($patientId, $encounterType, $referenceId = null, $doctorId = null)
    {
        return PatientAccount::create([
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'encounter_type' => $encounterType,
            'reference_id' => $referenceId,
            'status' => 'abierta',
            'opened_at' => now(),
        ]);
    }

    public function addCharge($accountId, $data)
    {
        $lineTotal = ($data['quantity'] * $data['unit_price']) - ($data['discount'] ?? 0);

        $item = AccountItem::create([
            'account_id' => $accountId,
            'type' => $data['type'],
            'concept' => $data['concept'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'discount' => $data['discount'] ?? 0,
            'line_total' => $lineTotal,
            'source_module' => $data['source_module'] ?? 'sistema',
            'prescribed_by' => $data['prescribed_by'] ?? null,
            'dispensed_by' => $data['dispensed_by'] ?? null,
        ]);

        $this->recalculateAccountTotal($accountId);
        return $item;
    }

    public function recalculateAccountTotal($accountId)
    {
        $account = PatientAccount::find($accountId);
        $subtotal = $account->items()->sum('line_total');
        $account->subtotal = $subtotal;
        // Al recalcular, respeta los descuentos/impuestos ya configurados a nivel de cuenta
        $account->total = $subtotal - ($account->discount ?? 0) + ($account->taxes ?? 0);
        $account->save();
        return $account;
    }

    public function directSale(array $items, $paymentMethod, $cashierId, $totalAmount)
    {
        $payment = Payment::create([
            'account_id' => null,
            'payment_type' => 'direct_sale',
            'amount' => $totalAmount,
            'method' => $paymentMethod,
            'cashier_id' => $cashierId,
        ]);

        foreach ($items as $itemData) {
            AccountItem::create([
                'account_id' => null,
                'payment_id' => $payment->id,
                'type' => $itemData['type'],
                'concept' => $itemData['concept'],
                'reference_type' => $itemData['reference_type'] ?? null,
                'reference_id' => $itemData['reference_id'] ?? null,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'line_total' => $itemData['line_total'],
                'source_module' => 'farmacia',
                'dispensed_by' => $cashierId,
            ]);
        }

        return $payment;
    }
}
