<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'component_id',
        'amount',
        'is_percentage',
        'effective_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_percentage' => 'boolean',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    public function getCalculatedAmountAttribute()
    {
        if ($this->is_percentage) {
            return $this->employee->basic_salary * ($this->amount / 100);
        }
        return $this->amount;
    }
}