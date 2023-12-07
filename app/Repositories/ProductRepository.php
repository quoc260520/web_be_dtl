<?php

namespace App\Repositories;

use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository
{
    const PAGE = 20;
    protected $model;
    protected $orderDetail;

    public function __construct(Product $model, OrderDetail $orderDetail)
    {
        $this->model = $model;
        $this->orderDetail = $orderDetail;
    }
    public function getAll($request, $userId = null)
    {
        $name = $request->name;
        $category = $request->category ?? [];
        $priceMin = $request->price_min;
        $priceMax = $request->price_max;
        $status = $request->status;
        $paginate = $request->get('limit') ?? self::PAGE;
        $products = $this->model->when($name, function ($query) use ($name) {
            $query->where('name', 'like', ['%' . $name . '%']);
        })->when($userId, function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->when(count($category), function ($query) use ($category) {
            $query->whereIn('category_id',$category);
        })->when($priceMin, function ($query) use ($priceMin) {
            $query->where('price', '>=', (int)$priceMin);
        })->when($priceMax, function ($query) use ($priceMax) {
            $query->where('price', '<=', (int)$priceMax);
        })->when(is_numeric($status), function ($query) use ($status) {
            $query->where('status', '=', (int)$status);
        })
            ->with('user:id,name', 'category:id,name')
            ->paginate($paginate);
        $minPrice =  $this->model->where('status', Product::STATUS_APPROVE)->min("price");
        $maxPrice =  $this->model->where('status', Product::STATUS_APPROVE)->max("price");
        return [
            'path_image' => asset('storage/product'),
            'status' => $status,
            'name' => $name,
            'category' => $category,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'products' => $products,
        ];
    }
    public function getTopOrder($request) {
        $collection = $this->orderDetail::groupBy('product_id')
                    ->selectRaw('count(*) as total, product_id')
                    ->orderBy('total','desc')
                    ->take(10)
                    ->pluck('product_id');
        $product = $this->model->whereIn('id',$collection->toArray())->with('user:id,name', 'category:id,name')
                    ->where('status', [Product::STATUS_APPROVE])
                    ->get();
            if(count($product) == 10) {
                return $product;
            } else {
                $productBonus = $this->model->whereNotIn('id',$collection->toArray())
                                ->where('status', [Product::STATUS_APPROVE])
                                ->with('user:id,name', 'category:id,name')
                                ->take(10 - count($product))->get();
                return array_merge($product->toArray(), $productBonus->toArray());
            }
    }
    public function getById($id)
    {
        return $this->model->with('user:id,name')->findOrFail($id);
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
    public function uploadImg($data)
    {
        if ($data->hasFile('image')) {
            $imageName =  $this->uploadImage($data->folder, $data->file('image'));
            return [
                'path_image' => asset('storage/' . $data->folder),
                'image' => $imageName
            ];
        }
        return [
            'error' => 'Error',
            'message' => 'Upload image failed',
        ];
    }
    public function deleteImg($data)
    {
        $this->deleteImage($data->folder, $data->image);
        return [
            'message' => 'Success',
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
