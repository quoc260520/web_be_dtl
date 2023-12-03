<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userRepository;
    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }
    public function index(Request $request){
        return $this->responseData($this->userRepository->getAll($request));
    }
    public function getById($id){
        return $this->resultResponse($this->userRepository->getById($id)->toArray());

    }
    public function create(UserRequest $request){
        return $this->resultResponse($this->userRepository->create($request));
    }


    public function update(UserRequest $request, $id){
        return $this->resultResponse($this->userRepository->update($id, $request));
    }
    public function delete($id){
        return $this->resultResponse($this->userRepository->delete($id));
    }
}
