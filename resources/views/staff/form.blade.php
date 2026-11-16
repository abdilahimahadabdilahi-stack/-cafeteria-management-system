@extends('layouts.app')

@section('title', $staffMember->exists ? 'Edit staff' : 'Add staff')
@section('eyebrow', 'TEAM')
@section('heading', $staffMember->exists ? 'Edit staff account' : 'Create staff account')

@section('content')
<div class="card p-3 p-lg-4" style="max-width: 760px">
    <form method="POST" action="{{ $staffMember->exists ? route('staff.update', $staffMember) : route('staff.store') }}">
        @csrf
        @if($staffMember->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Full name</label>
                <input id="name" name="name" value="{{ old('name', $staffMember->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="username" class="form-label">Username</label>
                <input id="username" name="username" value="{{ old('username', $staffMember->username) }}" class="form-control @error('username') is-invalid @enderror" required autocomplete="username" maxlength="255">
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                    @foreach(['admin' => 'Admin', 'cashier' => 'Cashier', 'kitchen' => 'Kitchen / Barista'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $staffMember->role ?: 'cashier') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone <span class="text-secondary">(optional)</span></label>
                <input id="phone" name="phone" value="{{ old('phone', $staffMember->phone) }}" class="form-control @error('phone') is-invalid @enderror" maxlength="255">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Password {{ $staffMember->exists ? '(leave blank to keep current)' : '' }}</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="{{ $staffMember->exists ? 'new-password' : 'new-password' }}" {{ $staffMember->exists ? '' : 'required' }}>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <input type="hidden" name="status" value="0">
                <div class="form-check mb-2">
                    <input id="status" name="status" type="checkbox" class="form-check-input" value="1" @checked(old('status', $staffMember->exists ? $staffMember->status : true))>
                    <label class="form-check-label" for="status">Account active</label>
                </div>
            </div>
            <div class="col-12 pt-2">
                <button class="btn btn-coffee" type="submit">{{ $staffMember->exists ? 'Save changes' : 'Create account' }}</button>
                <a class="btn btn-outline-secondary ms-1" href="{{ route('staff.index') }}">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
