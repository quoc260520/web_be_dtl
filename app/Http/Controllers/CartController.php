<?php

namespace App\Http\Controllers;

use App\Repositories\CartRepository;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartRepository;
    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }
    public function index(Request $request) {

    }
    public function add(Request $request) {
        return $this->resultResponse($this->cartRepository->add($request));
    }
    public function update(Request $request, $id) {
        return $this->resultResponse($this->cartRepository->update($id, $request));

    }
    public function delete(Request $request, $id) {
        return $this->resultResponse($this->cartRepository->delete($id));

    }
}
