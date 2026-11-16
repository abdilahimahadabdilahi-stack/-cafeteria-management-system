<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $shifts = Shift::query()->with('user')->latest('start_time')->paginate(15);

        return view('shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        return view('shifts.form', [
            'shift' => new Shift(['start_time' => now()]),
            'staff' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Shift::query()->create($this->validatedAttributes($request));

        return redirect()->route('shifts.index')->with('success', 'Shift created.');
    }

    public function edit(Shift $shift): View
    {
        return view('shifts.form', [
            'shift' => $shift,
            'staff' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $shift->update($this->validatedAttributes($request));

        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }

    private function validatedAttributes(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('status', true)],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'opening_cash' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'closing_cash' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'expected_cash' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);
    }
}
