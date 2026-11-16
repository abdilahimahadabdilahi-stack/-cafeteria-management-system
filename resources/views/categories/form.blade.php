@extends('layouts.app')

@section('title', $category->exists ? 'Edit category' : 'Add category')
@section('eyebrow', 'MENU SETUP')
@section('heading', $category->exists ? 'Edit category' : 'Add category')

@section('content')
<div class="card p-3 p-lg-4" style="max-width: 640px">
    <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}">
        @csrf
        @if($category->exists) @method('PUT') @endif
        <div class="mb-3">
            <label for="name" class="form-label">Category name</label>
            <input id="name" name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label for="icon" class="form-label">Bootstrap icon name <span class="text-secondary">(optional)</span></label>
            <input id="icon" name="icon" value="{{ old('icon', $category->icon) }}" class="form-control @error('icon') is-invalid @enderror" maxlength="100" placeholder="cup-hot">
            @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-coffee" type="submit">{{ $category->exists ? 'Save changes' : 'Create category' }}</button>
        <a class="btn btn-outline-secondary ms-1" href="{{ route('categories.index') }}">Cancel</a>
    </form>
</div>
@endsection
