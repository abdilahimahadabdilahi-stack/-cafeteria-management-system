<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cafeteria') · Qaxwo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --coffee: #422719;
            --coffee-light: #62402d;
            --coffee-deep: #2f1d14;
            --amber: #d89632;
            --cream: #fbf7f0;
            --ink: #32271f;
            --line: #eee5da;
        }
        body { background: var(--cream); color: var(--ink); }
        .sidebar { background: linear-gradient(180deg, var(--coffee), var(--coffee-deep)); color: #fff; min-height: 100vh; width: 250px; }
        .brand { color: #fff; font-weight: 700; letter-spacing: .03em; }
        .sidebar .nav-link { color: #eadfd5; border-radius: .75rem; padding: .7rem .85rem; transition: background-color .18s ease, color .18s ease, transform .18s ease; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--coffee-light); color: #fff; }
        .sidebar .nav-link:hover { transform: translateX(3px); }
        .sidebar .nav-link.active { box-shadow: inset 3px 0 #e7aa4a; }
        .sidebar .nav-link i { width: 1.4rem; display: inline-block; }
        .main-shell { min-width: 0; }
        .topbar { position: sticky; top: 0; z-index: 20; background: rgba(255,255,255,.94); border-bottom: 1px solid var(--line); backdrop-filter: blur(12px); }
        .card { border: 1px solid var(--line); border-radius: 1rem; box-shadow: 0 .35rem 1.2rem rgba(66,39,25,.055); }
        .btn { border-radius: .75rem; transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0) scale(.985); }
        .btn-coffee { background: var(--coffee); color: #fff; border-color: var(--coffee); box-shadow: 0 .3rem .8rem rgba(66,39,25,.14); }
        .btn-coffee:hover, .btn-coffee:focus-visible { background: var(--coffee-light); color: #fff; border-color: var(--coffee-light); box-shadow: 0 .45rem 1rem rgba(66,39,25,.2); }
        .text-coffee { color: var(--coffee); }
        .text-amber { color: #b87918; }
        .table tbody tr { transition: background-color .18s ease; }
        .menu-item-photo { position: relative; display: block; width: 100%; min-height: 120px; aspect-ratio: 4 / 3; overflow: hidden; border-radius: .85rem; background: linear-gradient(135deg, #f8efe2, #efe0c9); }
        .menu-item-photo > img { position: relative; z-index: 1; display: block; width: 100%; height: 100%; object-fit: cover; transition: transform .45s cubic-bezier(.2,.7,.2,1); }
        .menu-item-photo__placeholder { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .35rem; color: #8c6b4c; font-size: .75rem; opacity: 0; background: linear-gradient(135deg, #f8efe2, #efe0c9); }
        .menu-item-photo__placeholder i { font-size: 1.8rem; }
        .menu-item-photo.is-placeholder .menu-item-photo__placeholder { opacity: 1; }
        .menu-add { overflow: hidden; border: 1px solid var(--line) !important; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .menu-add:hover { transform: translateY(-3px); border-color: #d8bd98 !important; box-shadow: 0 .7rem 1.4rem rgba(66,39,25,.12); }
        .menu-add:active { transform: scale(.985); }
        .menu-add:hover .menu-item-photo > img { transform: scale(1.055); }
        .menu-add.is-selected { border-color: var(--amber) !important; box-shadow: 0 0 0 .18rem rgba(216,150,50,.18); }
        @media (max-width: 767.98px) { .sidebar { min-height: auto; width: 100%; } .sidebar .nav { flex-direction: row !important; flex-wrap: wrap; } }
        @media (prefers-reduced-motion: reduce) {
            html:focus-within { scroll-behavior: auto !important; }
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-md-flex min-vh-100">
    <aside class="sidebar p-3">
        <a href="{{ route('dashboard') }}" class="brand text-decoration-none d-flex align-items-center gap-2 mb-4 px-2">
            <i class="bi bi-cup-hot-fill text-warning fs-4"></i><span>Qaxwo Cafeteria</span>
        </a>
        <nav class="nav flex-column gap-1">
            @if(auth()->user()->role === 'admin')
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
                <a class="nav-link {{ request()->routeIs('menu.*') ? 'active' : '' }}" href="{{ route('menu.index') }}"><i class="bi bi-journal-richtext"></i> Menu</a>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> Categories</a>
                <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}"><i class="bi bi-box-seam"></i> Inventory</a>
                <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}"><i class="bi bi-receipt"></i> Orders</a>
                <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}"><i class="bi bi-wallet2"></i> Expenses</a>
                <a class="nav-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}" href="{{ route('shifts.index') }}"><i class="bi bi-clock-history"></i> Shifts</a>
                <a class="nav-link {{ request()->routeIs('staff.*') ? 'active' : '' }}" href="{{ route('staff.index') }}"><i class="bi bi-people"></i> Staff</a>
            @endif
            @if(in_array(auth()->user()->role, ['admin', 'cashier'], true))
                <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}"><i class="bi bi-calculator"></i> POS Terminal</a>
            @endif
            @if(auth()->user()->role === 'kitchen')
                <a class="nav-link active" href="{{ route('dashboard') }}"><i class="bi bi-fire"></i> Kitchen Orders</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="nav-link border-0 bg-transparent w-100 text-start" type="submit"><i class="bi bi-box-arrow-left"></i> Logout</button>
            </form>
        </nav>
        <div class="small text-white-50 mt-4 px-2">Warm service, every day.</div>
    </aside>
    <main class="main-shell flex-grow-1">
        <header class="topbar px-3 px-lg-4 py-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="small text-secondary">@yield('eyebrow', 'CAFETERIA MANAGEMENT')</div>
                <h1 class="h5 mb-0 text-coffee">@yield('heading', 'Welcome')</h1>
            </div>
            <div class="text-end">
                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                <div class="small text-secondary text-capitalize">{{ auth()->user()->role }}</div>
            </div>
        </header>
        <div class="p-3 p-lg-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="fw-semibold">Please check the following:</div>
                    <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('[data-menu-photo] img').forEach((image) => {
        image.addEventListener('error', () => {
            const photoFrame = image.closest('[data-menu-photo]');
            if (image.dataset.usingFallback !== 'true' && image.dataset.fallbackSrc) {
                image.dataset.usingFallback = 'true';
                image.src = image.dataset.fallbackSrc;
                return;
            }

            photoFrame.classList.add('is-placeholder');
            image.remove();
        });

        if (image.complete && image.naturalWidth === 0) {
            image.dispatchEvent(new Event('error'));
        }
    });
</script>
@stack('scripts')
</body>
</html>
