<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'country',
        'tax_type',
        'min_income',
        'max_income',
        'rate',
        'fixed_amount',
        'description',
        'is_active',
        'effective_date',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('effective_date', '<=', $date);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeByCountry($query, $country = 'Indonesia')
    {
        return $query->where('country', $country);
    }
}