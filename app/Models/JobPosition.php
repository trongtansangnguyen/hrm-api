<?php

namespace App\Models;

use App\Enums\JobPositionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'department_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobPositionStatus::class,
        ];
    }

    /**
     * Get the department that owns the job position.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the candidates for the job position.
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    /**
     * Check if job position is active.
     */
    public function isActive(): bool
    {
        return $this->status === JobPositionStatus::ACTIVE;
    }

    /**
     * Check if job position is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === JobPositionStatus::INACTIVE;
    }
}
