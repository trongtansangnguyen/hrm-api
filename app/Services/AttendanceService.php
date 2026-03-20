<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Helpers\GPSHelper;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceService
{
    public function checkIn(int $employeeId, float $lat, float $lng, ?string $ip, ?string $deviceId): array
    {
        $exists = Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', today())
            ->exists();

        if ($exists) {
            return [
                'success' => false,
                'message' => 'Bạn đã check-in hôm nay',
            ];
        }

        $locationValidation = $this->validateCompanyLocation($lat, $lng);
        if ($locationValidation !== null) {
            return $locationValidation;
        }

        $now = now();
        $lateAfterConfig = config('app.late_after');
        $lateAfter = $lateAfterConfig
            ? Carbon::parse(today()->toDateString() . ' ' . $lateAfterConfig)
            : Carbon::parse(today()->toDateString() . ' 08:00:00');

        $status = $now->gt($lateAfter)
            ? AttendanceStatus::LATE
            : AttendanceStatus::ON_TIME;

        Attendance::create([
            'employee_id' => $employeeId,
            'check_in' => $now,
            'latitude_in' => $lat,
            'longitude_in' => $lng,
            'date' => $now->toDateString(),
            'status' => $status,
            'ip_address' => $ip,
            'device_id' => $deviceId,
        ]);

        return [
            'success' => true,
            'message' => 'Check-in thành công',
            'status' => $status->label(),
        ];
    }

    public function checkOut(int $employeeId, float $lat, float $lng): array
    {
        $attendance = Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', today())
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'Bạn chưa check-in',
            ];
        }

        if ($attendance->check_out) {
            return [
                'success' => false,
                'message' => 'Bạn đã check-out',
            ];
        }

        if (!$attendance->check_in) {
            return [
                'success' => false,
                'message' => 'Bản ghi chấm công không hợp lệ',
            ];
        }

        $locationValidation = $this->validateCompanyLocation($lat, $lng);
        if ($locationValidation !== null) {
            return $locationValidation;
        }

        $now = now();
        $hours = $attendance->check_in->diffInMinutes($now) / 60;

        $attendance->update([
            'check_out' => $now,
            'latitude_out' => $lat,
            'longitude_out' => $lng,
            'working_hours' => round($hours, 2),
        ]);

        return [
            'success' => true,
            'message' => 'Check-out thành công',
            'working_hours' => round($hours, 2),
        ];
    }

    public function autoAbsent(): void
    {
        $today = today();
        $employeeIds = Employee::query()->pluck('id');

        foreach ($employeeIds as $id) {
            $exists = Attendance::query()
                ->where('employee_id', $id)
                ->whereDate('date', $today)
                ->exists();

            if ($exists) {
                continue;
            }

            Attendance::create([
                'employee_id' => $id,
                'date' => $today,
                'status' => AttendanceStatus::ABSENT,
            ]);
        }
    }

    private function validateCompanyLocation(float $lat, float $lng): ?array
    {
        $companyLat = config('app.company_lat');
        $companyLng = config('app.company_lng');
        $radius = config('app.company_radius');

        if ($companyLat === null || $companyLng === null || $radius === null) {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình vị trí công ty',
            ];
        }

        $distance = GPSHelper::distance(
            (float) $companyLat,
            (float) $companyLng,
            $lat,
            $lng
        );

        if ($distance > (float) $radius) {
            return [
                'success' => false,
                'message' => 'Bạn không ở trong khu vực công ty',
            ];
        }

        return null;
    }
}
