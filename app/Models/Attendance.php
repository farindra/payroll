<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'break_hours',
        'total_hours',
        'overtime_hours',
        'status',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'break_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function getWorkingHoursAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            $total = $this->clock_out->diffInHours($this->clock_in);
            return max(0, $total - $this->break_hours);
        }
        return 0;
    }
}