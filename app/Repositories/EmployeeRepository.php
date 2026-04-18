<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Repositories\IRepository;

class EmployeeRepository implements IRepository
{
    public function count(): int
    {
        return Employee::query()->count();
    }
}
