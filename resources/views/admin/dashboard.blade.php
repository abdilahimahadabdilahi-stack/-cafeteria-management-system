@extends('layouts.app')

@section('title', 'Dashboard')
@section('eyebrow', 'OVERVIEW')
@section('heading', 'Cafeteria dashboard')

@php
    $popularItem = $popularItems->first();
@endphp

@push('styles')
<style>
    .dashboard-welcome { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; overflow: hidden; padding: 1.25rem 1.5rem; border: 1px solid #eee1cf; border-radius: 1.15rem; background: linear-gradient(112deg, #fffdfa 0%, #f7ecdb 100%); box-shadow: 0 .4rem 1.25rem rgba(66,39,25,.055); }
    .dashboard-welcome::after { position: absolute; top: -4.5rem; right: 12%; width: 12rem; height: 12rem; border: 1px solid rgba(216,150,50,.12); border-radius: 50%; content: ""; pointer-events: none; }
    .dashboard-kicker { display: flex; align-items: center; gap: .45rem; margin-bottom: .45rem; color: #966018; font-size: .72rem; font-weight: 700; letter-spacing: .12em; }
    .dashboard-welcome h2 { color: #422719; }
    .dashboard-welcome__note { position: relative; z-index: 1; display: flex; align-items: center; gap: .75rem; padding-left: 1.25rem; border-left: 1px solid #e8d8c0; color: #806b57; }
    .dashboard-welcome__note i { color: #b87918; font-size: 1.45rem; }
    .dashboard-stat-card { position: relative; overflow: hidden; padding: 1.15rem 1.2rem; transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .dashboard-stat-card::after { position: absolute; right: -2.25rem; bottom: -3rem; width: 7rem; height: 7rem; border-radius: 50%; background: rgba(216,150,50,.065); content: ""; pointer-events: none; }
    .dashboard-stat-card:hover { transform: translateY(-4px); border-color: #e2d1b9; box-shadow: 0 .8rem 1.6rem rgba(66,39,25,.1); }
    .dashboard-stat-label { color: #756a60; font-size: .9rem; font-weight: 600; }
    .dashboard-stat-icon { display: grid; width: 2.65rem; height: 2.65rem; place-items: center; border-radius: .9rem; background: #fbf1e2; color: #a76e1a; font-size: 1.2rem; }
    .dashboard-stat-value { position: relative; z-index: 1; margin-top: 1rem; color: #422719; font-size: clamp(1.35rem, 2vw, 1.85rem); font-weight: 750; line-height: 1.15; font-variant-numeric: tabular-nums; }
    .dashboard-stat-value--text { margin-top: 1.05rem; font-size: 1.1rem; }
    .dashboard-stat-hint { margin-top: .35rem; color: #84796f; font-size: .78rem; }
    .dashboard-section-heading h2 { color: #422719; }
    .quick-menu-card { position: relative; display: block; overflow: hidden; height: 100%; padding: .8rem; color: inherit; text-decoration: none; transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .quick-menu-card:hover { transform: translateY(-4px); border-color: #dec7a6; box-shadow: 0 .8rem 1.55rem rgba(66,39,25,.12); color: inherit; }
    .quick-menu-card:active { transform: scale(.985); }
    .quick-menu-card:focus-visible { outline: 3px solid rgba(216,150,50,.55); outline-offset: 3px; }
    .quick-menu-card:hover .menu-item-photo > img { transform: scale(1.055); }
    .quick-menu-card__action { position: absolute; top: 1.15rem; right: 1.15rem; z-index: 2; display: inline-flex; align-items: center; gap: .3rem; padding: .38rem .55rem; border: 1px solid rgba(255,255,255,.62); border-radius: 999px; background: rgba(255,255,255,.9); color: #422719; font-size: .7rem; font-weight: 700; box-shadow: 0 .2rem .65rem rgba(40,25,15,.12); backdrop-filter: blur(8px); transition: background-color .18s ease, color .18s ease, transform .18s ease; }
    .quick-menu-card:hover .quick-menu-card__action { transform: translateY(-1px); background: #422719; color: #fff; }
    .quick-menu-card__name { color: #3e2a1c; }
    .quick-menu-card__price { color: #80521c; }
    .dashboard-orders-table thead th { background: #faf6ef; color: #77685a; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .dashboard-orders-table tbody tr:hover { --bs-table-bg: #fffaf2; }
    .dashboard-reveal { animation: dashboard-fade-up .5s cubic-bezier(.2,.7,.2,1) both; animation-delay: var(--reveal-delay, 0ms); }
    @keyframes dashboard-fade-up { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 575.98px) {
        .dashboard-welcome { padding: 1rem 1.1rem; }
        .dashboard-stat-card { padding: 1rem; }
        .quick-menu-card { padding: .65rem; }
        .quick-menu-card__action { top: .95rem; right: .95rem; padding: .35rem; }
        .quick-menu-card__action span { display: none; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-page">
    <section class="dashboard-welcome mb-4" data-reveal>
        <div>
            <div class="dashboard-kicker"><i class="bi bi-stars"></i> CAFE AT A GLANCE</div>
            <h2 class="h4 mb-1">Today, at a glance</h2>
            <p class="small text-secondary mb-0">A warm welcome, and a clear view of how service is going.</p>
        </div>
        <div class="dashboard-welcome__note d-none d-sm-flex">
            <i class="bi bi-cup-hot"></i>
            <div><div class="fw-semibold text-coffee">Made with care</div><div class="small">Every order matters</div></div>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <article class="card dashboard-stat-card h-100" data-reveal>
                <div class="d-flex justify-content-between align-items-start">
                    <span class="dashboard-stat-label">Daily Sales</span>
                    <span class="dashboard-stat-icon"><i class="bi bi-cash-coin"></i></span>
                </div>
                <div class="dashboard-stat-value" data-count-up="{{ (float) $dailySales }}" data-count-decimals="2">{{ number_format((float) $dailySales, 2) }}</div>
                <div class="dashboard-stat-hint">Completed today</div>
            </article>
        </div>
        <div class="col-6 col-xl-3">
            <article class="card dashboard-stat-card h-100" data-reveal>
                <div class="d-flex justify-content-between align-items-start">
                    <span class="dashboard-stat-label">Daily Orders</span>
                    <span class="dashboard-stat-icon"><i class="bi bi-receipt"></i></span>
                </div>
                <div class="dashboard-stat-value" data-count-up="{{ $dailyOrderCount }}" data-count-decimals="0">{{ $dailyOrderCount }}</div>
                <div class="dashboard-stat-hint">Completed today</div>
            </article>
        </div>
        <div class="col-6 col-xl-3">
            <article class="card dashboard-stat-card h-100" data-reveal>
                <div class="d-flex justify-content-between align-items-start">
                    <span class="dashboard-stat-label">Active Tables</span>
                    <span class="dashboard-stat-icon"><i class="bi bi-grid"></i></span>
                </div>
                <div class="dashboard-stat-value" data-count-up="{{ $activeTables }}" data-count-decimals="0">{{ $activeTables }}</div>
                <div class="dashboard-stat-hint">Tables with pending orders</div>
            </article>
        </div>
        <div class="col-6 col-xl-3">
            <article class="card dashboard-stat-card h-100" data-reveal>
                <div class="d-flex justify-content-between align-items-start">
                    <span class="dashboard-stat-label">Popular Items</span>
                    <span class="dashboard-stat-icon"><i class="bi bi-star"></i></span>
                </div>
                <div class="dashboard-stat-value dashboard-stat-value--text text-truncate">{{ $popularItem?->menuItem?->name ?? '—' }}</div>
                <div class="dashboard-stat-hint"><span data-count-up="{{ $popularItem?->sold_quantity ?? 0 }}" data-count-decimals="0">{{ $popularItem?->sold_quantity ?? 0 }}</span> sold today</div>
            </article>
        </div>
    </div>

    <div class="dashboard-section-heading d-flex justify-content-between align-items-center mb-3" data-reveal>
        <div><h2 class="h5 mb-1">Quick menu</h2><p class="text-secondary small mb-0">Tap a favorite to add it to a new order</p></div>
        <a class="btn btn-coffee" href="{{ route('pos.index') }}"><i class="bi bi-plus-lg me-1"></i> New order</a>
    </div>
    <div class="row g-3 mb-4">
        @forelse($quickMenuItems as $menuItem)
            <div class="col-6 col-lg-4 col-xl-2" data-reveal>
                <a class="card quick-menu-card" href="{{ route('pos.index', ['menu_item_id' => $menuItem->id]) }}" aria-label="Add {{ $menuItem->name }} to a new order">
                    <span class="quick-menu-card__action" aria-hidden="true"><i class="bi bi-plus-lg"></i><span>Add</span></span>
                    <x-menu-item-image :menu-item="$menuItem" />
                    <div class="px-1 pt-2">
                        <div class="fw-semibold text-truncate quick-menu-card__name">{{ $menuItem->name }}</div>
                        <div class="small text-secondary text-truncate">{{ $menuItem->category->name }}</div>
                        <div class="fw-bold mt-2 quick-menu-card__price">{{ number_format((float) $menuItem->price, 2) }}</div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12"><div class="card p-4 text-secondary">No available menu items yet.</div></div>
        @endforelse
    </div>

    <section class="card overflow-hidden" data-reveal>
        <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center">
            <div><h2 class="h5 mb-1">Recent orders</h2><p class="text-secondary small mb-0">Latest cafeteria transactions</p></div>
            <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-calculator me-1"></i> Open POS</a>
        </div>
        <div class="table-responsive">
            <table class="table dashboard-orders-table align-middle mb-0">
                <thead><tr><th class="ps-4">Order</th><th>Cashier</th><th>Table</th><th>Payment</th><th>Total</th><th>Status</th><th class="pe-4">Time</th></tr></thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr data-reveal><td class="ps-4 fw-semibold">{{ $order->order_number }}</td><td>{{ $order->user->name }}</td><td>{{ $order->table_number ?: 'Takeaway' }}</td><td class="text-uppercase">{{ $order->payment_method }}</td><td>{{ number_format((float) $order->total_amount, 2) }}</td><td><span class="badge rounded-pill {{ $order->status === 'completed' ? 'text-bg-success' : ($order->status === 'pending' ? 'text-bg-warning' : 'text-bg-secondary') }}">{{ ucfirst($order->status) }}</span></td><td class="pe-4 text-secondary">{{ $order->created_at->format('H:i') }}</td></tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4">No orders have been recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion) {
        document.querySelectorAll('[data-reveal]').forEach((element, index) => {
            element.classList.add('dashboard-reveal');
            element.style.setProperty('--reveal-delay', `${Math.min(index, 8) * 55}ms`);
        });

        document.querySelectorAll('[data-count-up]').forEach((element) => {
            const target = Number(element.dataset.countUp);
            const decimals = Number(element.dataset.countDecimals);
            if (!Number.isFinite(target)) {
                return;
            }

            const formatNumber = new Intl.NumberFormat(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
            const startedAt = performance.now();
            const duration = 850;

            function updateCount(now) {
                const progress = Math.min((now - startedAt) / duration, 1);
                const easedProgress = 1 - Math.pow(1 - progress, 3);
                element.textContent = formatNumber.format(target * easedProgress);

                if (progress < 1) {
                    requestAnimationFrame(updateCount);
                }
            }

            requestAnimationFrame(updateCount);
        });
    }
</script>
@endpush
