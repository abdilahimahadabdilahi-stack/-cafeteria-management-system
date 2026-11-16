@extends('layouts.app')

@section('title', 'Menu')
@section('eyebrow', 'CATALOG')
@section('heading', 'Menu items')

@section('content')
<div class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div><h2 class="h5 mb-1">Manage menu</h2><p class="text-secondary small mb-0">Update products, categories, and pricing.</p></div>
        <a href="{{ route('menu.create') }}" class="btn btn-coffee"><i class="bi bi-plus-lg me-1"></i> Add menu item</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Item</th><th>Category</th><th>Price</th><th>Cost</th><th>Availability</th><th class="text-end pe-4">Actions</th></tr></thead>
            <tbody>
            @forelse($menuItems as $menuItem)
                <tr>
                    <td class="ps-4"><div class="fw-semibold">{{ $menuItem->name }}</div>@if($menuItem->image)<small class="text-secondary text-truncate d-block" style="max-width: 220px">{{ $menuItem->image }}</small>@endif</td>
                    <td>{{ $menuItem->category->name }}</td>
                    <td>{{ number_format((float) $menuItem->price, 2) }}</td>
                    <td>{{ $menuItem->cost_price === null ? '—' : number_format((float) $menuItem->cost_price, 2) }}</td>
                    <td><span class="badge rounded-pill {{ $menuItem->is_available ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $menuItem->is_available ? 'Available' : 'Unavailable' }}</span></td>
                    <td class="text-end pe-4">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('menu.edit', $menuItem) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('menu.destroy', $menuItem) }}" onsubmit="return confirm('Delete this menu item?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">No menu items have been added.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($menuItems->hasPages())<div class="p-3">{{ $menuItems->links() }}</div>@endif
</div>
@endsection
