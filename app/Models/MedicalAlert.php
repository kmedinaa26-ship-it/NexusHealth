<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalAlert extends Model
{
    protected $fillable = [
        'vital_sign_id', 'triage_id', 'patient_name', 'type', 'category', 
        'message', 'is_read', 'triggered_by', 'target_user_id'
    ];
}
