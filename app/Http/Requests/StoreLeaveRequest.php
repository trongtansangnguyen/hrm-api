<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;   // Bạn có thể thay đổi sau nếu cần phân quyền
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'from_date' => 'required|date|after_or_equal:today',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'reason'    => 'required|string|min:10|max:500',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'from_date.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'to_date.after_or_equal'   => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'reason.min'               => 'Lý do phải có ít nhất 10 ký tự.',
        ];
    }
}