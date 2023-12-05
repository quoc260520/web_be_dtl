<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends BaseRequest
{
    public function rules(): array
    {
        return [

            'name' => [
                'required',
                Rule::unique('categories')->ignore($this->id)->where('deleted_at', null),
                'min:3',
                'max:100',
            ],
            'image' => 'nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif',
        ];
    }
    public function attributes(): array
    {
        return [
            'name' => 'tên loại sản phẩm',
            'image' => 'ảnh'
        ];
    }
}
