<?php

namespace App\Enums;

enum EmployeeStatus: int
{
    case WORKING = 1;
    case RESIGNED = 2;
    case SUSPENDED = 3;

    public function label(): string
    {
        return match($this) {
            self::WORKING => 'Working',
            self::RESIGNED => 'Resigned',
            self::SUSPENDED => 'Suspended',
        };
    }
}
