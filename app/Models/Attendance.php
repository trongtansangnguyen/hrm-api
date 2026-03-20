<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
    'employee_id',
    'check_in',
    'check_out',
    'working_hours',
    'date',
    'status',
    'latitude_in',
    'longitude_in',
    'latitude_out',
    'longitude_out'
];

    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'date' => 'date',
            'working_hours' => 'decimal:2',
        ];
    }

    /**
     * Get the employee that owns the attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if attendance is on time.
     */
    public function isOnTime(): bool
    {
        return $this->status === AttendanceStatus::ON_TIME;
    }

    /**
     * Check if attendance is late.
     */
    public function isLate(): bool
    {
        return $this->status === AttendanceStatus::LATE;
    }

    /**
     * Check if employee was absent.
     */
    public function isAbsent(): bool
    {
        return $this->status === AttendanceStatus::ABSENT;
    }
}
