<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'category' => [Rule::requiredIf(!$this->id),'nullable', 'exists:categories,id'],
                'name' => [Rule::requiredIf(!$this->id),'nullable', 'string', 'min:3'],
                'quantity' => [Rule::requiredIf(!$this->id),'nullable', 'numeric', 'min:0'],
                'status' => ['nullable', 'numeric', Rule::in([Product::STATUS_UN_APPROVE, PRODUCT::STATUS_APPROVE, Product::STATUS_OUT_STOCK])],
                'price' => [Rule::requiredIf(!$this->id),'nullable', 'numeric', 'min:0'],
                'image' => ['nullable'],
                'image.*' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif'],
                'image_delete' => ['nullable', 'array'],
                'description' => ['nullable', 'string'],
                'note' => ['nullable', 'string'],
        ];
    }
    public function attributes(): array
    {
        return [
            'category' => 'tên danh mục',
            'name' => 'tên sản phẩm',
            'quantity' => 'số lượng',
            'status' => 'trạng thái',
            'price' => 'giá',
            'image' => 'hình ảnh',
            'image.*' => 'hình ảnh',
            'description' => 'mô tả',
            'note' => 'ghi chú'
        ];
    }
}
