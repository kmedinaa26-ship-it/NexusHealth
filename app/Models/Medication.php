<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'active_ingredient', 'stock', 'min_stock', 'type',
        'price', 'required_level', 'enfermera_can_administer', 'origin',
        'lot_number', 'expiry_date', 'location', 'provider_name',
        // Campos nuevos de facturación
        'cost_price', 'sale_price', 'is_insumo'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'enfermera_can_administer' => 'boolean',
        'is_insumo' => 'boolean',
    ];

    // Accesor inteligente: Si hay sale_price lo usa, si no, usa el price normal
    public function getVentaPriceAttribute() {
        return $this->sale_price > 0 ? $this->sale_price : $this->price;
    }

    public function isLowStock() {
        return $this->stock <= $this->min_stock;
    }

    public function isExpiringSoon() {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function isExpired() {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getStockColorAttribute() {
        if ($this->stock == 0) return '#C7291C';
        if ($this->isLowStock()) return '#FF8C42';
        return '#2D9E6A';
    }

    public function getExpiryColorAttribute() {
        if ($this->isExpired()) return '#C7291C';
        if ($this->isExpiringSoon()) return '#FF8C42';
        return '#2D9E6A';
    }

    public function getLevelLabelAttribute() {
        $levels = [
            'A' => 'A - Especialista',
            'B' => 'B - Hospitalizacion',
            'C' => 'C - Basico/Pasante',
            'Enfermera' => 'Enfermera',
        ];
        return $levels[$this->required_level] ?? $this->required_level;
    }

    public function getLevelColorAttribute() {
        $colors = [
            'A' => '#C7291C',
            'B' => '#FF8C42',
            'C' => '#2D9E6A',
            'Enfermera' => '#3B82F6',
        ];
        return $colors[$this->required_level] ?? '#736860';
    }
}
