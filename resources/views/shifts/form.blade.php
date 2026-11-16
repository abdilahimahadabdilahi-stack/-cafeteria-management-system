@extends('layouts.app')

@section('title', $shift->exists ? 'Edit shift' : 'Add shift')
@section('eyebrow', 'OPERATIONS')
@section('heading', $shift->exists ? 'Edit shift' : 'Add shift')

@section('content')
<div class="card p-3 p-lg-4" style="max-width: 800px">
    <form method="POST" action="{{ $shift->exists ? route('shifts.update', $shift) : route('shifts.store') }}">
        @csrf
        @if($shift->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label for="user_id" class="form-label">Staff member</label>
                <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                    <option value="">Choose staff member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" @selected(old('user_id', $shift->user_id) == $member->id)>{{ $member->name }} · {{ ucfirst($member->role) }}</option>
                    @endforeach
                </select>
                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="start_time" class="form-label">Start time</label>
                <input id="start_time" name="start_time" type="datetime-local" value="{{ old('start_time', $shift->start_time?->format('Y-m-d\TH:i')) }}" class="form-control @error('start_time') is-invalid @enderror" required>
                @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="end_time" class="form-label">End time <span class="text-secondary">(optional)</span></label>
                <input id="end_time" name="end_time" type="datetime-local" value="{{ old('end_time', $shift->end_time?->format('Y-m-d\TH:i')) }}" class="form-control @error('end_time') is-invalid @enderror">
                @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="opening_cash" class="form-label">Opening cash</label>
                <input id="opening_cash" name="opening_cash" type="number" step="0.01" min="0" max="999999.99" value="{{ old('opening_cash', $shift->opening_cash) }}" class="form-control @error('opening_cash') is-invalid @enderror" required>
                @error('opening_cash')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="expected_cash" class="form-label">Expected cash <span class="text-secondary">(optional)</span></label>
                <input id="expected_cash" name="expected_cash" type="number" step="0.01" min="0" max="999999.99" value="{{ old('expected_cash', $shift->expected_cash) }}" class="form-control @error('expected_cash') is-invalid @enderror">
                @error('expected_cash')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="closing_cash" class="form-label">Closing cash <span class="text-secondary">(optional)</span></label>
                <input id="closing_cash" name="closing_cash" type="number" step="0.01" min="0" max="999999.99" value="{{ old('closing_cash', $shift->closing_cash) }}" class="form-control @error('closing_cash') is-invalid @enderror">
                @error('closing_cash')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 pt-2">
                <button class="btn btn-coffee" type="submit">{{ $shift->exists ? 'Save changes' : 'Create shift' }}</button>
                <a class="btn btn-outline-secondary ms-1" href="{{ route('shifts.index') }}">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
