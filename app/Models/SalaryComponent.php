<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'is_fixed',
        'formula_code',
        'default_amount',
        'is_percentage',
        'is_active',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
        'is_percentage' => 'boolean',
        'is_active' => 'boolean',
        'default_amount' => 'decimal:2',
    ];

    public function employeeComponents()
    {
        return $this->hasMany(EmployeeComponent::class);
    }
}