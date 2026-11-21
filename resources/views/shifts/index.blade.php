@extends('layouts.app')

@section('title', 'Shifts')
@section('eyebrow', 'OPERATIONS')
@section('heading', 'Staff shifts')

@section('content')
@if(!$hasShifts)
<div class="card p-3 p-lg-4 mb-3">
    <h2 class="h5 mb-1">Account starting balance</h2>
    <p class="small text-secondary mb-3">This balance will be used as the opening balance for the account's first shift.</p>
    <form class="row g-3 align-items-end" method="POST" action="{{ route('shifts.starting-balance.update') }}">
        @csrf
        @method('PATCH')
        <div class="col-md-6">
            <label for="starting_balance" class="form-label">Starting balance</label>
            <input id="starting_balance" name="starting_balance" type="number" step="0.01" min="0" max="999999.99" value="{{ old('starting_balance', $accountSetting->starting_balance) }}" class="form-control @error('starting_balance') is-invalid @enderror" required>
            @error('starting_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-auto"><button class="btn btn-coffee" type="submit">Save starting balance</button></div>
    </form>
</div>
@endif
<div class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center gap-3">
        <div><h2 class="h5 mb-1">Shift records</h2><p class="small text-secondary mb-0">Review each staff member's sales, cash movement, and handover balance.</p></div>
        <a class="btn btn-coffee" href="{{ route('shifts.create') }}"><i class="bi bi-plus-lg me-1"></i> Add shift</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Staff member</th><th>Shift period / status</th><th>Previous balance</th><th>Total sales</th><th>Cash collected</th><th>Non-cash collections</th><th>Expenses</th><th>Net movement</th><th>Expected closing</th><th>Actual closing</th><th class="text-end pe-4">Actions</th></tr></thead>
            <tbody>
            @forelse($shifts as $shift)
                @php($summary = $shiftSummaries[$shift->id])
                <tr>
                    <td class="ps-4 fw-semibold">{{ $shift->user->name }}</td>
                    <td>
                        <div>{{ $shift->start_time->format('Y-m-d H:i') }} - {{ $shift->end_time?->format('Y-m-d H:i') ?: 'Now' }}</div>
                        <span class="badge rounded-pill {{ $shift->end_time === null ? 'text-bg-warning' : 'text-bg-success' }}">{{ $shift->end_time === null ? 'Active' : 'Completed' }}</span>
                    </td>
                    <td>{{ number_format((float) $shift->opening_cash, 2) }}</td>
                    <td>{{ number_format((float) $summary['sales_total'], 2) }}</td>
                    <td>{{ number_format((float) $summary['cash_collections'], 2) }}</td>
                    <td>{{ number_format((float) $summary['other_collections'], 2) }}</td>
                    <td>{{ number_format((float) $summary['expenses'], 2) }}</td>
                    <td>{{ number_format((float) $summary['net_movement'], 2) }}</td>
                    <td>{{ number_format((float) $summary['expected_cash'], 2) }}</td>
                    <td>{{ $shift->closing_cash === null ? '—' : number_format((float) $shift->closing_cash, 2) }}</td>
                    <td class="text-end pe-4">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('shifts.edit', $shift) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('shifts.destroy', $shift) }}" onsubmit="return confirm('Delete this shift record?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-secondary py-5">No shifts have been recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($shifts->hasPages())<div class="p-3">{{ $shifts->links() }}</div>@endif
</div>
@endsection
