<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'User Management' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
        <a class="navbar-brand" href="#">Laravel 11+</a>
        @auth
            <div class="ms-auto">
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button class="btn btn-danger btn-sm">Logout</button>
                </form>
            </div>
        @endauth
    </nav>

    <div class="container mt-5">
        @yield('content')
    </div>
</body>
</html>
