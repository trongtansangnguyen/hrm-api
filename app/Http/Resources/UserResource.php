<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'email' => $this->email,
            'role' => $this->role,
            'role_name' => $this->getRoleName(),
            'status' => $this->status,
            'status_name' => $this->getStatusName(),
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get role name
     */
    private function getRoleName(): string
    {
        return match($this->role) {
            1 => 'Admin',
            2 => 'Manager',
            3 => 'Employee',
            default => 'Unknown',
        };
    }

    /**
     * Get status name
     */
    private function getStatusName(): string
    {
        return match($this->status) {
            0 => 'Inactive',
            1 => 'Active',
            default => 'Unknown',
        };
    }
}
