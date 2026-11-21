@props(['menuItem'])

@php
    $menuText = strtolower($menuItem->name.' '.($menuItem->category?->name ?? ''));
    $fallbackPhoto = match (true) {
        str_contains($menuText, 'avocado'), str_contains($menuText, 'avacado'), str_contains($menuText, 'smoothie') => 'photo-1622597467836-f3285f2131b8',
        str_contains($menuText, 'chicken'), str_contains($menuText, 'digaag') => 'photo-1532550907401-a500c9a57435',
        str_contains($menuText, 'cunto'), str_contains($menuText, 'wadan'), str_contains($menuText, 'somali') => 'photo-1547592180-85f173990554',
        str_contains($menuText, 'fadiirad'), str_contains($menuText, 'bread'), str_contains($menuText, 'rooti') => 'photo-1509440159596-0249088772ff',
        str_contains($menuText, 'coffee'), str_contains($menuText, 'large cup'), str_contains($menuText, 'latte'), str_contains($menuText, 'qaxwo') => 'photo-1509042239860-f550ce710b93',
        str_contains($menuText, 'shaah'), str_contains($menuText, 'tea') => 'photo-1544787219-7f47ccb76574',
        default => 'photo-1547592180-85f173990554',
    };
    $fallbackImage = 'https://images.unsplash.com/'.$fallbackPhoto.'?auto=format&fit=crop&w=800&q=80';
    $hasCustomImage = filled($menuItem->image);
    $imageSource = $hasCustomImage
        ? (preg_match('/^https?:\/\//i', $menuItem->image) === 1 ? $menuItem->image : asset(ltrim($menuItem->image, '/')))
        : $fallbackImage;
    $categoryIcon = $menuItem->category?->icon ?: 'cup-hot';
@endphp

<div class="menu-item-photo" data-menu-photo>
    <div class="menu-item-photo__placeholder" aria-hidden="true">
        <i class="bi bi-{{ $categoryIcon }}"></i>
        <span>Image unavailable</span>
    </div>
    <img
        src="{{ $imageSource }}"
        data-fallback-src="{{ $fallbackImage }}"
        data-using-fallback="{{ $hasCustomImage ? 'false' : 'true' }}"
        alt="{{ $menuItem->name }}"
        class="object-fit-cover"
        loading="lazy"
        decoding="async"
    >
</div>
