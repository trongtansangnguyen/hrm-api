<?php

namespace App\Models;

use App\Enums\LeaveRequestStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'from_date',
        'to_date',
        'reason',
        'status',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeaveRequestStatus::class,
            'from_date' => 'date',
            'to_date' => 'date',
            'approved_by' => 'integer',
        ];
    }

    /**
     * Get the employee that owns the leave request.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved the leave request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if leave request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === LeaveRequestStatus::PENDING;
    }

    /**
     * Check if leave request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === LeaveRequestStatus::APPROVED;
    }

    /**
     * Check if leave request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === LeaveRequestStatus::REJECTED;
    }

    /**
     * Get the number of days for the leave request.
     */
    public function getDaysAttribute(): int
    {
        $fromDate = Carbon::parse($this->from_date);
        $toDate = Carbon::parse($this->to_date);
        return $fromDate->diffInDays($toDate) + 1;
    }
}
