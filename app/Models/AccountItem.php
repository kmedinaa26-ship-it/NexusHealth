<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountItem extends Model
{
    protected $table = 'account_items';
    
    protected $fillable = [
        'account_id', 'payment_id', 'type', 'concept', 'reference_type', 'reference_id',
        'quantity', 'unit_price', 'discount', 'line_total',
        'source_module', 'prescribed_by', 'dispensed_by'
    ];
}
