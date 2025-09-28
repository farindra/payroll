<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'period_name',
        'start_date',
        'end_date',
        'payment_date',
        'status',
        'total_employees',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'total_employees' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    public function payrollDetails()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function updateTotals()
    {
        $this->total_employees = $this->payrollDetails()->count();
        $this->total_amount = $this->payrollDetails()->sum('net_salary');
        $this->save();
    }

    public function canBeProcessed()
    {
        return $this->status === 'Draft';
    }

    public function canBeEdited()
    {
        return $this->status === 'Draft';
    }
}