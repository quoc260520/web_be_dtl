<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productRepository;
    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }
    public function index(Request $request){
        return $this->responseData($this->productRepository->getAll($request));
    }
    public function getById($id){
        return $this->resultResponse($this->productRepository->getById($id)->toArray());

    }
    public function create(ProductRequest $request){
        return $this->resultResponse($this->productRepository->create($request));
    }


    public function update(ProductRequest $request, $id){
        return $this->resultResponse($this->productRepository->update($id, $request));
    }
    public function delete($id){
        return $this->resultResponse($this->productRepository->delete($id));
    }
}
