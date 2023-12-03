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
    public function getAll($request)
    {
        $name = $request->name;
        $paginate = $request->get('limit') ?? self::PAGE;
        $products = $this->model->when($name, function ($query) use ($name) {
            $query->where('name', 'like', ['%'.$name.'%']);
        })
        ->with('user:id,name')
        ->paginate($paginate);
        return [
            'name' => $name,
            'categories' => $products
        ];
    }
    public function getById($id)
    {
        return $this->model->with('user:id,name')->findOrFail($id);
    }
    public function create($data) {
        dd(asset('storage/product/q8fU9H1701613289.png'));
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
            'status' =>  $data->status,
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
        $category =  $this->model->find($id);
        if(!$category) {
            return [
                'errors' => 'Not found',
                'message' => 'sản phẩm không tồn tại',
            ];
        }
        $category->update([
            'name'  => $data->name,
        ]);
        return [
            'message' => 'Cập nhật sản phẩm thành công',
        ];
    }
    public function delete($id) {
        $category =  $this->model->find($id);
        if(!$category) {
            return [
                'errors' => 'Not found',
                'message' => 'sản phẩm không tồn tại',
            ];
        }
        $category->delete();
        return [
            'message' => 'Xóa sản phẩm thành công',
        ];
    }
}
