<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · Qaxwo Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; background: radial-gradient(circle at 20% 20%, #8b5a35, transparent 38%), linear-gradient(135deg, #28170f, #54321f 58%, #9a5d2f); }
        .login-card { width: min(100%, 440px); border: 0; border-radius: 1.25rem; box-shadow: 0 1.5rem 4rem #1c100b66; }
        .cup-icon { width: 76px; height: 76px; display: grid; place-items: center; margin: auto; border-radius: 50%; color: #fff; background: linear-gradient(145deg, #784322, #422719); box-shadow: 0 .5rem 1rem #42271933; }
        .coffee-button { background: #422719; color: #fff; }
        .coffee-button:hover { background: #62402d; color: #fff; }
    </style>
</head>
<body>
<main class="card login-card p-4 p-md-5">
    <div class="text-center mb-4">
        <div class="cup-icon mb-3"><i class="bi bi-cup-hot-fill fs-2"></i></div>
        <h1 class="h3 fw-bold mb-1">Qaxwo Cafeteria</h1>
        <p class="text-secondary mb-0">Sign in to your staff account</p>
    </div>
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label fw-semibold">Username</label>
            <input id="username" name="username" value="{{ old('username') }}" class="form-control form-control-lg @error('username') is-invalid @enderror" autocomplete="username" required autofocus>
            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password</label>
            <input id="password" name="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" autocomplete="current-password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-4">
            <input id="remember" name="remember" type="checkbox" class="form-check-input" value="1">
            <label for="remember" class="form-check-label text-secondary">Remember me</label>
        </div>
        <button class="btn coffee-button btn-lg w-100 mb-3" type="submit">Sign in <i class="bi bi-arrow-right ms-1"></i></button>
    </form>

    <!-- --- KU DARAN: Qaybta ku geedaysa Bogga Register-ka --- -->
    <div class="text-center small mt-2">
        <span class="text-secondary">Ma leedahay account? Maya,</span> 
        <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: #422719;">Isdiiwaangeli</a>
    </div>

    <p class="small text-secondary text-center mt-4 mb-0">Staff access · Admin · Cashier · Kitchen</p>
</main>
</body>
</html>