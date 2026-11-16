@extends('layouts.app')

@section('title', $menuItem->exists ? 'Edit menu item' : 'Add menu item')
@section('eyebrow', 'CATALOG')
@section('heading', $menuItem->exists ? 'Edit menu item' : 'Add menu item')

@section('content')
<div class="card p-3 p-lg-4" style="max-width: 760px">
    <form method="POST" action="{{ $menuItem->exists ? route('menu.update', $menuItem) : route('menu.store') }}">
        @csrf
        @if($menuItem->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Item name</label>
                <input id="name" name="name" value="{{ old('name', $menuItem->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                    <option value="">Choose category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $menuItem->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="price" class="form-label">Selling price</label>
                <input id="price" name="price" type="number" step="0.01" min="0" max="999999.99" value="{{ old('price', $menuItem->price) }}" class="form-control @error('price') is-invalid @enderror" required>
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="cost_price" class="form-label">Cost price <span class="text-secondary">(optional)</span></label>
                <input id="cost_price" name="cost_price" type="number" step="0.01" min="0" max="999999.99" value="{{ old('cost_price', $menuItem->cost_price) }}" class="form-control @error('cost_price') is-invalid @enderror">
                @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label for="image" class="form-label">Image URL or path <span class="text-secondary">(optional)</span></label>
                <input id="image" name="image" value="{{ old('image', $menuItem->image) }}" class="form-control @error('image') is-invalid @enderror" maxlength="255">
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <input type="hidden" name="is_available" value="0">
                <div class="form-check">
                    <input id="is_available" name="is_available" type="checkbox" value="1" class="form-check-input" @checked(old('is_available', $menuItem->exists ? $menuItem->is_available : true))>
                    <label class="form-check-label" for="is_available">Available for sale</label>
                </div>
            </div>
            <div class="col-12 d-flex gap-2 pt-2">
                <button class="btn btn-coffee" type="submit">{{ $menuItem->exists ? 'Save changes' : 'Create item' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('menu.index') }}">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
