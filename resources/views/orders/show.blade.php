@extends('layouts.app')

@section('title', $order->order_number)
@section('eyebrow', 'SALES')
@section('heading', 'Order details')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div><div class="small text-secondary">ORDER NUMBER</div><h2 class="h4 mb-0">{{ $order->order_number }}</h2></div>
    <div class="d-flex gap-2">
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Back to orders</a>
        @if($order->status !== 'cancelled')
            <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-secondary">Edit order</a>
            <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Cancel this order? Its order and payment history will be kept.')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger" type="submit">Cancel order</button>
            </form>
        @endif
    </div>
</div>
<div class="row g-3">
    <div class="col-12 col-xl-8">
        <section class="card overflow-hidden">
            <div class="p-3 d-flex justify-content-between align-items-center">
                <div><h3 class="h6 mb-1">Order items</h3><span class="small text-secondary">{{ $order->orderItems->count() }} lines</span></div>
                @if($order->status === 'pending')
                    <a class="btn btn-sm btn-coffee" href="{{ route('orders.items.create', $order) }}"><i class="bi bi-plus-lg me-1"></i> Add item</a>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-3">Item</th><th>Qty</th><th>Unit price</th><th>Total</th>@if($order->status === 'pending')<th class="text-end pe-3">Actions</th>@endif</tr></thead>
                    <tbody>
                    @forelse($order->orderItems as $orderItem)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $orderItem->menuItem->name }}</td><td>{{ $orderItem->quantity }}</td><td>{{ number_format((float) $orderItem->unit_price, 2) }}</td><td>{{ number_format((float) $orderItem->total_price, 2) }}</td>
                            @if($order->status === 'pending')
                                <td class="text-end pe-3">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('orders.items.edit', [$order, $orderItem]) }}">Edit</a>
                                    <form class="d-inline" method="POST" action="{{ route('orders.items.destroy', [$order, $orderItem]) }}" onsubmit="return confirm('Remove this order line?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">This order has no line items.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 ms-auto" style="min-width: min(100%, 300px)">
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Subtotal</span><strong>{{ number_format((float) $order->subtotal, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Discount</span><strong>{{ number_format((float) $order->discount, 2) }}</strong></div>
                <div class="d-flex justify-content-between border-top pt-2"><span class="fw-semibold">Total</span><strong class="text-coffee">{{ number_format((float) $order->total_amount, 2) }}</strong></div>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-4">
        <section class="card p-3">
            <h3 class="h6 mb-3">Transaction details</h3>
            <dl class="row small mb-0">
                <dt class="col-5 text-secondary">Status</dt><dd class="col-7"><span class="badge text-bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($order->status) }}</span></dd>
                <dt class="col-5 text-secondary">Cashier</dt><dd class="col-7">{{ $order->user->name }}</dd>
                <dt class="col-5 text-secondary">Table</dt><dd class="col-7">{{ $order->table_number ?: 'Takeaway' }}</dd>
                <dt class="col-5 text-secondary">Payment</dt><dd class="col-7 text-uppercase">{{ $order->payment_method }}</dd>
                <dt class="col-5 text-secondary">Created</dt><dd class="col-7">{{ $order->created_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </section>
    </div>
</div>
@endsection
