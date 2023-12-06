<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderRepository extends BaseRepository
{
    const PAGE = 20;
    protected $model;
    protected $orderDetail;

    public function __construct(Order $model, OrderDetail $orderDetail)
    {
        $this->model = $model;
        $this->orderDetail = $orderDetail;
    }
    public function index($request, $userId = null)
    {
        $nameUser = $request->name_user;
        $nameProduct = $request->name_product;
        $paginate = $request->get('limit') ?? self::PAGE;
        $order = $this->model->when($nameUser, function ($query, $nameUser) {
            $query->whereHas('user', function (Builder $q) use ($nameUser) {
                $q->where('name', 'like', ['%' . $nameUser . '%']);
            });
        })->when($nameProduct, function ($query, $nameProduct) {
            $query->whereHas('orderDetails.product', function (Builder $q) use ($nameProduct) {
                $q->withTrashed()->where('name', 'like', ['%' . $nameProduct . '%']);
            });
        })->when($userId, function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['user:id,name,email', 'orderDetails.product.user:id,name,email','orderDetails.product' => function ($query) {
            return $query->withTrashed();
        }])
            ->paginate($paginate);
        return [
            'path_image' => asset('storage/product'),
            'order' => $order,
        ];
    }
    public function getById($id)
    {
        return $this->model->with(['user:id,name,email',
                'orderDetails.product.user:id,name,email',
                'orderDetails.product' => function ($query) {
            return $query->withTrashed();
        }])->findOrFail($id);
    }
    public function create($data)
    {
        $this->model->create([
            'category_id' => $data->category,
            'user_id' => Auth::user()->id,
            'name' =>  $data->name,
            'quantity' =>  $data->quantity,
            'status' =>  Auth::user()->hasRole('admin') ? Product::STATUS_APPROVE : PRODUCT::STATUS_UN_APPROVE,
            'price' =>  $data->price,
            'image' =>  json_encode($data->image),
            'description' =>  $data->description,
            'note' =>  $data->note,
        ]);
        return [
            'message' => 'Thêm sản phẩm thành công',
        ];
    }
    public function update($id, $data)
    {
        $product =  $this->model->find($id);
        if (!$product) {
            return [
                'errors' => 'Not found',
                'message' => 'Sản phẩm không tồn tại',
            ];
        }
        $product->update([
            'category_id' => $data->category ?? $product->category_id,
            'name' =>  $data->name ??  $product->name,
            'quantity' =>  $data->quantity  ??  $product->name,
            'status' =>  $data->category,
            'price' =>  $data->price  ??  $product->price,
            'image' =>  json_encode($data->image) ?? $product->image,
            'description' =>  $data->description ?? $product->description,
            'note' =>  $data->note ?? $product->note,
        ]);
        return [
            'message' => 'Cập nhật sản phẩm thành công',
        ];
    }
    public function delete($id)
    {
        $product =  $this->model->find($id);
        if (!$product) {
            return [
                'errors' => 'Not found',
                'message' => 'Sản phẩm không tồn tại',
            ];
        }
        if ($product->image) {
            foreach (json_decode($product->image) as $image) {
                $this->deleteImage('product', $image);
            }
        }
        $product->delete();
        return [
            'message' => 'Xóa sản phẩm thành công',
        ];
    }
    public function changeStatus($data)
    {
        $this->model->whereIn('id', $data->products)->update(['status' => $data->status]);
        return [
            'message' => 'Success',
        ];
    }
}
