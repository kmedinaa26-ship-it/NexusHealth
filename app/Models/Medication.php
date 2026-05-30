<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Medication extends Model {
    protected $fillable = ['name', 'active_ingredient', 'stock', 'min_stock', 'type', 'price'];
}
