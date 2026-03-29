<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status');

        $query = User::with('employee');

        // Search by email or employee info
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by role
        if ($role) {
            $query->where('role', $role);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->successResponse([
            'users' => UserResource::collection($users->items()),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ], "Truy vấn thành công");
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        $user->load('employee');

        return $this->successResponse(
            new UserResource($user),
            'Tạo mới tài khoản thành công',
            201
        );
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->notFoundResponse("Không tìm thấy tài khoản với ID: $id");
        }

        $user->load('employee');

        return $this->successResponse(new UserResource($user));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->notFoundResponse("Không tìm thấy tài khoản với ID: $id");
        }

        $user->update($request->validated());
        $user->load('employee');

        return $this->successResponse(
            new UserResource($user),
            'Cập nhật tài khoản thành công'
        );
    }

    /**
     * Remove the specified user.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return $this->notFoundResponse("Xoá thất bại do ID tài khoản không tồn tại, ID: $id");
        }

        $user->delete();

        return $this->successResponse(null, 'Xóa tài khoản thành công');
    }
}
