@extends('layouts.app')

@section('title', 'Orders')
@section('eyebrow', 'SALES')
@section('heading', 'Order management')

@push('styles')
<style>
    .orders-card { overflow: hidden; border-color: #ebdfd0; }
    .orders-card__header { display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; padding: 1.2rem 1.4rem; border-bottom: 1px solid #eee5da; background: linear-gradient(110deg, #fffdfa, #faf4eb); }
    .orders-kicker { display: flex; align-items: center; gap: .4rem; margin-bottom: .35rem; color: #966018; font-size: .7rem; font-weight: 700; letter-spacing: .11em; }
    .orders-title { color: #422719; }
    .orders-summary { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border: 1px solid #eadbc5; border-radius: 999px; background: #fff; color: #78552e; font-size: .72rem; font-weight: 650; }
    .orders-summary i { color: #ad741e; }
    .orders-table { min-width: 930px; }
    .orders-table thead th { padding-top: .8rem; padding-bottom: .8rem; border-bottom-color: #e9dfd2; background: #faf6ef; color: #786b5d; font-size: .68rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; white-space: nowrap; }
    .orders-table tbody td { padding-top: .95rem; padding-bottom: .95rem; border-color: #f0e9df; vertical-align: middle; }
    .orders-table tbody tr { transition: background-color .18s ease, box-shadow .18s ease; }
    .orders-table tbody tr:hover { --bs-table-bg: #fffaf2; box-shadow: inset 3px 0 #d89632; }
    .order-number { display: inline-flex; align-items: center; gap: .45rem; color: #412b1d; font-family: var(--bs-font-monospace); font-size: .76rem; font-weight: 650; white-space: nowrap; }
    .order-number i { color: #a76e1a; font-size: .9rem; }
    .order-cashier { display: inline-flex; align-items: center; gap: .5rem; color: #40352c; font-weight: 550; white-space: nowrap; }
    .order-cashier__icon { display: grid; width: 1.8rem; height: 1.8rem; place-items: center; border: 1px solid #eee4d5; border-radius: 50%; background: #fffdfa; color: #8c6841; font-size: .8rem; }
    .order-context-badge, .order-item-count { display: inline-flex; align-items: center; gap: .35rem; padding: .35rem .55rem; border: 1px solid #eee4d5; border-radius: 999px; background: #fffdfa; color: #655442; font-size: .76rem; font-weight: 600; white-space: nowrap; }
    .order-context-badge i, .order-item-count i { color: #a76e1a; }
    .order-item-count { border-color: #eadbc5; background: #fbf3e7; color: #725126; font-variant-numeric: tabular-nums; }
    .payment-badge { display: inline-flex; align-items: center; gap: .38rem; padding: .38rem .62rem; border: 1px solid transparent; border-radius: 999px; font-size: .68rem; font-weight: 750; letter-spacing: .045em; white-space: nowrap; transition: transform .18s ease, box-shadow .18s ease; }
    .payment-badge:hover { transform: translateY(-1px); box-shadow: 0 .25rem .55rem rgba(50,39,31,.1); }
    .payment-badge--cash { border-color: #cfe4d5; background: #edf6ef; color: #28613a; }
    .payment-badge--zaad { border-color: #d7d9f3; background: #f0f0fb; color: #41458a; }
    .payment-badge--edahab { border-color: #efd9b7; background: #fff4e5; color: #825416; }
    .payment-badge--other { border-color: #e5ddd3; background: #f7f3ee; color: #62564a; }
    .order-total { color: #422719; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .order-status { display: inline-flex; align-items: center; gap: .35rem; padding: .38rem .65rem; border: 1px solid transparent; border-radius: 999px; font-size: .72rem; font-weight: 650; white-space: nowrap; }
    .order-status--completed { border-color: #cfe4d5; background: #edf6ef; color: #28613a; }
    .order-status--pending { border-color: #f0dfb8; background: #fff7e6; color: #805a16; }
    .order-status--cancelled, .order-status--other { border-color: #e5ddd3; background: #f7f3ee; color: #62564a; }
    .orders-view-button { display: inline-flex; align-items: center; gap: .35rem; border-color: #d8c8b3; color: #513b28; font-weight: 600; transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease; }
    .orders-view-button:hover, .orders-view-button:focus-visible { border-color: #422719; background: #422719; color: #fff; box-shadow: 0 .25rem .65rem rgba(66,39,25,.18); }
    .orders-view-button:active { transform: scale(.97); }
    .orders-empty { padding: 3rem 1rem !important; color: #82776c; }
    .orders-empty i { display: block; margin-bottom: .65rem; color: #b58b55; font-size: 1.7rem; }
    .orders-pagination { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .85rem 1.25rem; border-top: 1px solid #eee5da; }
    .orders-pagination__summary { color: #82776c; font-size: .8rem; white-space: nowrap; }
    .orders-reveal { animation: orders-fade-up .42s cubic-bezier(.2,.7,.2,1) both; animation-delay: var(--order-reveal-delay, 0ms); }
    @keyframes orders-fade-up { from { opacity: 0; transform: translateY(9px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 575.98px) {
        .orders-card__header { align-items: flex-start; flex-direction: column; padding: 1rem; }
        .orders-card__header > .btn { align-self: stretch; }
        .orders-table { min-width: 850px; }
        .orders-pagination { align-items: flex-start; flex-direction: column; padding: .8rem 1rem; }
        .orders-pagination .pagination { margin-bottom: 0; }
    }
</style>
@endpush

@section('content')
<section class="card orders-card" data-order-reveal>
    <header class="orders-card__header">
        <div>
            <div class="orders-kicker"><i class="bi bi-receipt"></i> SALES ACTIVITY</div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h2 class="h5 orders-title mb-0">Order history</h2>
                <span class="orders-summary"><i class="bi bi-collection"></i> {{ $orders->total() }} {{ $orders->total() === 1 ? 'order' : 'orders' }}</span>
            </div>
            <p class="small text-secondary mb-0 mt-1">Review and manage cafeteria transactions.</p>
        </div>
        <a class="btn btn-coffee" href="{{ route('orders.create') }}"><i class="bi bi-plus-lg me-1"></i> Create order</a>
    </header>

    <div class="table-responsive">
        <table class="table orders-table align-middle mb-0">
            <thead>
                <tr><th class="ps-4">Order number</th><th>Cashier</th><th>Table</th><th>Items</th><th>Payment</th><th>Total</th><th>Status</th><th class="text-end pe-4">Action</th></tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                @php
                    $paymentMethod = strtolower($order->payment_method);
                    $paymentClass = match ($paymentMethod) {
                        'cash', 'zaad', 'edahab' => $paymentMethod,
                        default => 'other',
                    };
                    $paymentIcon = match ($paymentMethod) {
                        'cash' => 'cash-coin',
                        'zaad' => 'phone',
                        'edahab' => 'wallet2',
                        default => 'credit-card',
                    };
                    $statusClass = match (strtolower($order->status)) {
                        'completed' => 'completed',
                        'pending' => 'pending',
                        'cancelled' => 'cancelled',
                        default => 'other',
                    };
                    $itemCount = (int) $order->order_items_count;
                @endphp
                <tr data-order-reveal>
                    <td class="ps-4"><span class="order-number"><i class="bi bi-receipt" aria-hidden="true"></i>{{ $order->order_number }}</span></td>
                    <td><span class="order-cashier"><i class="bi bi-person-circle order-cashier__icon" aria-hidden="true"></i>{{ $order->user->name }}</span></td>
                    <td>
                        @if($order->table_number)
                            <span class="order-context-badge"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>{{ $order->table_number }}</span>
                        @else
                            <span class="order-context-badge"><i class="bi bi-bag" aria-hidden="true"></i>Takeaway</span>
                        @endif
                    </td>
                    <td><span class="order-item-count"><i class="bi bi-basket2" aria-hidden="true"></i>{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}</span></td>
                    <td><span class="payment-badge payment-badge--{{ $paymentClass }}" aria-label="Payment method: {{ strtoupper($order->payment_method) }}"><i class="bi bi-{{ $paymentIcon }}" aria-hidden="true"></i>{{ strtoupper($order->payment_method) }}</span></td>
                    <td><span class="order-total">{{ number_format((float) $order->total_amount, 2) }}</span></td>
                    <td><span class="order-status order-status--{{ $statusClass }}"><i class="bi {{ $statusClass === 'completed' ? 'bi-check-circle-fill' : ($statusClass === 'pending' ? 'bi-clock-fill' : 'bi-dash-circle-fill') }}" aria-hidden="true"></i>{{ ucfirst($order->status) }}</span></td>
                    <td class="text-end pe-4"><a class="btn btn-sm btn-outline-secondary orders-view-button" href="{{ route('orders.show', $order) }}" aria-label="View order {{ $order->order_number }}"><i class="bi bi-eye" aria-hidden="true"></i> View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="orders-empty text-center"><i class="bi bi-receipt" aria-hidden="true"></i>No orders have been recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <footer class="orders-pagination">
            <span class="orders-pagination__summary">Showing {{ $orders->firstItem() }}&ndash;{{ $orders->lastItem() }} of {{ $orders->total() }} orders</span>
            {{ $orders->links() }}
        </footer>
    @endif
</section>
@endsection

@push('scripts')
<script>
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('[data-order-reveal]').forEach((element, index) => {
            element.classList.add('orders-reveal');
            element.style.setProperty('--order-reveal-delay', `${Math.min(index, 8) * 35}ms`);
        });
    }
</script>
@endpush
