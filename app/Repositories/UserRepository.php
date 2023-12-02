<?php

namespace App\Repositories;

use App\Mail\ResetPasswordMail;
use App\Models\Cart;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserRepository extends BaseRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }
    public function getAll($request)
    {
    }
    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }
    public function createUser($data) {
        try {
            DB::beginTransaction();
            $user = $this->model->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->password),
            ]);
            $user->assignRole(User::ROLE_CLIENT);
            Cart::create([
                'user_id' => $user->id,
            ]);
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
        }

    }
    public function sendMailResetPassword($email) {
        $user = $this->model->where('email',$email)->first();
        $token = rand(100000,999999);
        PasswordResetToken::where('email',$user->email)->delete();
        PasswordResetToken::create(
            [
                'email' => $email,
                'token'=> $token,
                'created_at' => time()
            ]
        );
        Mail::to($email)->send(new ResetPasswordMail($token));
        return $user;
    }
    public function checkToken($email, $token) {
        $token = PasswordResetToken::where('email',$email)->where('token',$token)->first();
        if(!$token) {
            return [
                'code' => 400,
                'errors' => true,
                'message' => 'Mã đặt lại mật khẩu không hợp lệ'
            ];
        }
        $now = Carbon::now();
        $startTime = Carbon::parse($token->created_at);
        if ( $startTime->diffInHours($now) > 12) {
            return [
                'code' => 400,
                'errors' => true,
                'message' => 'Mã đặt lại mật khẩu đã hết hạn'
            ];
        }
        return [
            'code' => 200,
            'message' => 'Thành công'
        ];
    }
    public function resetPassword($email, $token, $password) {
        $check = $this->checkToken($email, $token);
        if($check['code'] !== 200) {
            return [
                'code' => 400,
                'errors' => true,
                'message' => $check['message']
            ];
        }
        $user = $this->model->where('email',$email)->update(['password' => Hash::make($password)]);
        PasswordResetToken::where('email',$email)->delete();
        return [
            'code' => 200,
            'message' => 'Đặt lại mật khẩu công'
        ];;
    }
}
