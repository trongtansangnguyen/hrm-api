<?php

namespace App\Enums;

enum UserRole: int
{
    case ADMIN = 1;
    case MANAGER = 2;
    case EMPLOYEE = 3;

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::EMPLOYEE => 'Employee',
        };
    }
}
