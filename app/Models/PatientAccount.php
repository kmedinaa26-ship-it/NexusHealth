<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientAccount extends Model
{
    protected $table = 'patient_accounts';

    protected $fillable = [
        'patient_id', 'doctor_id', 'encounter_type', 'reference_id', 'status',
        'subtotal', 'discount', 'taxes', 'total', 'total_paid',
        'insurance_id', 'insurance_coverage', 'opened_at', 'closed_at'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(AccountItem::class, 'account_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'account_id');
    }

    // En tu sistema, el paciente vive en la tabla triages
    public function patient()
    {
        return $this->belongsTo(Triage::class, 'patient_id');
    }

    // Agregamos la relación del médico
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
