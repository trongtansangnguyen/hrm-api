<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService
    ) {
    }

    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $employeeId = $request->user()?->employee_id;
        if (!$employeeId) {
            return $this->errorResponse('Tài khoản chưa liên kết nhân viên', 422);
        }

        $result = $this->attendanceService->checkIn(
            $employeeId,
            (float) $request->latitude,
            (float) $request->longitude,
            $request->ip(),
            $request->header('X-Device-Id')
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result, $result['message']);
    }

    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $employeeId = $request->user()?->employee_id;
        if (!$employeeId) {
            return $this->errorResponse('Tài khoản chưa liên kết nhân viên', 422);
        }

        $result = $this->attendanceService->checkOut(
            $employeeId,
            (float) $request->latitude,
            (float) $request->longitude
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result, $result['message']);
    }

    public function today(Request $request): JsonResponse
    {
        $employeeId = $request->user()?->employee_id;
        if (!$employeeId) {
            return $this->errorResponse('Tài khoản chưa liên kết nhân viên', 422);
        }

        $attendance = $request->user()
            ->employee
            ?->attendances()
            ->whereDate('date', today())
            ->first();

        return $this->successResponse($attendance);
    }
}
