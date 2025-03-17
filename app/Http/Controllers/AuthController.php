<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    private $cloudName = 'dilsxgqkq';
    private $apiKey = '253398346126772';
    private $apiSecret = 'YK3V45nVVcmN6gbClSpytizJZlo';

    private function uploadToCloudinary($file)
    {
        $timestamp = time();
        $uploadPreset = ''; // Nếu không dùng upload preset thì để trống

        $paramsToSign = [
            'timestamp' => $timestamp,
            // 'upload_preset' => $uploadPreset // Nếu cần
        ];

        ksort($paramsToSign);

        $signatureString = http_build_query($paramsToSign) . $this->apiSecret;
        $signature = sha1($signatureString);

        $response = Http::attach(
            'file', file_get_contents($file), $file->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload", [
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            // 'upload_preset' => $uploadPreset, // Nếu dùng upload preset
        ]);

        if ($response->successful()) {
            return $response->json('secure_url');
        }

        Log::error('Cloudinary Upload Failed', [
            'response' => $response->body()
        ]);

        return null;
    }

    // Đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'Sai email hoặc mật khẩu'
            ], 401);
        }

        $user = JWTAuth::user();

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => $user
        ]);
    }

    // Đăng ký
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $avatarUrl = null;

        if ($request->hasFile('avatar')) {
            $avatarUrl = $this->uploadToCloudinary($request->file('avatar'));

            if (!$avatarUrl) {
                return response()->json([
                    'message' => 'Tải ảnh lên thất bại!'
                ], 500);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'user',
            'password' => bcrypt($request->password),
            'avatar' => $avatarUrl,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Đăng ký thành công',
            'token' => $token,
            'user' => $user
        ]);
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Đăng xuất thành công']);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Không thể đăng xuất, thử lại!'], 500);
        }
    }

    // Cập nhật thông tin user
    public function update(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email',
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $existingUser = User::where('email', $validated['email'])->where('id', '!=', $user->id)->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'Email đã được sử dụng bởi tài khoản khác.',
                    'errors' => ['email' => ['Email đã tồn tại!']]
                ], 422);
            }

            $user->email = $validated['email'];
        }

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Cập nhật thông tin thành công!',
            'user' => $user
        ]);
    }

    // Cập nhật profile + avatar
    public function updateProfile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validated['email'] !== $user->email) {
            $existingUser = User::where('email', $validated['email'])
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'Email đã được sử dụng bởi tài khoản khác.',
                    'errors' => ['email' => ['Email đã tồn tại!']]
                ], 422);
            }

            $user->email = $validated['email'];
        }

        $user->name = $validated['name'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Xử lý avatar
        if ($request->hasFile('avatar')) {
            $avatarUrl = $this->uploadToCloudinary($request->file('avatar'));

            if (!$avatarUrl) {
                return response()->json([
                    'message' => 'Tải ảnh lên thất bại!'
                ], 500);
            }

            $user->avatar = $avatarUrl;
        }

        $user->save();

        return response()->json([
            'message' => 'Cập nhật thông tin thành công!',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar
            ]
        ]);
    }

    // Lấy thông tin user
    public function profile(Request $request)
    {
        return response()->json(auth('api')->user());
    }
}

