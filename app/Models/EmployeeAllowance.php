<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'allowance_id',
    ];

    /**
     * Get the employee that owns the employee allowance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the allowance that owns the employee allowance.
     */
    public function allowance(): BelongsTo
    {
        return $this->belongsTo(Allowance::class);
    }
}
