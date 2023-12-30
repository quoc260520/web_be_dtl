<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectionRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'name' => [
                'required',
                Rule::unique('collections')->ignore($this->id)->where('deleted_at', null),
                'min:3',
                'max:100',
            ],
            'image' => 'nullable', 'string',
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'tên bộ sưu tập',
            'image' => 'ảnh'
        ];
    }
}
