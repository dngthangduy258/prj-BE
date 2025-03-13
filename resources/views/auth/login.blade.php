@extends('layouts.app')

@section('content')
<h2 class="text-center">Đăng nhập</h2>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('login.submit') }}" class="w-50 mx-auto">
    @csrf
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required />
    </div>

    <div class="mb-3">
        <label>Mật khẩu</label>
        <input type="password" name="password" class="form-control" required />
    </div>

    <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>

    <p class="mt-3 text-center">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p>
</form>
@endsection
