<?php

namespace App\Http\Controllers;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Danh sách tất cả user
    public function index()
    {
        return User::all();
    }

    // Chi tiết user
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy user'], 404);
        }

        return response()->json($user);
    }

    // Cập nhật user
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy user'], 404);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        return response()->json(['message' => 'Cập nhật thành công']);
    }

    // Xóa user
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy user'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Xóa thành công']);
    }

    
    public function uploadAvatar(Request $request)
    {
        // Validate file gửi lên
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);
    
        // Xác thực người dùng
        $user = JWTAuth::parseToken()->authenticate();
    
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        // Lấy thông tin file
        $file = $request->file('avatar');
        $filePath = $file->getRealPath(); // Đường dẫn tạm trên server
        $fileName = 'avatar_' . $user->id . '.' . $file->getClientOriginalExtension();
    
        // Thông tin Cloudinary
        $cloudName = 'dilsxgqkq';
        $apiKey = '253398346126772';
        $apiSecret = 'YK3V45nVVcmN6gbClSpytizJZlo';
    
        // Tạo timestamp và signature cho request (theo tài liệu Cloudinary API)
        $timestamp = time();
        $params_to_sign = "timestamp=$timestamp";
        $signature = sha1($params_to_sign . $apiSecret);
    
        // API endpoint Cloudinary
        $url = "https://api.cloudinary.com/v1_1/$cloudName/image/upload";
    
        // Tạo data post cho cURL
        $postData = [
            'file' => new \CURLFile($filePath),
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature
        ];
    
        // Khởi tạo curl
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
        // Thực thi curl
        $result = curl_exec($ch);
    
        // Kiểm tra lỗi
        if (curl_errno($ch)) {
            return response()->json([
                'message' => 'Upload lỗi',
                'error' => curl_error($ch)
            ], 500);
        }
    
        curl_close($ch);
    
        // Convert kết quả trả về thành mảng
        $resultData = json_decode($result, true);
    
        // Kiểm tra nếu upload không thành công
        if (!isset($resultData['secure_url'])) {
            return response()->json([
                'message' => 'Upload thất bại!',
                'response' => $resultData
            ], 500);
        }
    
        // Lấy URL hình ảnh đã upload trên Cloudinary
        $avatarUrl = $resultData['secure_url'];
    
        // Lưu URL vào DB (tuỳ bạn lưu URL hay chỉ tên file)
        $user->avatar = $avatarUrl;
        $user->save();
    
        return response()->json([
            'message' => 'Upload thành công!',
            'avatar_url' => $avatarUrl
        ]);
    }

}
