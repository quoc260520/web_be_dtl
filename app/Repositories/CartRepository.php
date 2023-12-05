<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CartRepository extends BaseRepository
{
    const PAGE = 20;
    protected $model;
    protected $cartDetail;

    public function __construct(Cart $model, CartDetail $cartDetail)
    {
        $this->model = $model;
        $this->cartDetail = $cartDetail;
    }
    public function index($request)
    {
        $cart = $this->model->where('user_id', Auth::user()->id)->with([
            'cartDetails.product',
           ])->get();
        return [
            'path_image' => asset('storage/product'),
            'cart' => $cart
        ];
    }
    public function add($data) {
        $cart = $this->model->where('user_id', Auth::user()->id)->first();
        $product = Product::find($data->product);
        if(!$product || $product->status == Product::STATUS_UN_APPROVE || $product->status == Product::STATUS_OUT_STOCK) {
            return [
                'errors' => 'Not Found',
                'message' => 'Sản phẩm không tồn tại'
            ];
        }
        $check = $this->cartDetail->where('cart_id', $cart->id)->where('product_id', $product->id)->first();
        if($check) {
            return $this->update($check->id, $data, true);
        }
        if($data->quantity > $product->quantity) {
            return [
                'errors' => 'Errors',
                'message' => 'Số lương sản phẩm không đủ'
            ];
        }
        $cart->cartDetails()->create([
            'product_id' => $data->product,
            'quantity' =>  $data->quantity,
        ]);
        return [
            'message' => 'Thêm giỏ hàng thành công',
        ];
    }
    public function update($id, $data, $fromAdd = false) {
        $product = Product::find($data->product);
        if(!$product || $product->status == Product::STATUS_UN_APPROVE || $product->status == Product::STATUS_OUT_STOCK) {
            return [
                'errors' => 'Not Found',
                'message' => 'Sản phẩm không tồn tại'
            ];
        }
        if($data->quantity + ($fromAdd ? 1 : 0) > $product->quantity) {
            return [
                'errors' => 'Errors',
                'message' => 'Số lương sản phẩm không đủ'
            ];
        }
        $cartDetail = $this->cartDetail->find($id);
        if(!$cartDetail) {
            return [
                'errors' => 'Errors',
                'message' => 'Sản phẩm không tồn tại trong giỏ hàng'
            ];
        }
        if($fromAdd) {
            $cartDetail->increment('quantity');
        } else {
            $cartDetail->update([
                'quantity' =>  $data->quantity,
            ]);
        }
        return [
            'message' => 'Ok',
        ];
    }
    public function delete($id) {
        $cartDetail =  $this->cartDetail->find($id);
        if(!$cartDetail) {
            return [
                'errors' => 'Not found',
                'message' => 'Đã có lôi xảy ra',
            ];
        }
        $cartDetail->delete();
        return [
            'message' => 'Ok',
        ];
    }
}
