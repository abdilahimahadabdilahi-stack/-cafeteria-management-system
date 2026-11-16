@extends('layouts.app')

@section('title', $order->exists ? 'Edit order' : 'Create order')
@section('eyebrow', 'SALES')
@section('heading', $order->exists ? 'Edit order details' : 'Create order')

@section('content')
<div class="card p-3 p-lg-4" style="max-width: 900px">
    <form method="POST" action="{{ $order->exists ? route('orders.update', $order) : route('orders.store') }}" id="order-form">
        @csrf
        @if($order->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label for="user_id" class="form-label">Cashier</label>
                <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                    <option value="">Choose cashier</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" @selected(old('user_id', $order->user_id ?: auth()->id()) == $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="table_number" class="form-label">Table number <span class="text-secondary">(optional)</span></label>
                <input id="table_number" name="table_number" value="{{ old('table_number', $order->table_number) }}" class="form-control @error('table_number') is-invalid @enderror" maxlength="30">
                @error('table_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="payment_method" class="form-label">Payment method</label>
                <select id="payment_method" name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                    @foreach(['cash' => 'Cash', 'zaad' => 'Zaad', 'edahab' => 'eDahab'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', $order->payment_method ?: 'cash') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="discount" class="form-label">Discount</label>
                <input id="discount" name="discount" type="number" step="0.01" min="0" max="999999.99" value="{{ old('discount', $order->discount ?: '0.00') }}" class="form-control @error('discount') is-invalid @enderror" required>
                @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if($order->exists)
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        @foreach(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $order->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @else
                <input type="hidden" name="status" value="pending">
            @endif
        </div>

        @if(!$order->exists)
            <div class="border-top mt-4 pt-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div><h2 class="h6 mb-1">Order items</h2><p class="small text-secondary mb-0">Prices are taken from the current menu.</p></div>
                    <button class="btn btn-sm btn-outline-secondary" type="button" id="add-order-item"><i class="bi bi-plus-lg me-1"></i> Add item</button>
                </div>
                @error('items')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                <div id="order-item-lines" class="d-grid gap-2">
                    @foreach(old('items', []) as $index => $oldItem)
                        <div class="row g-2 order-item-row">
                            <div class="col-md-8">
                                <select name="items[{{ $index }}][menu_item_id]" class="form-select" required>
                                    <option value="">Choose menu item</option>
                                    @foreach($menuItems as $menuItem)
                                        <option value="{{ $menuItem->id }}" @selected(($oldItem['menu_item_id'] ?? null) == $menuItem->id)>{{ $menuItem->name }} · {{ number_format((float) $menuItem->price, 2) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3"><input type="number" name="items[{{ $index }}][quantity]" value="{{ $oldItem['quantity'] ?? 1 }}" min="1" max="99" class="form-control" required></div>
                            <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-order-item" aria-label="Remove item"><i class="bi bi-x-lg"></i></button></div>
                        </div>
                    @endforeach
                </div>
                @error('items.*.menu_item_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                @error('items.*.quantity')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </div>
        @endif

        <div class="pt-4">
            <button class="btn btn-coffee" type="submit">{{ $order->exists ? 'Save order details' : 'Create order' }}</button>
            <a class="btn btn-outline-secondary ms-1" href="{{ $order->exists ? route('orders.show', $order) : route('orders.index') }}">Cancel</a>
        </div>
    </form>
</div>
@if(!$order->exists)
    <template id="order-item-template">
        <div class="row g-2 order-item-row">
            <div class="col-md-8">
                <select class="form-select" data-name="menu_item_id" required>
                    <option value="">Choose menu item</option>
                    @foreach($menuItems as $menuItem)
                        <option value="{{ $menuItem->id }}">{{ $menuItem->name }} · {{ number_format((float) $menuItem->price, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><input type="number" data-name="quantity" value="1" min="1" max="99" class="form-control" required></div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-order-item" aria-label="Remove item"><i class="bi bi-x-lg"></i></button></div>
        </div>
    </template>
    @push('scripts')
    <script>
        const orderItemLines = document.getElementById('order-item-lines');
        let nextOrderItemIndex = {{ count(old('items', [])) }};
        document.getElementById('add-order-item').addEventListener('click', () => {
            const row = document.getElementById('order-item-template').content.firstElementChild.cloneNode(true);
            row.querySelectorAll('[data-name]').forEach((input) => {
                input.name = `items[${nextOrderItemIndex}][${input.dataset.name}]`;
            });
            nextOrderItemIndex++;
            orderItemLines.append(row);
        });
        orderItemLines.addEventListener('click', (event) => {
            if (event.target.closest('.remove-order-item')) {
                event.target.closest('.order-item-row').remove();
            }
        });
        if (orderItemLines.children.length === 0) {
            document.getElementById('add-order-item').click();
        }
    </script>
    @endpush
@endif
@endsection
