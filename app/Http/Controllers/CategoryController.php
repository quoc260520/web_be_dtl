<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryRepository;
    public function __construct(CategoryRepository $categoryRepository) {
        $this->categoryRepository = $categoryRepository;
    }
    public function index(Request $request){
        return $this->responseData($this->categoryRepository->getAll($request));
    }
    public function getById($id){
        return $this->resultResponse($this->categoryRepository->getById($id)->toArray());

    }
    public function create(CategoryRequest $request){
        return $this->resultResponse($this->categoryRepository->create($request));
    }


    public function update(CategoryRequest $request, $id){
        return $this->resultResponse($this->categoryRepository->update($id, $request));
    }
    public function delete($id){
        return $this->resultResponse($this->categoryRepository->delete($id));
    }
}
