<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeStatus;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\Employee;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.company_lat' => -6.2000000,
            'app.company_lng' => 106.8166667,
            'app.company_radius' => 1000,
            'app.company_wifi_ips' => [],
            'app.late_after' => '08:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_check_in_is_rejected_when_employee_has_already_checked_in_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-10 07:55:00'));

        $employee = $this->createEmployee();
        $service = app(AttendanceService::class);

        $firstAttempt = $service->checkIn($employee->id, -6.2000000, 106.8166667, '10.0.0.1', 'device-1');
        $secondAttempt = $service->checkIn($employee->id, -6.2000000, 106.8166667, '10.0.0.1', 'device-1');

        $this->assertTrue($firstAttempt['success']);
        $this->assertFalse($secondAttempt['success']);
        $this->assertSame('Ban da check-in hom nay', $secondAttempt['message']);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_check_out_is_rejected_after_it_has_already_been_completed(): void
    {
        $employee = $this->createEmployee();
        $attendance = Attendance::query()->create([
            'employee_id' => $employee->id,
            'check_in' => Carbon::parse('2026-04-10 08:00:00'),
            'date' => '2026-04-10',
            'status' => AttendanceStatus::ON_TIME,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-04-10 17:00:00'));

        $service = app(AttendanceService::class);

        $firstAttempt = $service->checkOut($employee->id, -6.2000000, 106.8166667, '10.0.0.1');
        $secondAttempt = $service->checkOut($employee->id, -6.2000000, 106.8166667, '10.0.0.1');

        $attendance->refresh();

        $this->assertTrue($firstAttempt['success']);
        $this->assertFalse($secondAttempt['success']);
        $this->assertSame('Ban da check-out', $secondAttempt['message']);
        $this->assertNotNull($attendance->check_out);
    }

    public function test_auto_absent_only_creates_one_record_per_employee_per_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-10 18:00:00'));

        $employee = $this->createEmployee();
        $service = app(AttendanceService::class);

        $service->autoAbsent();
        $service->autoAbsent();

        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'date' => '2026-04-10',
            'status' => AttendanceStatus::ABSENT->value,
        ]);
    }

    private function createEmployee(): Employee
    {
        return Employee::query()->create([
            'employee_code' => 'EMP-'.fake()->unique()->numerify('####'),
            'first_name' => 'Test',
            'last_name' => 'User',
            'gender' => 1,
            'date_of_birth' => '1990-01-01',
            'phone' => '08123456789',
            'email' => fake()->unique()->safeEmail(),
            'address' => 'Jakarta',
            'identity_number' => fake()->unique()->numerify('################'),
            'join_date' => '2024-01-01 08:00:00',
            'status' => EmployeeStatus::WORKING,
        ]);
    }
}
