<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'identity_number',
        'join_date',
        'status',
        'department_id',
        'position_id',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'status' => EmployeeStatus::class,
            'date_of_birth' => 'date',
            'join_date' => 'datetime',
        ];
    }

    /**
     * Get the department that owns the employee.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position that owns the employee.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the user associated with the employee.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Get the attendances for the employee.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leave requests for the employee.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the salaries for the employee.
     */
    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * Get the allowances for the employee.
     */
    public function allowances(): BelongsToMany
    {
        return $this->belongsToMany(Allowance::class, 'employee_allowances');
    }

    /**
     * Get the full name of the employee.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if employee is working.
     */
    public function isWorking(): bool
    {
        return $this->status === EmployeeStatus::WORKING;
    }

    /**
     * Check if employee has resigned.
     */
    public function hasResigned(): bool
    {
        return $this->status === EmployeeStatus::RESIGNED;
    }

    /**
     * Check if employee is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === EmployeeStatus::SUSPENDED;
    }
}
