<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    protected $fillable = [
        'name', 'category', 'price', 'unit', 'is_active'
    ];
}
