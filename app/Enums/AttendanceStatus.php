<?php

namespace App\Enums;

enum AttendanceStatus: int
{
    case ON_TIME = 1;
    case LATE = 2;
    case ABSENT = 3;

    public function label(): string
    {
        return match($this) {
            self::ON_TIME => 'On Time',
            self::LATE => 'Late',
            self::ABSENT => 'Absent',
        };
    }
}
