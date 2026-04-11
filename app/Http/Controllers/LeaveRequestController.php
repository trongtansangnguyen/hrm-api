<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveRequestService $leaveService
    ) {}

    /**
     * Hiển thị danh sách đơn nghỉ phép
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin nhân viên.'
            ], 403);
        }

        $query = LeaveRequest::with(['employee.department', 'employee.position', 'approver']);

        //  Employee → chỉ xem của mình
        if ($user->role?->value === 3) {
            $query->where('employee_id', $user->employee->id);
        }

        //  Manager/Admin → filter nâng cao
        else {

            //  Search theo tên nhân viên
            if ($request->filled('search')) {
                $search = $request->search;

                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                    });
                });
            }

            //  Lọc theo status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            //  Lọc theo phòng ban
            if ($request->filled('department_id')) {
                $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }

            //  Lọc theo vị trí
            if ($request->filled('position_id')) {
                $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('position_id', $request->position_id);
                });
            }
        }

        //  Pagination dynamic
        $perPage = $request->input('per_page', 15);

        $leaveRequests = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $leaveRequests
        ]);
    }

    /**
     * Nhân viên tạo đơn nghỉ phép
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin nhân viên.'
            ], 403);
        }

        try {
            $leaveRequest = $this->leaveService->createLeaveRequest(
                $user->employee, 
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Đơn nghỉ phép đã được gửi thành công. Vui lòng chờ phê duyệt.',
                'data'    => $leaveRequest->load('employee')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Xem chi tiết đơn nghỉ phép
     */
    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin nhân viên.'
            ], 403);
        }

        // Employee chỉ xem đơn của mình
        if ($user->role?->value === 3 && $leaveRequest->employee_id !== $user->employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem đơn này.'
            ], 403);
        }

        $leaveRequest->load(['employee', 'approver']);

        return response()->json([
            'success' => true,
            'data'    => $leaveRequest
        ]);
    }

    public function update(StoreLeaveRequest $request, LeaveRequest $leaveRequest): JsonResponse
{
    $user = Auth::user();

    if (!$user || !$user->employee) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy thông tin nhân viên.'
        ], 403);
    }

    // Chỉ cho sửa đơn của chính mình
    if ($leaveRequest->employee_id !== $user->employee->id) {
        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền chỉnh sửa đơn này.'
        ], 403);
    }

    try {
        $updated = $this->leaveService->updateLeaveRequest(
            $leaveRequest,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đơn nghỉ phép thành công.',
            'data'    => $updated->load('employee')
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }
}

    /**
     * Manager phê duyệt đơn
     */
    public function approve(LeaveRequest $leaveRequest): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin người phê duyệt.'
            ], 403);
        }

        try {
            $this->leaveService->approve($leaveRequest, $user->employee);

            return response()->json([
                'success' => true,
                'message' => 'Đơn nghỉ phép đã được phê duyệt thành công.',
                'data'    => $leaveRequest->fresh()->load(['employee', 'approver'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Manager từ chối đơn
     */
    public function reject(LeaveRequest $leaveRequest): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin người phê duyệt.'
            ], 403);
        }

        try {
            $this->leaveService->reject($leaveRequest, $user->employee);

            return response()->json([
                'success' => true,
                'message' => 'Đơn nghỉ phép đã bị từ chối.',
                'data'    => $leaveRequest->fresh()->load(['employee', 'approver'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}