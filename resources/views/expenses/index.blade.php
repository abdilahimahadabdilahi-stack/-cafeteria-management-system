@extends('layouts.app')

@section('title', 'Expenses')
@section('eyebrow', 'FINANCE')
@section('heading', 'Expenses')

@section('content')
<div class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center gap-3">
        <div><h2 class="h5 mb-1">Expense records</h2><p class="small text-secondary mb-0">Track operating costs by date and staff member.</p></div>
        <a class="btn btn-coffee" href="{{ route('expenses.create') }}"><i class="bi bi-plus-lg me-1"></i> Record expense</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Date</th><th>Title</th><th>Recorded by</th><th>Amount</th><th>Notes</th><th class="text-end pe-4">Actions</th></tr></thead>
            <tbody>
            @forelse($expenses as $expense)
                <tr>
                    <td class="ps-4">{{ $expense->expense_date->format('Y-m-d') }}</td><td class="fw-semibold">{{ $expense->title }}</td><td>{{ $expense->user->name }}</td><td>{{ number_format((float) $expense->amount, 2) }}</td><td>{{ $expense->notes ?: '—' }}</td>
                    <td class="text-end pe-4">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('expenses.edit', $expense) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense record?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">No expenses have been recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())<div class="p-3">{{ $expenses->links() }}</div>@endif
</div>
@endsection
