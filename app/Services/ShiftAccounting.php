<?php

namespace App\Services;

use App\Models\AccountSetting;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Shift;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ShiftAccounting
{
    /**
     * @param  EloquentCollection<int, Shift>  $shifts
     * @return array<int, array{sales_total: string, cash_collections: string, other_collections: string, expenses: string, net_movement: string, expected_cash: string}>
     */
    public function summariesFor(EloquentCollection $shifts, ?CarbonInterface $now = null): array
    {
        if ($shifts->isEmpty()) {
            return [];
        }

        $now ??= now();
        $summaryCents = [];

        foreach ($shifts->modelKeys() as $shiftId) {
            $summaryCents[$shiftId] = [
                'sales_total' => 0,
                'cash_collections' => 0,
                'other_collections' => 0,
                'expenses' => 0,
            ];
        }

        foreach (array_chunk($shifts->modelKeys(), 500) as $shiftIds) {
            $orders = Order::query()
                ->join('shifts', 'orders.user_id', '=', 'shifts.user_id')
                ->whereIn('shifts.id', $shiftIds)
                ->where('orders.status', 'completed')
                ->whereColumn('orders.created_at', '>=', 'shifts.start_time');
            $this->constrainToShiftEnd($orders, 'orders', $now);

            foreach ($orders
                ->selectRaw(
                    'shifts.id as shift_id, COALESCE(SUM(orders.total_amount), 0) as sales_total, COALESCE(SUM(CASE WHEN orders.payment_method = ? THEN orders.total_amount ELSE 0 END), 0) as cash_collections, COALESCE(SUM(CASE WHEN orders.payment_method <> ? THEN orders.total_amount ELSE 0 END), 0) as other_collections',
                    ['cash', 'cash'],
                )
                ->groupBy('shifts.id')
                ->get() as $orderSummary) {
                $summaryCents[$orderSummary->shift_id]['sales_total'] = $this->toCents($orderSummary->sales_total);
                $summaryCents[$orderSummary->shift_id]['cash_collections'] = $this->toCents($orderSummary->cash_collections);
                $summaryCents[$orderSummary->shift_id]['other_collections'] = $this->toCents($orderSummary->other_collections);
            }

            $expenses = Expense::query()
                ->join('shifts', 'expenses.user_id', '=', 'shifts.user_id')
                ->whereIn('shifts.id', $shiftIds)
                ->whereColumn('expenses.created_at', '>=', 'shifts.start_time');
            $this->constrainToShiftEnd($expenses, 'expenses', $now);

            foreach ($expenses
                ->selectRaw('shifts.id as shift_id, COALESCE(SUM(expenses.amount), 0) as expenses')
                ->groupBy('shifts.id')
                ->get() as $expenseSummary) {
                $summaryCents[$expenseSummary->shift_id]['expenses'] = $this->toCents($expenseSummary->expenses);
            }
        }

        $summaries = [];

        foreach ($shifts as $shift) {
            $totals = $summaryCents[$shift->getKey()];
            $netMovement = $totals['cash_collections'] - $totals['expenses'];
            $expectedCash = $this->toCents($shift->opening_cash) + $netMovement;

            $summaries[$shift->getKey()] = [
                'sales_total' => $this->formatCents($totals['sales_total']),
                'cash_collections' => $this->formatCents($totals['cash_collections']),
                'other_collections' => $this->formatCents($totals['other_collections']),
                'expenses' => $this->formatCents($totals['expenses']),
                'net_movement' => $this->formatCents($netMovement),
                'expected_cash' => $this->formatCents($expectedCash),
            ];
        }

        return $summaries;
    }

    public function openingBalanceFor(CarbonInterface $startTime, AccountSetting $settings): string
    {
        $previousShift = Shift::query()
            ->whereNotNull('end_time')
            ->where('end_time', '<=', $startTime)
            ->orderByDesc('end_time')
            ->orderByDesc('id')
            ->first();

        if ($previousShift === null) {
            return $settings->starting_balance;
        }

        return $previousShift->closing_cash
            ?? $previousShift->expected_cash
            ?? $settings->starting_balance;
    }

    public function recalculateAll(AccountSetting $settings): void
    {
        $shifts = Shift::query()
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        if ($shifts->isEmpty()) {
            return;
        }

        $summaries = $this->summariesFor($shifts);
        $completedShifts = $shifts
            ->filter(fn (Shift $shift): bool => $shift->end_time !== null)
            ->all();

        usort($completedShifts, function (Shift $firstShift, Shift $secondShift): int {
            return ($firstShift->end_time <=> $secondShift->end_time)
                ?: ($firstShift->id <=> $secondShift->id);
        });

        $previousBalance = $this->toCents($settings->starting_balance);
        $completedShiftIndex = 0;

        foreach ($shifts as $shift) {
            while (
                isset($completedShifts[$completedShiftIndex])
                && $completedShifts[$completedShiftIndex]->id !== $shift->id
                && $completedShifts[$completedShiftIndex]->end_time <= $shift->start_time
            ) {
                $previousShift = $completedShifts[$completedShiftIndex];
                $previousBalance = $previousShift->closing_cash !== null
                    ? $this->toCents($previousShift->closing_cash)
                    : $this->toCents($previousShift->expected_cash);
                $completedShiftIndex++;
            }

            $shift->opening_cash = $this->formatCents($previousBalance);
            $shift->expected_cash = $this->formatCents(
                $previousBalance
                    + $this->toCents($summaries[$shift->getKey()]['cash_collections'])
                    - $this->toCents($summaries[$shift->getKey()]['expenses']),
            );
            $shift->save();
        }
    }

    /**
     * @return array{sales_total: string, cash_collections: string, other_collections: string, expenses: string, net_movement: string, expected_cash: string}
     */
    public function summaryFor(Shift $shift): array
    {
        if (! $shift->exists) {
            $openingBalance = $shift->opening_cash ?? '0.00';

            return [
                'sales_total' => '0.00',
                'cash_collections' => '0.00',
                'other_collections' => '0.00',
                'expenses' => '0.00',
                'net_movement' => '0.00',
                'expected_cash' => $this->formatCents($this->toCents($openingBalance)),
            ];
        }

        $shifts = new EloquentCollection([$shift]);

        return $this->summariesFor($shifts)[$shift->getKey()];
    }

    private function constrainToShiftEnd(Builder $query, string $table, CarbonInterface $now): void
    {
        $query->where(function (Builder $query) use ($table, $now): void {
            $query
                ->where(function (Builder $query) use ($table): void {
                    $query
                        ->whereNotNull('shifts.end_time')
                        ->whereColumn($table.'.created_at', '<', 'shifts.end_time');
                })
                ->orWhere(function (Builder $query) use ($table, $now): void {
                    $query
                        ->whereNull('shifts.end_time')
                        ->where($table.'.created_at', '<=', $now);
                });
        });
    }

    private function toCents(mixed $amount): int
    {
        return (int) round((float) ($amount ?? 0) * 100);
    }

    private function formatCents(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
