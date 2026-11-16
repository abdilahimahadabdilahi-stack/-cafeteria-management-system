@extends('layouts.app')

@section('title', $inventoryItem->exists ? 'Edit stock item' : 'Add stock item')
@section('eyebrow', 'STOCK CONTROL')
@section('heading', $inventoryItem->exists ? 'Edit stock item' : 'Add stock item')

@section('content')
<div class="card p-3 p-lg-4" style="max-width: 760px">
    <form method="POST" action="{{ $inventoryItem->exists ? route('inventory.update', $inventoryItem) : route('inventory.store') }}">
        @csrf
        @if($inventoryItem->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label for="item_name" class="form-label">Item name</label>
                <input id="item_name" name="item_name" value="{{ old('item_name', $inventoryItem->item_name) }}" class="form-control @error('item_name') is-invalid @enderror" required maxlength="255">
                @error('item_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="unit" class="form-label">Unit</label>
                <select id="unit" name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                    @foreach(['kg' => 'Kilograms (kg)', 'liter' => 'Liters', 'pcs' => 'Pieces'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('unit', $inventoryItem->unit) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="quantity" class="form-label">Quantity</label>
                <input id="quantity" name="quantity" type="number" step="0.01" min="0" max="999999.99" value="{{ old('quantity', $inventoryItem->quantity) }}" class="form-control @error('quantity') is-invalid @enderror" required>
                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="reorder_level" class="form-label">Reorder level</label>
                <input id="reorder_level" name="reorder_level" type="number" step="0.01" min="0" max="999999.99" value="{{ old('reorder_level', $inventoryItem->reorder_level) }}" class="form-control @error('reorder_level') is-invalid @enderror" required>
                @error('reorder_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex gap-2 pt-2">
                <button class="btn btn-coffee" type="submit">{{ $inventoryItem->exists ? 'Save changes' : 'Add stock item' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('inventory.index') }}">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
