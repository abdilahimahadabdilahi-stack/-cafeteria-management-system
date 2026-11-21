<?php

namespace App\Http\Controllers;

use App\Models\AccountSetting;
use App\Models\Shift;
use App\Models\User;
use App\Services\ShiftAccounting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(ShiftAccounting $accounting): View
    {
        $shifts = Shift::query()
            ->with('user')
            ->latest('start_time')
            ->latest('id')
            ->paginate(15);
        $shiftSummaries = $accounting->summariesFor($shifts->getCollection());

        return view('shifts.index', [
            'accountSetting' => AccountSetting::query()->findOrFail(1),
            'hasShifts' => Shift::query()->exists(),
            'shiftSummaries' => $shiftSummaries,
            'shifts' => $shifts,
        ]);
    }

    public function create(ShiftAccounting $accounting): View
    {
        $shift = new Shift(['start_time' => now()]);
        $accountSetting = AccountSetting::query()->findOrFail(1);
        $shift->opening_cash = $accounting->openingBalanceFor($shift->start_time, $accountSetting);

        return view('shifts.form', [
            'shift' => $shift,
            'summary' => $accounting->summaryFor($shift),
            'staff' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ShiftAccounting $accounting): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);

        DB::transaction(function () use ($attributes, $accounting): void {
            $accountSetting = AccountSetting::query()->lockForUpdate()->findOrFail(1);
            $this->ensureNoOverlappingShift($attributes);

            Shift::query()->create([
                'user_id' => $attributes['user_id'],
                'start_time' => $attributes['start_time'],
                'end_time' => $attributes['end_time'] ?? null,
                'opening_cash' => $accounting->openingBalanceFor(
                    Carbon::parse($attributes['start_time']),
                    $accountSetting,
                ),
                'closing_cash' => ($attributes['end_time'] ?? null) === null
                    ? null
                    : ($attributes['closing_cash'] ?? null),
            ]);

            $accounting->recalculateAll($accountSetting);
        });

        return redirect()->route('shifts.index')->with('success', 'Shift created.');
    }

    public function edit(Shift $shift, ShiftAccounting $accounting): View
    {
        return view('shifts.form', [
            'shift' => $shift,
            'summary' => $accounting->summaryFor($shift),
            'staff' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Shift $shift, ShiftAccounting $accounting): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request);

        DB::transaction(function () use ($attributes, $shift, $accounting): void {
            $accountSetting = AccountSetting::query()->lockForUpdate()->findOrFail(1);
            $this->ensureNoOverlappingShift($attributes, $shift);

            $shift->user_id = $attributes['user_id'];
            $shift->start_time = $attributes['start_time'];
            $shift->end_time = $attributes['end_time'] ?? null;
            $shift->closing_cash = $shift->end_time === null
                ? null
                : ($attributes['closing_cash'] ?? null);
            $shift->save();

            $accounting->recalculateAll($accountSetting);
        });

        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function updateStartingBalance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'starting_balance' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        DB::transaction(function () use ($validated): void {
            $accountSetting = AccountSetting::query()->lockForUpdate()->findOrFail(1);

            if (Shift::query()->exists()) {
                throw ValidationException::withMessages([
                    'starting_balance' => 'The account starting balance can only be changed before the first shift.',
                ]);
            }

            $accountSetting->update([
                'starting_balance' => $validated['starting_balance'],
            ]);
        });

        return redirect()->route('shifts.index')->with('success', 'Account starting balance updated.');
    }

    public function destroy(Shift $shift, ShiftAccounting $accounting): RedirectResponse
    {
        DB::transaction(function () use ($shift, $accounting): void {
            $accountSetting = AccountSetting::query()->lockForUpdate()->findOrFail(1);
            $shift->delete();
            $accounting->recalculateAll($accountSetting);
        });

        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }

    private function validatedAttributes(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('status', true)],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'closing_cash' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);
    }

    /**
     * @param  array{user_id: int|string, start_time: string, end_time?: string|null, closing_cash?: string|null}  $attributes
     */
    private function ensureNoOverlappingShift(array $attributes, ?Shift $except = null): void
    {
        $startTime = Carbon::parse($attributes['start_time']);
        $endTime = isset($attributes['end_time'])
            ? Carbon::parse($attributes['end_time'])
            : null;

        $overlappingShifts = Shift::query()
            ->when($except !== null, fn ($query) => $query->where('id', '<>', $except->getKey()))
            ->when($endTime !== null, fn ($query) => $query->where('start_time', '<', $endTime))
            ->where(function ($query) use ($startTime): void {
                $query
                    ->whereNull('end_time')
                    ->orWhere('end_time', '>', $startTime);
            });

        if ($overlappingShifts->exists()) {
            throw ValidationException::withMessages([
                'start_time' => 'This shift overlaps another shift. Complete the active shift before starting another.',
            ]);
        }
    }
}
