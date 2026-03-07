<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'base_salary',
        'overtime',
        'allowance',
        'bonus',
        'deduction',
        'net_salary',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'overtime' => 'decimal:2',
            'allowance' => 'decimal:2',
            'bonus' => 'decimal:2',
            'deduction' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    /**
     * Get the employee that owns the salary.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate net salary.
     */
    public function calculateNetSalary(): float
    {
        return $this->base_salary + $this->overtime + $this->allowance + $this->bonus - $this->deduction;
    }
}
