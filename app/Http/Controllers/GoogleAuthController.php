<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite; 

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\alert;
class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();

    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Kiểm tra user đã tồn tại chưa
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Tạo user mới nếu chưa có
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt('password'), // Bạn có thể set random nếu không dùng mật khẩu
                    'role' => 'user'
                ]);
            }

            // Tạo JWT token
            $token = JWTAuth::fromUser($user);
            Log::info('Token:', ['token' => $token]);
            // ✅ Quan trọng: Sau khi callback thành công thì frontend cần lấy token
            // Ví dụ: Gửi response JSON về hoặc redirect kèm token
            return redirect()->away('https://react-fe-blue.vercel.app/login-success?token=' . $token);

        } catch (\Exception $e) {
            return redirect()->away('https://react-fe-blue.vercel.app/login-failed');
        }
    }
}
