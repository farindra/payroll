<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type',
        'description',
        'amount',
        'installment_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'monthly_amount' => 'decimal:2',
        'installment_count' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('start_date', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
                    });
    }
}