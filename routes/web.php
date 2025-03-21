<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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


Route::get('/run-migrate', function () {

    // Kiểm tra database có tồn tại chưa (optional)
    $dbName = env('DB_DATABASE');
    try {
        DB::connection()->getPdo();
        $database = DB::connection()->getDatabaseName();
        if ($database === $dbName) {
            echo " Đang kết nối tới database `{$database}`.<br>";
        }
    } catch (\Exception $e) {
        return ' Không kết nối được database: ' . $e->getMessage();
    }

    // Chạy migrate
    Artisan::call('migrate', ['--force' => true]);
    return ' Đã migrate database!';
});


Route::get('/create-db', function () {
    $database = 'prj_interview_db';
    try {
        DB::statement("CREATE DATABASE IF NOT EXISTS {$database}");
        return " Đã tạo database `{$database}`!";
    } catch (\Exception $e) {
        return ' Lỗi tạo database: ' . $e->getMessage();
    }
});
