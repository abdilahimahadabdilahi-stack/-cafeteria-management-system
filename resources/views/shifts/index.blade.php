@extends('layouts.app')

@section('title', 'Shifts')
@section('eyebrow', 'OPERATIONS')
@section('heading', 'Staff shifts')

@section('content')
<div class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center gap-3">
        <div><h2 class="h5 mb-1">Shift records</h2><p class="small text-secondary mb-0">Review opening and closing cash by staff shift.</p></div>
        <a class="btn btn-coffee" href="{{ route('shifts.create') }}"><i class="bi bi-plus-lg me-1"></i> Add shift</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Staff member</th><th>Start</th><th>End</th><th>Opening</th><th>Expected</th><th>Closing</th><th class="text-end pe-4">Actions</th></tr></thead>
            <tbody>
            @forelse($shifts as $shift)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $shift->user->name }}</td><td>{{ $shift->start_time->format('Y-m-d H:i') }}</td><td>{{ $shift->end_time?->format('Y-m-d H:i') ?: 'Open' }}</td>
                    <td>{{ number_format((float) $shift->opening_cash, 2) }}</td><td>{{ $shift->expected_cash === null ? '—' : number_format((float) $shift->expected_cash, 2) }}</td><td>{{ $shift->closing_cash === null ? '—' : number_format((float) $shift->closing_cash, 2) }}</td>
                    <td class="text-end pe-4">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('shifts.edit', $shift) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('shifts.destroy', $shift) }}" onsubmit="return confirm('Delete this shift record?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-5">No shifts have been recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($shifts->hasPages())<div class="p-3">{{ $shifts->links() }}</div>@endif
</div>
@endsection
