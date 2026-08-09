<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedReport extends Model
{
    protected $fillable = ['user_id', 'module', 'from', 'to', 'title', 'file_path', 'total_events', 'total_outliers'];

    protected $casts = [
        'from' => 'date',
        'to' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
