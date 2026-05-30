<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 'curp', 'rfc', 'ine_path', 'cedula_path', 'certifications_path', 'validation_status', 'rejection_reason', 'finance_pin',
    ];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed']; }
}
