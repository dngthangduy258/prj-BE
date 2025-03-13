@extends('layouts.app')

@section('content')
<h2 class="text-center">Welcome, {{ Auth::user()->name }}</h2>

<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        Thông tin người dùng
    </div>
    <div class="card-body">
        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
        <p><strong>Vai trò:</strong> {{ Auth::user()->role }}</p>
    </div>
</div>
@endsection
