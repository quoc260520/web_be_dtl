<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderRepository;
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    public function index(Request $request) {
        return $this->resultResponse($this->orderRepository->index($request));
    }
    public function getByUser(Request $request) {
        return $this->resultResponse($this->orderRepository->index($request, Auth::user()->id));
    }
    public function getById(Request $request, $id) {
        return $this->resultResponse($this->orderRepository->getById($id)->toArray());
    }
    public function create(OrderRequest $request) {
        return $this->resultResponse($this->orderRepository->create($request));
    }
    public function update(Request $request, $id) {
        return $this->resultResponse($this->orderRepository->update($id, $request));

    }
    public function delete(Request $request, $id) {
        return $this->resultResponse($this->orderRepository->delete($id));

    }
}
