<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $userRepository;
    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }
    public function register(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required|min:3',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:6|max:50',
                    'password_confirmation' => 'required|same:password'
                ],
                [],
                [
                    'name' => 'tên',
                    'email' => 'email',
                    'password' => 'mật khẩu',
                    'password_confirmation' => 'mật khẩu xác nhận'
                ],
            );

            if ($validateUser->fails()) {
                return $this->responseData( [
                    'code' => 401,
                    'errors' => 'validation error',
                    'message' => $validateUser->errors(),
                ]);
            }

            $user = $this->userRepository->createUser($request);

            return $this->responseData( [
                'code' => 200,
                'message' => 'Tạo người dùng thành công',
                'token' => $user->createToken('API TOKEN')->plainTextToken,
            ]);

        } catch (\Throwable $th) {
            return $this->errorResponse();
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ],[],
            [
                'email' => 'email',
                'password' => 'mật khẩu',
            ]);

            if ($validateUser->fails()) {
                return $this->responseData( [
                    'code' => 401,
                    'errors' => 'validation error',
                    'message' => $validateUser->errors(),
                ]);
            }

            if (!Auth::guard('web')->attempt($request->only(['email', 'password']))) {
                return $this->responseData( [
                    'code' => 400,
                    'message' => 'Email hoặc mật khẩu không chính xác',
                ]);
            }

            $user = User::where('email', $request->email)->first();
            return $this->responseData( [
                'code' => 200,
                'message' => 'Đăng nhập thành công',
                'data' => [
                    'user' => $user,
                    'token' => $user->createToken('API TOKEN')->plainTextToken,
                ]
            ]);
        } catch (\Throwable $th) {
            return $this->errorResponse();
        }
    }
    public function sendMailResetPassword(Request $request) {
        $validateUser = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ],[],
        [
            'email' => 'email',
        ]);

        if ($validateUser->fails()) {
            return $this->responseData( [
                'code' => 401,
                'errors' => 'validation error',
                'message' => $validateUser->errors(),
            ]);
        }
        try {
            $this->userRepository->sendMailResetPassword($request->email);
            return $this->responseData( [
                'code' => 200,
                'message' => 'Gửi mã thành công',
            ]);
        } catch (\Throwable $th) {
            return $this->errorResponse();
        }
    }
    public function checkToken(Request $request) {
        $validateUser = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|min:6',
        ],[],
        [
            'email' => 'email',
            'token' => 'token',
        ]);

        if ($validateUser->fails()) {
            return $this->responseData( [
                'code' => 401,
                'errors' => 'validation error',
                'message' => $validateUser->errors(),
            ]);
        }
        return $this->resultResponse($this->userRepository->checkToken($request->email, $request->token));
    }
    public function resetPassword(Request $request) {
        $validateUser = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|min:6',
            'password' => 'required|min:6|max:20',
        ],[],
        [
            'email' => 'email',
            'token' => 'token',
            'password' => 'mật khẩu',
        ]);

        if ($validateUser->fails()) {
            return $this->responseData( [
                'code' => 401,
                'errors' => 'validation error',
                'message' => $validateUser->errors(),
            ]);
        }
        return $this->resultResponse($this->userRepository->resetPassword($request->email, $request->token, $request->password));
    }
}
