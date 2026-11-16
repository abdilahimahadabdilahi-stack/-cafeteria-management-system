@extends('layouts.app')

@section('title', 'Categories')
@section('eyebrow', 'MENU SETUP')
@section('heading', 'Categories')

@section('content')
<div class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center gap-3">
        <div><h2 class="h5 mb-1">Menu categories</h2><p class="small text-secondary mb-0">Organize the items shown at the POS.</p></div>
        <a class="btn btn-coffee" href="{{ route('categories.create') }}"><i class="bi bi-plus-lg me-1"></i> Add category</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Name</th><th>Icon</th><th>Menu items</th><th class="text-end pe-4">Actions</th></tr></thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $category->name }}</td>
                    <td>{{ $category->icon ?: '—' }}</td>
                    <td>{{ $category->menu_items_count }}</td>
                    <td class="text-end pe-4">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('categories.edit', $category) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category and its unused menu items?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-secondary py-5">No categories have been created.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())<div class="p-3">{{ $categories->links() }}</div>@endif
</div>
@endsection
