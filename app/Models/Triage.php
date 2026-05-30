<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triage extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_name', 'age', 'triage_level', 'symptoms', 'status',
        'vitals_ta', 'vitals_fc', 'vitals_temp', 'vitals_spo2',
        'assigned_area', 'is_derived', 'derived_to', 'derived_reason',
        'doctor_id', 'nurse_id',
    ];

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function hospitalization()
    {
        return $this->hasOne(Hospitalization::class);
    }

    public function evolutions()
    {
        return $this->hasMany(NurseEvolution::class);
    }
}
