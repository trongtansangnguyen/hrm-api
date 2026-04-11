<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Enums\LeaveRequestStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestService
{
    /**
     * Tạo đơn nghỉ phép mới
     */
    public function createLeaveRequest(Employee $employee, array $data): LeaveRequest
    {
        // Kiểm tra trùng lịch nghỉ
        $exists = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', '!=', LeaveRequestStatus::REJECTED)
            ->where(function ($query) use ($data) {
                $query->whereBetween('from_date', [$data['from_date'], $data['to_date']])
                      ->orWhereBetween('to_date', [$data['from_date'], $data['to_date']]);
            })
            ->exists();

        if ($exists) {
            throw new \Exception('Bạn đã có đơn nghỉ phép trùng với khoảng thời gian này.');
        }

        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'from_date'   => $data['from_date'],
            'to_date'     => $data['to_date'],
            'reason'      => $data['reason'],
            'status'      => LeaveRequestStatus::PENDING,
        ]);
    }

   public function updateLeaveRequest(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        //  Chỉ cho sửa khi PENDING
        if ($leaveRequest->status !== LeaveRequestStatus::PENDING) {
            throw new \Exception('Chỉ có thể chỉnh sửa đơn khi đang chờ duyệt.');
        }

        // Kiểm tra trùng lịch (bỏ qua chính nó)
        $exists = LeaveRequest::where('employee_id', $leaveRequest->employee_id)
            ->where('id', '!=', $leaveRequest->id)
            ->where('status', '!=', LeaveRequestStatus::REJECTED)
            ->where(function ($query) use ($data) {
                $query->whereBetween('from_date', [$data['from_date'], $data['to_date']])
                    ->orWhereBetween('to_date', [$data['from_date'], $data['to_date']]);
            })
            ->exists();

        if ($exists) {
            throw new \Exception('Bạn đã có đơn nghỉ phép trùng thời gian.');
        }

        // Không cần đổi status nữa vì luôn là PENDING
        $leaveRequest->update([
            'from_date' => $data['from_date'],
            'to_date'   => $data['to_date'],
            'reason'    => $data['reason'],
        ]);

        return $leaveRequest;
    }

    /**
     * Phê duyệt đơn nghỉ phép (Chỉ Manager mới gọi hàm này)
     */
    public function approve(LeaveRequest $leaveRequest, Employee $approver): bool
    {
        if (!$leaveRequest->isPending()) {
            throw new \Exception('Đơn này đã được xử lý trước đó.');
        }

        return DB::transaction(function () use ($leaveRequest, $approver) {
            $leaveRequest->update([
                'status'       => LeaveRequestStatus::APPROVED,
                'approved_by'  => $approver->id,
            ]);

            return true;
        });
    }

    /**
     * Từ chối đơn nghỉ phép
     */
    public function reject(LeaveRequest $leaveRequest, Employee $approver): bool
    {
        if (!$leaveRequest->isPending()) {
            throw new \Exception('Đơn này đã được xử lý trước đó.');
        }

        $leaveRequest->update([
            'status'       => LeaveRequestStatus::REJECTED,
            'approved_by'  => $approver->id,
        ]);

        return true;
    }
}