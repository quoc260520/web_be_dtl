<?php

namespace App\Repositories;

use App\Models\Collection;

class CollectionRepository extends BaseRepository
{
    const PAGE = 20;
    protected $model;

    public function __construct(Collection $model)
    {
        $this->model = $model;
    }
    public function getAll($request)
    {
        $collection = $request->collection;
        $paginate = $request->get('limit') ?? self::PAGE;
        $collections = $this->model->when($collection, function ($query) use ($collection) {
            $query->where('name', 'like', ['%' . $collection . '%']);
        })->paginate($paginate);
        return [
            'path_image' => asset('storage/collection'),
            'collection' => $collection,
            'collections' => $collections
        ];
    }
    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }
    public function create($data)
    {
        $this->model->create([
            'name'  => $data->name,
            'image' => $data->image
        ]);
        return [
            'message' => 'Thêm bộ sưu tập thành công',
        ];
    }
    public function update($id, $data)
    {
        $collection =  $this->model->find($id);
        if (!$collection) {
            return [
                'errors' => 'Not found',
                'message' => 'Bộ sưu tập không tồn tại',
            ];
        }
        $collection->update([
            'name'  => $data->name,
            'image' => $data->image
        ]);
        return [
            'message' => 'Cập nhật bộ sưu tập thành công',
        ];
    }
    public function delete($id)
    {
        $collection =  $this->model->find($id);
        if (!$collection) {
            return [
                'errors' => 'Not found',
                'message' => 'Bộ sưu tập không tồn tại',
            ];
        }
        if ($collection->image) {
            $image =  $this->deleteImage('collection', $collection->image);
            $collection->update([
                'image' => ""
            ]);
        }
        $collection->delete();
        return [
            'message' => 'Xóa loại sản phẩm thành công',
        ];
    }
}
