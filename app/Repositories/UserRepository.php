<?php

namespace App\Repositories;

use App\Http\Resources\UserCollection;
use App\Mail\ResetPasswordMail;
use App\Models\Cart;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserRepository extends BaseRepository
{
    const PAGE = 20;
    protected $model;
    public function __construct(User $model)
    {
        $this->model = $model;
    }
    public function getAll($request)
    {
        $name = $request->name;
        $email = $request->email;
        $paginate = $request->get('limit') ?? self::PAGE;
        $users = $this->model->with('roles:name')
            ->when($name, function ($query) use ($name) {
                $query->where('name', 'like', ['%' . $name . '%']);
            })->when($email, function ($query) use ($email) {
                $query->where('email', 'like', ['%' . $email . '%']);
            })->paginate($paginate);
        return [
            'name' => $name,
            'email' => $email,
            'users' => $users
        ];
    }
    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }
    public function me()
    {
        $user = $this->model->with(['cart:id,user_id', 'cart.cartDetails'])->findOrFail(Auth::user()->id);
        $totalProductCart = $user->cart->cartDetails->sum('quantity');
        return ['user' => $user->toArray(), 'totalProductCart' => $totalProductCart];
    }
    public function createUser($data)
    {
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
    public function sendMailResetPassword($email)
    {
        $user = $this->model->where('email', $email)->first();
        $token = rand(100000, 999999);
        PasswordResetToken::where('email', $user->email)->delete();
        PasswordResetToken::create(
            [
                'email' => $email,
                'token' => $token,
                'created_at' => time()
            ]
        );
        Mail::to($email)->send(new ResetPasswordMail($token));
        return $user;
    }
    public function checkToken($email, $token)
    {
        $token = PasswordResetToken::where('email', $email)->where('token', $token)->first();
        if (!$token) {
            return [
                'code' => 400,
                'errors' => true,
                'message' => 'Mã đặt lại mật khẩu không hợp lệ'
            ];
        }
        $now = Carbon::now();
        $startTime = Carbon::parse($token->created_at);
        if ($startTime->diffInHours($now) > 12) {
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
    public function resetPassword($email, $token, $password)
    {
        $check = $this->checkToken($email, $token);
        if ($check['code'] !== 200) {
            return [
                'code' => 400,
                'errors' => true,
                'message' => $check['message']
            ];
        }
        $user = $this->model->where('email', $email)->update(['password' => Hash::make($password)]);
        PasswordResetToken::where('email', $email)->delete();
        return [
            'code' => 200,
            'message' => 'Đặt lại mật khẩu công'
        ];;
    }
    public function create($data)
    {
        try {
            DB::beginTransaction();
            $userSoft = $this->model->onlyTrashed()->where('email', $data->email)->first();
            if ($userSoft) {
                $userSoft->cart()->forceDelete();
                $userSoft->forceDelete();
            }
            $user = $this->model->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'address' => $data->address,
            ]);
            $user->assignRole($data->role);
            Cart::create([
                'user_id' => $user->id,
            ]);
            DB::commit();
            return [
                'message' => 'Thêm người dùng thành công',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'errors' => true,
                'message' => 'Thêm người dùng không thành công',
            ];
        }
    }
    public function update($id, $data)
    {
        $user =  $this->model->find($id);
        if (!$user) {
            return [
                'errors' => 'Not found',
                'message' => 'Người dùng không tồn tại',
            ];
        }
        $user->update([
            'name' => $data->name ?? $user->name,
            'email' => $data->email ?? $user->email,
            'address' => $data->address ?? $user->address,
        ]);
        $user->syncRoles([]);
        $user->assignRole($data->role);
        return [
            'message' => 'Cập nhật người dùng thành công',
        ];
    }

    public function delete($id)
    {
        $user =  $this->model->find($id);
        if (!$user) {
            return [
                'errors' => 'Not found',
                'message' => 'Người dùng không tồn tại',
            ];
        }
        $user->delete();
        return [
            'message' => 'Xóa người dùng thành công',
        ];
    }
}
