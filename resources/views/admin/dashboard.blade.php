@extends('layouts.app')

@section('title', 'Dashboard')
@section('eyebrow', 'OVERVIEW')
@section('heading', 'Cafeteria dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="card h-100 p-3 p-lg-4"><div class="d-flex justify-content-between"><span class="text-secondary">Daily Sales</span><i class="bi bi-cash-coin text-amber fs-4"></i></div><div class="fs-3 fw-bold mt-2">{{ number_format((float) $dailySales, 2) }}</div><div class="small text-secondary">Completed today</div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100 p-3 p-lg-4"><div class="d-flex justify-content-between"><span class="text-secondary">Daily Orders</span><i class="bi bi-receipt text-amber fs-4"></i></div><div class="fs-3 fw-bold mt-2">{{ $dailyOrderCount }}</div><div class="small text-secondary">Completed today</div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100 p-3 p-lg-4"><div class="d-flex justify-content-between"><span class="text-secondary">Active Tables</span><i class="bi bi-grid text-amber fs-4"></i></div><div class="fs-3 fw-bold mt-2">{{ $activeTables }}</div><div class="small text-secondary">Tables with pending orders</div></div></div>
    <div class="col-6 col-xl-3"><div class="card h-100 p-3 p-lg-4"><div class="d-flex justify-content-between"><span class="text-secondary">Popular Items</span><i class="bi bi-star text-amber fs-4"></i></div><div class="fs-5 fw-bold mt-3 text-truncate">{{ $popularItems->first()?->menuItem?->name ?? '—' }}</div><div class="small text-secondary">{{ $popularItems->first()?->sold_quantity ?? 0 }} sold today</div></div></div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h2 class="h5 mb-1">Quick menu</h2><p class="text-secondary small mb-0">Available items, ready for checkout</p></div>
    <a class="btn btn-coffee" href="{{ route('pos.index') }}"><i class="bi bi-plus-lg me-1"></i> New order</a>
</div>
<div class="row g-3 mb-4">
    @forelse($quickMenuItems as $menuItem)
        <div class="col-6 col-lg-4 col-xl-2"><div class="card p-3 h-100"><div class="text-amber fs-4"><i class="bi bi-cup-hot"></i></div><div class="fw-semibold text-truncate mt-2">{{ $menuItem->name }}</div><div class="small text-secondary">{{ $menuItem->category->name }}</div><div class="fw-bold text-coffee mt-2">{{ number_format((float) $menuItem->price, 2) }}</div></div></div>
    @empty
        <div class="col-12"><div class="card p-4 text-secondary">No available menu items yet.</div></div>
    @endforelse
</div>

<section class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center"><div><h2 class="h5 mb-1">Recent orders</h2><p class="text-secondary small mb-0">Latest cafeteria transactions</p></div><a href="{{ route('pos.index') }}" class="btn btn-outline-secondary btn-sm">Open POS</a></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Order</th><th>Cashier</th><th>Table</th><th>Payment</th><th>Total</th><th>Status</th><th class="pe-4">Time</th></tr></thead>
            <tbody>
            @forelse($recentOrders as $order)
                <tr><td class="ps-4 fw-semibold">{{ $order->order_number }}</td><td>{{ $order->user->name }}</td><td>{{ $order->table_number ?: 'Takeaway' }}</td><td class="text-uppercase">{{ $order->payment_method }}</td><td>{{ number_format((float) $order->total_amount, 2) }}</td><td><span class="badge rounded-pill {{ $order->status === 'completed' ? 'text-bg-success' : ($order->status === 'pending' ? 'text-bg-warning' : 'text-bg-secondary') }}">{{ ucfirst($order->status) }}</span></td><td class="pe-4 text-secondary">{{ $order->created_at->format('H:i') }}</td></tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-4">No orders have been recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
