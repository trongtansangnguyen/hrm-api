<?php

namespace App\Enums;

enum LeaveRequestStatus: int
{
    case PENDING = 1;
    case APPROVED = 2;
    case REJECTED = 3;

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}
