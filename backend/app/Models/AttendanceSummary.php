<?php
// app/Models/AttendanceSummary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'month',
        'total_days',
        'present_days',
        'absent_days',
        'late_days',
        'half_days',
        'attendance_percentage'
    ];

    protected $casts = [
        'attendance_percentage' => 'decimal:2'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getPerformanceAttribute()
    {
        if ($this->attendance_percentage >= 90) return 'excellent';
        if ($this->attendance_percentage >= 75) return 'good';
        if ($this->attendance_percentage >= 60) return 'average';
        return 'poor';
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
