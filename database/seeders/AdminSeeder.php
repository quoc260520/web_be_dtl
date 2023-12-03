<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' =>  'admin@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('12345678')
            ]
        );
        $user->assignRole(config('auth.role_name.admin'));
        $cart = Cart::firstOrCreate(['user_id' =>  $user->id]);
    }
}
