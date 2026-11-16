@extends('layouts.app')

@section('title', 'Inventory')
@section('eyebrow', 'STOCK CONTROL')
@section('heading', 'Inventory')

@section('content')
<div class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div><h2 class="h5 mb-1">Stock levels</h2><p class="text-secondary small mb-0">Review quantities and reorder thresholds.</p></div>
        <a href="{{ route('inventory.create') }}" class="btn btn-coffee"><i class="bi bi-plus-lg me-1"></i> Add stock item</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Item</th><th>Quantity</th><th>Unit</th><th>Reorder level</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
            <tbody>
            @forelse($inventoryItems as $inventoryItem)
                @php($lowStock = (float) $inventoryItem->quantity <= (float) $inventoryItem->reorder_level)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $inventoryItem->item_name }}</td>
                    <td>{{ number_format((float) $inventoryItem->quantity, 2) }}</td>
                    <td>{{ $inventoryItem->unit }}</td>
                    <td>{{ number_format((float) $inventoryItem->reorder_level, 2) }}</td>
                    <td><span class="badge rounded-pill {{ $lowStock ? 'text-bg-warning' : 'text-bg-success' }}">{{ $lowStock ? 'Reorder' : 'In stock' }}</span></td>
                    <td class="text-end pe-4">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('inventory.edit', $inventoryItem) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('inventory.destroy', $inventoryItem) }}" onsubmit="return confirm('Delete this inventory item?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">No stock items have been added.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($inventoryItems->hasPages())<div class="p-3">{{ $inventoryItems->links() }}</div>@endif
</div>
@endsection
