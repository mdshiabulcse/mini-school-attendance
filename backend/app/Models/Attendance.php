<?php
// app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'status',
        'note',
        'recorded_by'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getStatusColorAttribute()
    {
        return [
            'present' => 'success',
            'absent' => 'error',
            'late' => 'warning',
            'half_day' => 'info'
        ][$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('date', now()->year)
            ->whereMonth('date', now()->month);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
