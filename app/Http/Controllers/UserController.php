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
    
        $user = JWTAuth::parseToken()->authenticate();
    
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        $file = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '.' . $file->getClientOriginalExtension();
    
        // Lưu trực tiếp vào thư mục tạm thời của hệ thống
        $tmpPath = '/tmp/' . $filename;
        $file->move('/tmp', $filename); // Di chuyển file vào /tmp
    
        // Nếu bạn muốn lưu đường dẫn vào DB (tùy bạn có trường avatar hay không)
        $user->avatar = $tmpPath; // hoặc chỉ lưu tên file thôi nếu thích
        $user->save();
    
        return response()->json([
            'message' => 'Upload thành công!',
            'avatar_tmp_path' => $tmpPath
        ]);
    }

}
