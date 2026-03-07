<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'level',
        'base_salary',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'level' => 'integer',
        ];
    }

    /**
     * Get the employees for the position.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
