@extends('layouts.app')

@section('title', 'Kitchen orders')
@section('eyebrow', 'KITCHEN')
@section('heading', 'Orders to prepare')

@section('content')
<div class="row g-3">
    @forelse($orders as $order)
        <div class="col-12 col-lg-6 col-xxl-4">
            <article class="card h-100 p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div><div class="small text-secondary">ORDER</div><h2 class="h5 mb-0">{{ $order->order_number }}</h2></div>
                    <span class="badge text-bg-warning rounded-pill">{{ $order->table_number ? 'Table '.$order->table_number : 'Takeaway' }}</span>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($order->orderItems as $orderItem)
                        <li class="list-group-item px-0 d-flex justify-content-between"><span>{{ $orderItem->menuItem->name }}</span><strong>× {{ $orderItem->quantity }}</strong></li>
                    @endforeach
                </ul>
                <div class="small text-secondary mt-3">Placed {{ $order->created_at->diffForHumans() }} · {{ $order->user->name }}</div>
            </article>
        </div>
    @empty
        <div class="col-12"><div class="card p-5 text-center"><i class="bi bi-check2-circle text-success fs-1"></i><h2 class="h5 mt-3">All caught up</h2><p class="text-secondary mb-0">There are no pending orders.</p></div></div>
    @endforelse
</div>
@endsection
