<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $status = $request->get('status');
        $departmentId = $request->get('department_id');
        $positionId = $request->get('position_id');

        $query = Employee::with(['department', 'position']);

        // Search by name, email, phone, employee_code
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by department
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // Filter by position
        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->successResponse([
            'employees' => EmployeeResource::collection($employees->items()),
            'pagination' => [
                'total' => $employees->total(),
                'per_page' => $employees->perPage(),
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'from' => $employees->firstItem(),
                'to' => $employees->lastItem(),
            ],
        ], "Truy vấn thành công");
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create($request->validated());
        $employee->load(['department', 'position']);

        return $this->successResponse(
            new EmployeeResource($employee),
            'Tạo mới nhân viên thành công',
            201
        );
    }

    /**
     * Display the specified employee.
     */
    public function show(int $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return $this->notFoundResponse("Không tìm thấy nhân viên với ID: $id");
        }

        $employee->load(['department', 'position']);

        return $this->successResponse(new EmployeeResource($employee));
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, int $id): JsonResponse
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return $this->notFoundResponse("Không tìm thấy nhân viên với ID: $id");
        }

        $employee->update($request->validated());
        $employee->load(['department', 'position']);

        return $this->successResponse(
            new EmployeeResource($employee),
            'Cập nhật thông tin nhân viên thành công'
        );
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(int $id): JsonResponse
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return $this->notFoundResponse("Xoá thất bại do ID nhân viên không tồn tại, ID: $id");
        }

        $employee->delete();

        return $this->successResponse(null, 'Xóa nhân viên thành công');
    }
}
