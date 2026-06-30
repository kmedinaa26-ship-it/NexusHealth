<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MlFeed extends Model {
    protected $fillable = ['patient_name','concept','amount','source_module','source_detail'];
}
