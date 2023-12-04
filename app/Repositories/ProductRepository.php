<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductRepository extends BaseRepository
{
    const PAGE = 20;
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }
    public function getAll($request, $userId = null)
    {
        $name = $request->name;
        $category = $request->category ?? [];
        $priceMin = $request->price_min;
        $priceMax = $request->price_max;
        $paginate = $request->get('limit') ?? self::PAGE;
        $products = $this->model->when($name, function ($query) use ($name) {
            $query->where('name', 'like', ['%'.$name.'%']);
        }) ->when($userId, function ($query) use ($userId) {
            $query->where('user_id',$userId);
        })->when(count($category), function ($query) use ($category) {
            $query->whereIn('category_id',$category);
        })->when($priceMin, function ($query) use ($priceMin) {
            $query->where('price','>=',$priceMin);
        })->when($priceMax, function ($query) use ($priceMax) {
            $query->where('price','=<',$priceMax);
        })
        ->with('user:id,name')
        ->paginate($paginate);
        return [
            'path_image' => asset('storage/product'),
            'name' => $name,
            'category' => $category,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'products' => $products
        ];
    }
    public function getById($id)
    {
        return $this->model->with('user:id,name')->findOrFail($id);
    }
    public function create($data) {
        $arrayImage = [];
        if ($data->hasFile('image')) {
            foreach ($data->file('image') as $file) {
                $arrayImage[] =  $this->uploadImage('product',$file);
            }
        }
        $this->model->create([
            'category_id' => $data->category,
            'user_id' => Auth::user()->id,
            'name' =>  $data->name,
            'quantity' =>  $data->quantity,
            'status' =>  Auth::user()->hasRole('admin') ? Product::STATUS_APPROVE : PRODUCT::STATUS_UN_APPROVE,
            'price' =>  $data->price,
            'image' =>  json_encode($arrayImage),
            'description' =>  $data->description,
            'note' =>  $data->note,
        ]);
        return [
            'message' => 'Thêm sản phẩm thành công',
        ];
    }
    public function update($id, $data) {
        if($data->image_delete) {
            foreach(($data->image_delete) as $image) {
                $this->deleteImage('product',$image);
            }
        }
        $product =  $this->model->find($id);
        if(!$product) {
            return [
                'errors' => 'Not found',
                'message' => 'Sản phẩm không tồn tại',
            ];
        }
        $arrayImage = array_diff(json_decode($product->image), $data->image_delete);
        if ($data->hasFile('image')) {
            foreach ($data->file('image') as $file) {
                $arrayImage[] =  $this->uploadImage('product',$file);
            }
        }
        $product->update([
            'category_id' => $data->category ?? $product->category_id,
            'name' =>  $data->name ??  $product->name,
            'quantity' =>  $data->quantity  ??  $product->name,
            'status' =>  $data->category,
            'price' =>  $data->price  ??  $product->price,
            'image' =>  json_encode($arrayImage),
            'description' =>  $data->description ?? $product->description,
            'note' =>  $data->note ?? $product->note,
        ]);
        return [
            'message' => 'Cập nhật sản phẩm thành công',
        ];
    }
    public function delete($id) {
        $product =  $this->model->find($id);
        if(!$product) {
            return [
                'errors' => 'Not found',
                'message' => 'Sản phẩm không tồn tại',
            ];
        }
        if($product->image) {
            foreach(json_decode($product->image) as $image) {
                $this->deleteImage('product',$image);
            }
        }
        $product->delete();
        return [
            'message' => 'Xóa sản phẩm thành công',
        ];
    }
}
