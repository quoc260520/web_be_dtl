<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/check', function (Request $request) {
    return 'oke';
});

Route::middleware('auth:sanctum')->get('/me', [UserController::class, 'me'])->name('me');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/send-mail', [AuthController::class, 'sendMailResetPassword'])->name('auth.send_mail');
    Route::post('/check-token', [AuthController::class, 'checkToken'])->name('auth.check_token');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset_password');
});

Route::prefix('category')->group(function () {
    Route::get('/all', [CategoryController::class, 'index'])->name('category.list');
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/{id}', [CategoryController::class, 'getById'])->name('category.get_by_id');
        Route::post('/create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/update/{id}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/delete/{id}', [CategoryController::class, 'delete'])->name('category.delete');
    });
});

Route::prefix('user')->group(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/all', [UserController::class, 'index'])->name('user.list');
        Route::get('/{id}', [UserController::class, 'getById'])->name('user.get_by_id');
        Route::post('/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/update/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
    });
});

Route::prefix('product')->group(function () {
    Route::get('/all', [ProductController::class, 'index'])->name('product.list');
    Route::middleware(['auth:sanctum', 'role:admin|client'])->get('/by-user', [ProductController::class, 'productByUser'])->name('product.by_user');
    Route::get('/{id}', [ProductController::class, 'getById'])->name('product.get_by_id');
    Route::middleware(['auth:sanctum', 'role:admin|client'])->group(function () {
        Route::post('/create', [ProductController::class, 'create'])->name('product.create');
    });
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/update/{id}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/delete/{id}', [ProductController::class, 'delete'])->name('product.delete');
    });
});

Route::prefix('order')->group(function () {
    Route::get('/all', [ProductController::class, 'index'])->name('order.list');
    Route::get('/{id}', [ProductController::class, 'getById'])->name('order.get_by_id');
    Route::middleware(['auth:sanctum', 'role:admin|client'])->group(function () {
        Route::post('/create', [ProductController::class, 'create'])->name('order.create');
    });
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/update/{id}', [ProductController::class, 'update'])->name('order.update');
        Route::delete('/delete/{id}', [ProductController::class, 'delete'])->name('order.delete');
    });
});
