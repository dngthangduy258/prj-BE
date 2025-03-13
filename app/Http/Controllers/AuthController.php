<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Exceptions\JWTException;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


use Illuminate\Support\Facades\Log;




class AuthController extends Controller
{
    // Đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'message' => 'Sai email hoặc mật khẩu'
            ], 401);
        }
    
        // ✅ Cách chắc chắn lấy user sau khi xác thực
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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // validate ảnh
        ]);

        // Xử lý avatar
        $avatarPath = null;

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Tạo tên file duy nhất (dựa theo timestamp + tên file)
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Lưu file vào storage/app/public/avatars (phải link storage)
            $file->storeAs('avatars/', $fileName);


            // Lưu path vào database (tùy cách bạn lấy ảnh ra, có thể dùng URL hoặc chỉ lưu tên file)
            $avatarPath = 'storage/avatars/' . $fileName;

        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'user',
            'password' => bcrypt($request->password),
            'avatar' => $avatarPath, // lưu đường dẫn ảnh
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
    public function update(Request $request)
    {
        // Lấy user từ JWTAuth
        $user = JWTAuth::parseToken()->authenticate();

        // Validate cơ bản
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email',
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        //Kiểm tra trùng email thủ công (ngoài validate)
        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            // Tìm user khác có cùng email
            $existingUser = User::where('email', $validated['email'])->where('id', '!=', $user->id)->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'Email đã được sử dụng bởi tài khoản khác.',
                    'errors' => [
                        'email' => ['Email đã tồn tại!']
                    ]
                ], 422);
            }

            // Nếu không có user trùng email, tiếp tục cập nhật
            $user->email = $validated['email'];
        }

        // Cập nhật các field khác
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

    public function updateProfile(Request $request)
    {
        // Xác thực người dùng
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate đầu vào
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Kiểm tra email có thay đổi không
        if ($validated['email'] !== $user->email) {
            $existingUser = User::where('email', $validated['email'])
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'message' => 'Email đã được sử dụng bởi tài khoản khác.',
                    'errors' => [
                        'email' => ['Email đã tồn tại!']
                    ]
                ], 422);
            }

            $user->email = $validated['email'];
        }

        // Cập nhật tên
        $user->name = $validated['name'];

        // Nếu có password mới thì hash và cập nhật
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Xử lý avatar nếu có upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            $fileName = time() . '_' . $file->getClientOriginalName();

        Log::info($fileName);
            // Lưu vào storage/app/public/avatars

            // Lưu file vào storage/app/public/avatars (phải link storage)
            $file->storeAs('avatars/', $fileName);


            // Lưu path vào database (tùy cách bạn lấy ảnh ra, có thể dùng URL hoặc chỉ lưu tên file)
            $avatarPath = 'storage/avatars/' . $fileName;
        
            // Xóa avatar cũ nếu có (trừ khi là ảnh mặc định)
           // Đúng cách:
           if ($user->avatar) {
            $avatarPath1 = '/' . $user->avatar;  // Di chuyển lên 1 folder cha
            
            $avatarPath = str_replace('/storage', '', $avatarPath1);
            if (Storage::exists($avatarPath)) {
                Log::info('Avatar found: ' . $avatarPath);
                Storage::disk('public')->delete($avatarPath);
                Log::info('Avatar deleted: ' . $avatarPath);
            } else {
                Log::warning('Avatar not found: ' . $avatarPath);
            }
        }
        

        
            // Chỉ lưu đường dẫn tương đối vào DB (Laravel best practice)
            $avatarPath = 'storage/avatars/' . $fileName;

        }
        $user->avatar = $avatarPath;
        

        $user->save();

        return response()->json([
            'message' => 'Cập nhật thông tin thành công!',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' =>$avatarPath
            ]
        ]);
        
    }


    
    // Lấy thông tin user
    public function profile(Request $request)
    {
        return response()->json(auth('api')->user());
    }
}
