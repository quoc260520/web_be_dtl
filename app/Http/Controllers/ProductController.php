<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    protected $productRepository;
    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }
    public function index(Request $request){
        return $this->responseData($this->productRepository->getAll($request));
    }
    public function getTopOrder(Request $request){
        return $this->responseData($this->productRepository->getTopOrder($request));
    }
    public function getById($id){
        return $this->resultResponse(
            [
                'path_image' => asset('storage/product'),
                'product' => $this->productRepository->getById($id)->toArray()
            ]
        );
    }
    public function productByUser(Request $request){
        return $this->responseData($this->productRepository->getAll($request, Auth::user()->id));

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
    public function changeStatus(Request $request) {
        $validate = Validator::make($request->all(), [
            'status' => ['required', Rule::in(Product::STATUS_UN_APPROVE, Product::STATUS_APPROVE, Product::STATUS_OUT_STOCK)],
            'products' => 'required|array',
        ]);
        if ($validate->fails()) {
            return $this->responseData( [
                'code' => 401,
                'errors' => 'validation error',
                'message' => $validate->errors(),
            ]);
        }
        return $this->resultResponse($this->productRepository->changeStatus($request));
    }
    public function updateImage(Request $request){
        $validate = Validator::make($request->all(), [
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,gif'],
            'folder' => 'required|in:product,category,collection',
        ]);

        if ($validate->fails()) {
            return $this->responseData( [
                'code' => 401,
                'errors' => 'validation error',
                'message' => $validate->errors(),
            ]);
        }
        return $this->resultResponse($this->productRepository->uploadImg($request));
    }
    public function deleteImage(Request $request){
        $validate = Validator::make($request->all(), [
            'image' => ['required', 'string'],
            'folder' => 'required|in:product,category,collection',
        ]);
        if ($validate->fails()) {
            return $this->responseData( [
                'code' => 401,
                'errors' => 'validation error',
                'message' => $validate->errors(),
            ]);
        }
        return $this->resultResponse($this->productRepository->deleteImg($request));
    }
}
