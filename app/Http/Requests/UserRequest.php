<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|min:3',
            'email' => ['required','email',Rule::unique('users')->ignore($this->id)->where('deleted_at', null)],
            'password' => [Rule::requiredIf(!$this->id),'nullable','min:6','max:50'],
            'address' => 'nullable|string',
            'role' => ['required', Rule::in([User::ROLE_ADMIN,User::ROLE_CLIENT])]
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'tên',
            'email' => 'email',
            'password' => 'mật khẩu',
            'address' => 'địa chỉ',
            'role' => 'quyền'
        ];
    }
}
