<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository
{
    const PAGE = 20;
    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }
    public function getAll($request)
    {
        $category = $request->category;
        $paginate = $request->get('limit') ?? self::PAGE;
        $categories = $this->model->when($category, function ($query) use ($category) {
            $query->where('name', 'like', ['%' . $category . '%']);
        })->paginate($paginate);
        return [
            'path_image' => asset('storage/category'),
            'category' => $category,
            'categories' => $categories
        ];
    }
    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }
    public function create($data)
    {
        $image = "";
        if ($data->hasFile('image')) {
            $image =  $this->uploadImage('category', $data->file('image'));
        }
        $this->model->create([
            'name'  => $data->name,
            'image' => $image
        ]);
        return [
            'message' => 'Thêm loại sản phẩm thành công',
        ];
    }
    public function update($id, $data)
    {
        $category =  $this->model->find($id);
        $image = $category->image;
        if ($data->hasFile('image')) {
            $image = $this->deleteImage('category', $image);
            $image =  $this->uploadImage('category', $data->file('image'));
        }
        if (!$category) {
            return [
                'errors' => 'Not found',
                'message' => 'Loại sản phẩm không tồn tại',
            ];
        }
        $category->update([
            'name'  => $data->name,
            'image' => $image
        ]);
        return [
            'message' => 'Cập nhật loại sản phẩm thành công',
        ];
    }
    public function delete($id)
    {
        $category =  $this->model->find($id);
        if (!$category) {
            return [
                'errors' => 'Not found',
                'message' => 'Loại sản phẩm không tồn tại',
            ];
        }
        if ($category->image) {
            $image =  $this->deleteImage('category', $category->image);
            $category->update([
                'image' => ""
            ]);
        }
        $category->delete();
        return [
            'message' => 'Xóa loại sản phẩm thành công',
        ];
    }
}
