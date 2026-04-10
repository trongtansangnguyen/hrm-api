<?php

namespace App\Events;

use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public EmployeeResource $employee;

    public function __construct(Employee $employee)
    {
        $this->employee = new EmployeeResource($employee->load(['department', 'position']));
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('public-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'EmployeeCreated';
    }
}
