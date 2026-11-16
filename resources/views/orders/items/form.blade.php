@extends('layouts.app')

@section('title', $orderItem->exists ? 'Edit order item' : 'Add order item')
@section('eyebrow', $order->order_number)
@section('heading', $orderItem->exists ? 'Edit order line' : 'Add order line')

@section('content')
<div class="card p-3 p-lg-4" style="max-width: 680px">
    <form method="POST" action="{{ $orderItem->exists ? route('orders.items.update', [$order, $orderItem]) : route('orders.items.store', $order) }}">
        @csrf
        @if($orderItem->exists) @method('PUT') @endif
        <div class="mb-3">
            <label for="menu_item_id" class="form-label">Menu item</label>
            <select id="menu_item_id" name="menu_item_id" class="form-select @error('menu_item_id') is-invalid @enderror" required>
                <option value="">Choose menu item</option>
                @foreach($menuItems as $menuItem)
                    <option value="{{ $menuItem->id }}" @selected(old('menu_item_id', $orderItem->menu_item_id) == $menuItem->id)>{{ $menuItem->name }} · {{ number_format((float) $menuItem->price, 2) }}{{ $menuItem->is_available ? '' : ' (unavailable)' }}</option>
                @endforeach
            </select>
            @error('menu_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="quantity" class="form-label">Quantity</label>
            <input id="quantity" name="quantity" type="number" min="1" max="99" value="{{ old('quantity', $orderItem->quantity ?: 1) }}" class="form-control @error('quantity') is-invalid @enderror" required>
            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-coffee" type="submit">{{ $orderItem->exists ? 'Save line' : 'Add line' }}</button>
        <a class="btn btn-outline-secondary ms-1" href="{{ route('orders.show', $order) }}">Cancel</a>
    </form>
</div>
@endsection
