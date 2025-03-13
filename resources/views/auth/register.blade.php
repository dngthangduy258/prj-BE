@extends('layouts.app')

@section('content')
<h2 class="text-center">Đăng ký tài khoản</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register.submit') }}" class="w-50 mx-auto">
    @csrf
    <div class="mb-3">
        <label>Họ tên</label>
        <input type="text" name="name" class="form-control" required />
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required />
    </div>

    <div class="mb-3">
        <label>Mật khẩu</label>
        <input type="password" name="password" class="form-control" required />
    </div>

    <div class="mb-3">
        <label>Nhập lại mật khẩu</label>
        <input type="password" name="password_confirmation" class="form-control" required />
    </div>

    <button type="submit" class="btn btn-success w-100">Đăng ký</button>

    <p class="mt-3 text-center">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
</form>
@endsection
