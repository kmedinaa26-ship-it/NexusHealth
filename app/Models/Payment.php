<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'account_id', 'payment_type', 'amount', 'method', 'reference', 'cashier_id'
    ];
}
