<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
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
    Route::get('/get-top-order', [ProductController::class, 'getTopOrder'])->name('product.get_top_order');
    Route::middleware(['auth:sanctum', 'role:admin|client'])->get('/by-user', [ProductController::class, 'productByUser'])->name('product.by_user');
    Route::get('/{id}', [ProductController::class, 'getById'])->name('product.get_by_id');
    Route::middleware(['auth:sanctum', 'role:admin|client'])->group(function () {
        Route::post('/create', [ProductController::class, 'create'])->name('product.create');
    });
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/change-status', [ProductController::class, 'changeStatus'])->name('product.change_status');
        Route::post('/update/{id}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/delete/{id}', [ProductController::class, 'delete'])->name('product.delete');
    });
});

Route::middleware(['auth:sanctum', 'role:admin|client'])->prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('/add', [CartController::class, 'add'])->name('cart.create');
    Route::post('/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/delete/{id}', [CartController::class, 'delete'])->name('cart.delete');
});
Route::middleware(['auth:sanctum', 'role:admin|client'])->prefix('image')->group(function () {
    Route::post('/upload', [ProductController::class, 'updateImage'])->name('image.upload');
    Route::post('/delete', [ProductController::class, 'deleteImage'])->name('image.delete');
});

Route::middleware(['auth:sanctum', 'role:admin|client'])->prefix('order')->group(function () {
    Route::get('/get-by-user', [OrderController::class, 'getByUser'])->name('order.get_by_user');
    Route::get('/', [OrderController::class, 'index'])->name('order.index');
    Route::get('/{id}', [OrderController::class, 'getById'])->name('order.get_by_id');
    Route::post('/create', [OrderController::class, 'create'])->name('order.create');
    Route::post('/update/{id}', [OrderController::class, 'update'])->name('order.update');
    Route::delete('/delete/{id}', [OrderController::class, 'delete'])->name('order.delete');
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('statistics')->group(function () {
    Route::get('/', [DashboardController::class, 'statistics'])->name('statistics');
});
