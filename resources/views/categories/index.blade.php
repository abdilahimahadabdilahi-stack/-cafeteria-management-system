@extends('layouts.app')

@section('title', 'Categories')
@section('eyebrow', 'MENU SETUP')
@section('heading', 'Categories')

@push('styles')
<style>
    .categories-card { overflow: hidden; border-color: #ebdfd0; }
    .categories-card__header { display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; padding: 1.2rem 1.4rem; border-bottom: 1px solid #eee5da; background: linear-gradient(110deg, #fffdfa, #faf4eb); }
    .categories-kicker { display: flex; align-items: center; gap: .4rem; margin-bottom: .35rem; color: #966018; font-size: .7rem; font-weight: 700; letter-spacing: .11em; }
    .categories-title { color: #422719; }
    .categories-summary { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border: 1px solid #eadbc5; border-radius: 999px; background: #fff; color: #78552e; font-size: .72rem; font-weight: 650; }
    .categories-summary i { color: #ad741e; }
    .categories-table { min-width: 650px; }
    .categories-table thead th { padding-top: .8rem; padding-bottom: .8rem; border-bottom-color: #e9dfd2; background: #faf6ef; color: #786b5d; font-size: .7rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .categories-table tbody td { padding-top: 1rem; padding-bottom: 1rem; border-color: #f0e9df; vertical-align: middle; }
    .categories-table tbody tr { transition: background-color .18s ease, box-shadow .18s ease; }
    .categories-table tbody tr:hover { --bs-table-bg: #fffaf2; box-shadow: inset 3px 0 #d89632; }
    .category-name { color: #3e2a1c; font-weight: 650; }
    .category-icon-badge { display: inline-flex; min-width: 8.5rem; align-items: center; gap: .55rem; padding: .4rem .65rem; border: 1px solid #eee4d5; border-radius: .75rem; background: #fffdfa; color: #5d4834; font-size: .8rem; font-weight: 550; }
    .category-icon-badge i { display: grid; width: 1.65rem; height: 1.65rem; flex: 0 0 auto; place-items: center; border-radius: .5rem; background: #f8eddb; color: #9a6419; font-size: .95rem; }
    .category-item-count { display: inline-flex; align-items: center; gap: .35rem; padding: .35rem .65rem; border: 1px solid #eadbc5; border-radius: 999px; background: #fbf3e7; color: #725126; font-size: .78rem; font-weight: 700; font-variant-numeric: tabular-nums; }
    .category-item-count i { color: #a76e1a; }
    .category-action-group { display: inline-flex; align-items: center; gap: .45rem; }
    .category-action { display: inline-flex; min-height: 2rem; align-items: center; justify-content: center; gap: .35rem; padding: .35rem .65rem; font-weight: 600; transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease; }
    .category-action--edit { border-color: #d8c8b3; color: #513b28; }
    .category-action--edit:hover { border-color: #b99569; background: #f8efe2; color: #382719; }
    .category-action--delete:hover { box-shadow: 0 .2rem .55rem rgba(220,53,69,.15); }
    .category-action:active { transform: scale(.97); }
    .categories-empty { padding: 3rem 1rem !important; color: #82776c; }
    .categories-empty i { display: block; margin-bottom: .65rem; color: #b58b55; font-size: 1.7rem; }
    .categories-reveal { animation: categories-fade-up .42s cubic-bezier(.2,.7,.2,1) both; animation-delay: var(--category-reveal-delay, 0ms); }
    @keyframes categories-fade-up { from { opacity: 0; transform: translateY(9px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 575.98px) {
        .categories-card__header { align-items: flex-start; flex-direction: column; padding: 1rem; }
        .categories-card__header > .btn { align-self: stretch; }
        .categories-table { min-width: 610px; }
    }
</style>
@endpush

@section('content')
<div class="categories-page">
    <section class="card categories-card" data-category-reveal>
        <header class="categories-card__header">
            <div>
                <div class="categories-kicker"><i class="bi bi-tags"></i> MENU ORGANIZATION</div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <h2 class="h5 categories-title mb-0">Menu categories</h2>
                    <span class="categories-summary"><i class="bi bi-collection"></i> {{ $categories->total() }} {{ $categories->total() === 1 ? 'category' : 'categories' }}</span>
                </div>
                <p class="small text-secondary mb-0 mt-1">Organize the items shown at the POS.</p>
            </div>
            <a class="btn btn-coffee" href="{{ route('categories.create') }}"><i class="bi bi-plus-lg me-1"></i> Add category</a>
        </header>

        <div class="table-responsive">
            <table class="table categories-table align-middle mb-0">
                <thead>
                    <tr><th class="ps-4">Name</th><th>Icon</th><th>Menu items</th><th class="text-end pe-4">Actions</th></tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    @php
                        $iconKey = strtolower(trim($category->icon ?? ''));
                        $iconClass = match ($iconKey) {
                            'local food', 'local-food', 'food', 'basket2' => 'basket2',
                            'bun', 'egg-fried' => 'egg-fried',
                            'cup-hot' => 'cup-hot',
                            'cup-straw' => 'cup-straw',
                            'basket' => 'basket',
                            default => 'grid',
                        };
                        $menuItemCount = (int) $category->menu_items_count;
                        $iconLabel = $category->icon
                            ? ucwords(str_replace(['-', '_'], ' ', trim($category->icon)))
                            : 'Not set';
                    @endphp
                    <tr data-category-reveal>
                        <td class="ps-4"><span class="category-name">{{ $category->name }}</span></td>
                        <td>
                            <span class="category-icon-badge">
                                <i class="bi bi-{{ $iconClass }}" aria-hidden="true"></i>
                                <span>{{ $iconLabel }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="category-item-count">
                                <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>
                                <span>{{ $menuItemCount }}</span>
                                <span>{{ $menuItemCount === 1 ? 'item' : 'items' }}</span>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="category-action-group">
                                <a class="btn btn-sm btn-outline-secondary category-action category-action--edit" href="{{ route('categories.edit', $category) }}" aria-label="Edit {{ $category->name }}"><i class="bi bi-pencil-square" aria-hidden="true"></i> Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category and its unused menu items?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger category-action category-action--delete" type="submit" aria-label="Delete {{ $category->name }}"><i class="bi bi-trash3" aria-hidden="true"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="categories-empty text-center"><i class="bi bi-tags" aria-hidden="true"></i>No categories have been created.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="p-3 border-top">{{ $categories->links() }}</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('[data-category-reveal]').forEach((element, index) => {
            element.classList.add('categories-reveal');
            element.style.setProperty('--category-reveal-delay', `${Math.min(index, 8) * 35}ms`);
        });
    }
</script>
@endpush
