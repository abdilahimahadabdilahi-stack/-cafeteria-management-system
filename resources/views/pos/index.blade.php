@extends('layouts.app')

@section('title', 'POS terminal')
@section('eyebrow', 'SALES')
@section('heading', 'Point of sale')

@section('content')
<div class="row g-4 align-items-start">
    <section class="col-12 col-xl-8">
        <div class="d-flex flex-wrap gap-2 mb-3" aria-label="Menu categories">
            <button type="button" class="btn btn-coffee btn-sm category-filter" data-category="all">All items</button>
            @foreach($categories as $category)
                <button type="button" class="btn btn-outline-secondary btn-sm category-filter" data-category="{{ $category->id }}">{{ $category->name }}</button>
            @endforeach
        </div>
        <div class="row g-3" id="menu-grid">
            @forelse($categories as $category)
                @foreach($category->menuItems as $menuItem)
                    <div class="col-6 col-md-4 col-xxl-3 menu-card" data-category="{{ $category->id }}">
                        <button
                            type="button"
                            class="card p-3 h-100 w-100 text-start border-0 menu-add"
                            data-id="{{ $menuItem->id }}"
                            data-name="{{ $menuItem->name }}"
                            data-price="{{ $menuItem->price }}"
                        >
                            <span class="text-amber fs-3"><i class="bi bi-cup-hot"></i></span>
                            <span class="fw-semibold mt-2 text-truncate">{{ $menuItem->name }}</span>
                            <span class="small text-secondary">{{ $category->name }}</span>
                            <span class="fw-bold text-coffee mt-2">{{ number_format((float) $menuItem->price, 2) }}</span>
                        </button>
                    </div>
                @endforeach
            @empty
                <div class="col-12"><div class="card p-4 text-secondary">No categories have been configured.</div></div>
            @endforelse
            @if($categories->isNotEmpty() && $categories->every(fn ($category) => $category->menuItems->isEmpty()))
                <div class="col-12"><div class="card p-4 text-secondary">There are no available menu items.</div></div>
            @endif
        </div>
    </section>

    <aside class="col-12 col-xl-4">
        <form id="checkout-form" class="card p-3 p-lg-4" method="POST" action="{{ route('pos.store') }}">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><h2 class="h5 mb-1">Current order</h2><p class="small text-secondary mb-0">Select items to build a ticket</p></div>
                <span id="cart-count" class="badge rounded-pill text-bg-warning">0 items</span>
            </div>
            <div id="cart-lines" class="mb-3">
                <div id="cart-empty" class="border rounded-3 p-4 text-center text-secondary">
                    <i class="bi bi-basket2 fs-2 d-block mb-2"></i>Your cart is empty
                </div>
            </div>
            <div id="cart-inputs"></div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small fw-semibold" for="table_number">Table number</label>
                    <input class="form-control" id="table_number" name="table_number" value="{{ old('table_number') }}" placeholder="Takeaway">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold" for="payment_method">Payment</label>
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                        <option value="zaad" @selected(old('payment_method') === 'zaad')>Zaad</option>
                        <option value="edahab" @selected(old('payment_method') === 'edahab')>eDahab</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold" for="discount">Discount</label>
                    <input class="form-control" id="discount" name="discount" type="number" step="0.01" min="0" max="999999.99" value="{{ old('discount', '0.00') }}">
                </div>
            </div>
            <div class="border-top mt-3 pt-3">
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Subtotal</span><strong id="cart-subtotal">0.00</strong></div>
                <div class="d-flex justify-content-between mb-3"><span class="text-secondary">Total</span><strong class="fs-5 text-coffee" id="cart-total">0.00</strong></div>
                <button id="checkout-button" class="btn btn-coffee w-100 py-2" type="submit" disabled><i class="bi bi-check2-circle me-1"></i> Complete order</button>
            </div>
        </form>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    const cart = new Map();
    const cartLines = document.getElementById('cart-lines');
    const cartInputs = document.getElementById('cart-inputs');
    const discountInput = document.getElementById('discount');
    const money = (amount) => Number(amount).toFixed(2);

    function renderCart() {
        cartLines.replaceChildren();
        cartInputs.replaceChildren();
        let subtotal = 0;
        let count = 0;
        let index = 0;

        if (cart.size === 0) {
            const empty = document.createElement('div');
            empty.className = 'border rounded-3 p-4 text-center text-secondary';
            empty.textContent = 'Your cart is empty';
            cartLines.append(empty);
        }

        for (const [id, item] of cart) {
            const lineTotal = item.price * item.quantity;
            subtotal += lineTotal;
            count += item.quantity;

            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 border-bottom py-2';
            const detail = document.createElement('div');
            detail.className = 'flex-grow-1 min-w-0';
            const name = document.createElement('div');
            name.className = 'fw-semibold text-truncate';
            name.textContent = item.name;
            const price = document.createElement('div');
            price.className = 'small text-secondary';
            price.textContent = `${money(item.price)} each`;
            detail.append(name, price);

            const quantity = document.createElement('input');
            quantity.type = 'number';
            quantity.min = '1';
            quantity.max = '99';
            quantity.value = String(item.quantity);
            quantity.className = 'form-control form-control-sm';
            quantity.style.width = '68px';
            quantity.setAttribute('aria-label', `${item.name} quantity`);
            quantity.addEventListener('change', () => {
                const nextQuantity = Number.parseInt(quantity.value, 10);
                if (!Number.isInteger(nextQuantity) || nextQuantity < 1 || nextQuantity > 99) {
                    cart.delete(id);
                } else {
                    item.quantity = nextQuantity;
                }
                renderCart();
            });

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-secondary';
            remove.setAttribute('aria-label', `Remove ${item.name}`);
            remove.innerHTML = '<i class="bi bi-x-lg"></i>';
            remove.addEventListener('click', () => {
                cart.delete(id);
                renderCart();
            });

            row.append(detail, quantity, remove);
            cartLines.append(row);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = `items[${index}][menu_item_id]`;
            idInput.value = id;
            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = `items[${index}][quantity]`;
            quantityInput.value = String(item.quantity);
            cartInputs.append(idInput, quantityInput);
            index++;
        }

        const discount = Math.max(0, Number.parseFloat(discountInput.value) || 0);
        document.getElementById('cart-subtotal').textContent = money(subtotal);
        document.getElementById('cart-total').textContent = money(Math.max(0, subtotal - discount));
        document.getElementById('cart-count').textContent = `${count} ${count === 1 ? 'item' : 'items'}`;
        document.getElementById('checkout-button').disabled = cart.size === 0;
    }

    document.querySelectorAll('.menu-add').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            const item = cart.get(id) ?? {
                name: button.dataset.name,
                price: Number.parseFloat(button.dataset.price),
                quantity: 0,
            };
            if (item.quantity < 99) {
                item.quantity++;
            }
            cart.set(id, item);
            renderCart();
        });
    });

    document.querySelectorAll('.category-filter').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.category-filter').forEach((filter) => {
                filter.classList.toggle('btn-coffee', filter === button);
                filter.classList.toggle('btn-outline-secondary', filter !== button);
            });
            document.querySelectorAll('.menu-card').forEach((card) => {
                card.classList.toggle('d-none', button.dataset.category !== 'all' && card.dataset.category !== button.dataset.category);
            });
        });
    });

    discountInput.addEventListener('input', renderCart);
    document.getElementById('checkout-form').addEventListener('submit', (event) => {
        if (cart.size === 0) {
            event.preventDefault();
        }
    });
    renderCart();
</script>
@endpush
