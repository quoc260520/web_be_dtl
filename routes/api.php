<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
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
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/send-mail', [AuthController::class, 'sendMailResetPassword'])->name('auth.send_mail');
    Route::post('/check-token', [AuthController::class, 'checkToken'])->name('auth.check_token');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset_password');
});
Route::prefix('category')->group(function () {
    Route::get('/all', [CategoryController::class, 'index'])->name('category.register');
    Route::middleware(['auth:sanctum','role:admin'])->group(function () {
        Route::get('/{id}', [CategoryController::class, 'getById'])->name('category.get_by_id');
        Route::post('/create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/update/{id}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/delete/{id}', [CategoryController::class, 'delete'])->name('category.delete');
    });
});
