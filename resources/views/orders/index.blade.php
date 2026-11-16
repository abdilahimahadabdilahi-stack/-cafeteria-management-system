@extends('layouts.app')

@section('title', 'Orders')
@section('eyebrow', 'SALES')
@section('heading', 'Order management')

@section('content')
<div class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center gap-3">
        <div><h2 class="h5 mb-1">Order history</h2><p class="small text-secondary mb-0">Review and manage cafeteria transactions.</p></div>
        <a class="btn btn-coffee" href="{{ route('orders.create') }}"><i class="bi bi-plus-lg me-1"></i> Create order</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Order number</th><th>Cashier</th><th>Table</th><th>Items</th><th>Payment</th><th>Total</th><th>Status</th><th class="text-end pe-4">Action</th></tr></thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $order->order_number }}</td><td>{{ $order->user->name }}</td><td>{{ $order->table_number ?: 'Takeaway' }}</td><td>{{ $order->order_items_count }}</td><td class="text-uppercase">{{ $order->payment_method }}</td><td>{{ number_format((float) $order->total_amount, 2) }}</td>
                    <td><span class="badge rounded-pill {{ $order->status === 'completed' ? 'text-bg-success' : ($order->status === 'pending' ? 'text-bg-warning' : 'text-bg-secondary') }}">{{ ucfirst($order->status) }}</span></td>
                    <td class="text-end pe-4"><a class="btn btn-sm btn-outline-secondary" href="{{ route('orders.show', $order) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-5">No orders have been recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())<div class="p-3">{{ $orders->links() }}</div>@endif
</div>
@endsection
