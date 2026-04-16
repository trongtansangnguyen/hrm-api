<?php

namespace App\Services;

use App\Repositories\EmployeeRepository;

class EmployeeService
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository
    ) {
    }

    public function getEmployeeCount(): int
    {
        return $this->employeeRepository->count();
    }
}
