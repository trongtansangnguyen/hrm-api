<?php

namespace App\Models;

use App\Enums\CandidateStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'cv_path',
        'job_position_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CandidateStatus::class,
        ];
    }

    /**
     * Get the job position that owns the candidate.
     */
    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    /**
     * Get the full name of the candidate.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if candidate has applied.
     */
    public function hasApplied(): bool
    {
        return $this->status === CandidateStatus::APPLIED;
    }

    /**
     * Check if candidate was interviewed.
     */
    public function wasInterviewed(): bool
    {
        return $this->status === CandidateStatus::INTERVIEWED;
    }

    /**
     * Check if candidate was hired.
     */
    public function wasHired(): bool
    {
        return $this->status === CandidateStatus::HIRED;
    }

    /**
     * Check if candidate was rejected.
     */
    public function wasRejected(): bool
    {
        return $this->status === CandidateStatus::REJECTED;
    }
}
