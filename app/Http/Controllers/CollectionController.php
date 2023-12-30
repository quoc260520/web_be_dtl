<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollectionRequest;
use App\Repositories\CollectionRepository;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    protected $collectionRepository;
    public function __construct(CollectionRepository $collectionRepository)
    {
        $this->collectionRepository = $collectionRepository;
    }
    public function index(Request $request)
    {
        return $this->responseData($this->collectionRepository->getAll($request));
    }
    public function getById($id)
    {
        return $this->resultResponse([
            'path_image' => asset('storage/collection'),
            'collection' => $this->collectionRepository->getById($id)->toArray()
        ]);
    }
    public function create(CollectionRequest $request)
    {
        return $this->resultResponse($this->collectionRepository->create($request));
    }


    public function update(CollectionRequest $request, $id)
    {
        return $this->resultResponse($this->collectionRepository->update($id, $request));
    }
    public function delete($id)
    {
        return $this->resultResponse($this->collectionRepository->delete($id));
    }
}
