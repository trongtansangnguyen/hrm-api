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
                'message' => 'Ban da check-in hom nay',
            ];
        }

        $networkValidation = $this->validateCompanyNetwork($ip);
        if ($networkValidation !== null) {
            return $networkValidation;
        }

        $locationValidation = $this->validateCompanyLocation($lat, $lng);
        if ($locationValidation !== null) {
            return $locationValidation;
        }

        $now = now();
        $lateAfterConfig = config('app.late_after');
        $lateAfter = $lateAfterConfig
            ? Carbon::parse(today()->toDateString().' '.$lateAfterConfig)
            : Carbon::parse(today()->toDateString().' 08:00:00');

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
            'message' => 'Check-in thanh cong',
            'status' => $status->label(),
        ];
    }

    public function checkOut(int $employeeId, float $lat, float $lng, ?string $ip): array
    {
        $attendance = Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereDate('date', today())
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'Ban chua check-in',
            ];
        }

        if ($attendance->check_out) {
            return [
                'success' => false,
                'message' => 'Ban da check-out',
            ];
        }

        if (!$attendance->check_in) {
            return [
                'success' => false,
                'message' => 'Ban ghi cham cong khong hop le',
            ];
        }

        $networkValidation = $this->validateCompanyNetwork($ip);
        if ($networkValidation !== null) {
            return $networkValidation;
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
            'message' => 'Check-out thanh cong',
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
                'message' => 'Chua cau hinh vi tri cong ty',
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
                'message' => 'Ban khong o trong khu vuc cong ty',
            ];
        }

        return null;
    }

    private function validateCompanyNetwork(?string $ip): ?array
    {
        $allowedNetworks = config('app.company_wifi_ips', []);

        if (empty($allowedNetworks)) {
            return null;
        }

        if (!$ip) {
            return [
                'success' => false,
                'message' => 'Khong xac dinh duoc IP thiet bi',
            ];
        }

        foreach ($allowedNetworks as $allowedNetwork) {
            if ($this->ipMatchesNetwork($ip, $allowedNetwork)) {
                return null;
            }
        }

        return [
            'success' => false,
            'message' => 'Ban khong ket noi dung mang cong ty',
        ];
    }

    private function ipMatchesNetwork(string $ip, string $allowedNetwork): bool
    {
        $allowedNetwork = trim($allowedNetwork);
        if ($allowedNetwork === '') {
            return false;
        }

        if (!str_contains($allowedNetwork, '/')) {
            return $ip === $allowedNetwork;
        }

        [$subnet, $prefix] = explode('/', $allowedNetwork, 2);
        $ipBinary = @inet_pton($ip);
        $subnetBinary = @inet_pton($subnet);

        if ($ipBinary === false || $subnetBinary === false || strlen($ipBinary) !== strlen($subnetBinary)) {
            return false;
        }

        $prefix = (int) $prefix;
        $maxPrefix = strlen($ipBinary) * 8;
        if ($prefix < 0 || $prefix > $maxPrefix) {
            return false;
        }

        $fullBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;

        if ($fullBytes > 0 && substr($ipBinary, 0, $fullBytes) !== substr($subnetBinary, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = ((0xFF << (8 - $remainingBits)) & 0xFF);

        return (ord($ipBinary[$fullBytes]) & $mask) === (ord($subnetBinary[$fullBytes]) & $mask);
    }
}
