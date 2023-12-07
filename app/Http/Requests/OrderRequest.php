<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Validation\Rule;

class OrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            "phone" => "required|regex:/(0)[0-9]/|not_regex:/[a-z]/|digits:10",
            "cart_detail" => "required",
            "cart_detail.*" => "required|exists:cart_details,id",
            "kind_of_payment" => ['required', Rule::in(Order::KIND_MANUAL, Order::KIND_PAYPAL)],
            "address" => "required|string",
        ];
    }
    public function attributes(): array
    {
        return [
            "phone" => "số điện thoại",
            "kind_of_payment" => "phương thức thanh toán",
            "address" => "địa chỉ",
            "cart_detail" => "giỏ hàng",
            "cart_detail.*" => "giỏ hàng",
        ];
    }
}
