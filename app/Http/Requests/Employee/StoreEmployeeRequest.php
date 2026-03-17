<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_code' => 'required|string|unique:employees,employee_code|max:50',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:0,1',
            'date_of_birth' => 'required|date|before:today',
            'phone' => 'required|string|max:20|unique:employees,phone',
            'email' => 'required|email|max:255|unique:employees,email',
            'address' => 'nullable|string|max:500',
            'identity_number' => 'required|string|max:20|unique:employees,identity_number',
            'join_date' => 'required|date',
            'status' => 'required|in:1,2,3',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_code.required' => 'Mã nhân viên là bắt buộc',
            'employee_code.unique' => 'Mã nhân viên đã tồn tại',
            'employee_code.max' => 'Mã nhân viên không được vượt quá 50 ký tự',
            'first_name.required' => 'Tên là bắt buộc',
            'first_name.max' => 'Tên không được vượt quá 100 ký tự',
            'last_name.required' => 'Họ là bắt buộc',
            'last_name.max' => 'Họ không được vượt quá 100 ký tự',
            'gender.required' => 'Giới tính là bắt buộc',
            'gender.in' => 'Giới tính không hợp lệ',
            'date_of_birth.required' => 'Ngày sinh là bắt buộc',
            'date_of_birth.date' => 'Ngày sinh không đúng định dạng',
            'date_of_birth.before' => 'Ngày sinh phải trước ngày hiện tại',
            'phone.required' => 'Số điện thoại là bắt buộc',
            'phone.unique' => 'Số điện thoại đã tồn tại',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã tồn tại',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự',
            'identity_number.required' => 'Số CMND/CCCD là bắt buộc',
            'identity_number.unique' => 'Số CMND/CCCD đã tồn tại',
            'identity_number.max' => 'Số CMND/CCCD không được vượt quá 20 ký tự',
            'join_date.required' => 'Ngày vào làm là bắt buộc',
            'join_date.date' => 'Ngày vào làm không đúng định dạng',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
            'department_id.required' => 'Phòng ban là bắt buộc',
            'department_id.exists' => 'Phòng ban không tồn tại',
            'position_id.required' => 'Chức vụ là bắt buộc',
            'position_id.exists' => 'Chức vụ không tồn tại',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
