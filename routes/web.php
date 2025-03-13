<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Đăng ký và đăng nhập (public)
Route::post('/api/login', [AuthController::class, 'login'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/api/register', [AuthController::class, 'register'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


/*
|--------------------------------------------------------------------------
| Protected Routes - Cần Token
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->group(function () {

    // Lấy profile cá nhân và update profile cá nhân
    Route::get('/api/profile', [AuthController::class, 'profile']);          // Lấy thông tin user hiện tại
    Route::get('/api/user', [AuthController::class, 'profile']);             // Lấy thông tin user hiện tại
    Route::put('/api/user', [AuthController::class, 'updateProfile']);       // Thêm route update chính mình
    Route::post('/api/logout', [AuthController::class, 'logout']);           // Logout
    Route::post('/api/user/avatar', [UserController::class, 'uploadAvatar']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/api/users', [UserController::class, 'index']);           // Lấy danh sách tất cả users
        Route::get('/api/users/{id}', [UserController::class, 'show']);       // Lấy chi tiết 1 user bất kỳ (admin)
        Route::put('/api/users/{id}', [UserController::class, 'update']);     // Admin update user khác
        Route::delete('/api/users/{id}', [UserController::class, 'destroy']); // Admin xóa user
    });
});


// Login Google URL
Route::get('/api/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Callback từ Google
Route::get('/api/auth/google/callback', [GoogleAuthController::class, 'callback'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


