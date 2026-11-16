@extends('layouts.app')

@section('title', $expense->exists ? 'Edit expense' : 'Record expense')
@section('eyebrow', 'FINANCE')
@section('heading', $expense->exists ? 'Edit expense' : 'Record expense')

@section('content')
<div class="card p-3 p-lg-4" style="max-width: 760px">
    <form method="POST" action="{{ $expense->exists ? route('expenses.update', $expense) : route('expenses.store') }}">
        @csrf
        @if($expense->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label for="title" class="form-label">Expense title</label>
                <input id="title" name="title" value="{{ old('title', $expense->title) }}" class="form-control @error('title') is-invalid @enderror" required maxlength="255">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="user_id" class="form-label">Recorded by</label>
                <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                    <option value="">Choose staff member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" @selected(old('user_id', $expense->user_id ?: auth()->id()) == $member->id)>{{ $member->name }}</option>
                    @endforeach
                </select>
                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="amount" class="form-label">Amount</label>
                <input id="amount" name="amount" type="number" step="0.01" min="0.01" max="999999.99" value="{{ old('amount', $expense->amount) }}" class="form-control @error('amount') is-invalid @enderror" required>
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="expense_date" class="form-label">Expense date</label>
                <input id="expense_date" name="expense_date" type="date" value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}" class="form-control @error('expense_date') is-invalid @enderror" required>
                @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label for="notes" class="form-label">Notes <span class="text-secondary">(optional)</span></label>
                <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $expense->notes) }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 pt-2">
                <button class="btn btn-coffee" type="submit">{{ $expense->exists ? 'Save changes' : 'Record expense' }}</button>
                <a class="btn btn-outline-secondary ms-1" href="{{ route('expenses.index') }}">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
