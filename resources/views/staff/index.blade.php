@extends('layouts.app')

@section('title', 'Staff')
@section('eyebrow', 'TEAM')
@section('heading', 'Staff accounts')

@section('content')
<div class="card overflow-hidden">
    <div class="p-3 p-lg-4 d-flex justify-content-between align-items-center gap-3">
        <div><h2 class="h5 mb-1">Staff management</h2><p class="small text-secondary mb-0">Manage account roles and access.</p></div>
        <a class="btn btn-coffee" href="{{ route('staff.create') }}"><i class="bi bi-person-plus me-1"></i> Add staff</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Name</th><th>Username</th><th>Phone</th><th>Role</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $user->name }}</td><td>{{ $user->username }}</td><td>{{ $user->phone ?: '—' }}</td><td class="text-capitalize">{{ $user->role }}</td>
                    <td><span class="badge rounded-pill {{ $user->status ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->status ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-end pe-4">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.edit', $user) }}">Edit</a>
                        <form class="d-inline" method="POST" action="{{ route('staff.destroy', $user) }}" onsubmit="return confirm('Delete this staff account? Accounts with transaction history must be deactivated instead.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-5">No staff accounts found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="p-3">{{ $users->links() }}</div>@endif
</div>
@endsection
