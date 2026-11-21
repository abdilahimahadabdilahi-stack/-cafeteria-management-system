<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ShiftAccountingTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_uses_configured_starting_balance_and_summarizes_handover_activity(): void
    {
        $this->travelTo('2026-11-21 08:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $firstCashier = User::factory()->create(['name' => 'Ayaan']);
        $nextCashier = User::factory()->create(['name' => 'Hodan']);
        $otherCashier = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('shifts.starting-balance.update'), [
                'starting_balance' => '100.00',
            ])
            ->assertRedirect(route('shifts.index'));

        $this->post(route('shifts.store'), [
            'user_id' => $firstCashier->id,
            'start_time' => '2026-11-21 08:00:00',
        ])->assertRedirect(route('shifts.index'));

        $firstShift = Shift::query()->sole();
        $this->assertSame('100.00', $firstShift->opening_cash);
        $this->assertSame('100.00', $firstShift->expected_cash);

        $this->patch(route('shifts.starting-balance.update'), [
            'starting_balance' => '500.00',
        ])->assertSessionHasErrors('starting_balance');
        $this->assertDatabaseHas('account_settings', ['id' => 1, 'starting_balance' => '100.00']);

        $this->recordOrder($firstCashier, 'TEST-CASH-1', 'cash', '40.00', 'completed', '2026-11-21 09:00:00');
        $this->recordOrder($firstCashier, 'TEST-DIGITAL-1', 'edahab', '25.00', 'completed', '2026-11-21 10:00:00');
        $this->recordOrder($firstCashier, 'TEST-PENDING-1', 'cash', '99.00', 'pending', '2026-11-21 10:30:00');
        $this->recordOrder($otherCashier, 'TEST-OTHER-1', 'cash', '50.00', 'completed', '2026-11-21 10:45:00');
        $this->recordOrder($firstCashier, 'TEST-BOUNDARY-1', 'cash', '7.00', 'completed', '2026-11-21 12:00:00');
        $this->recordExpense($firstCashier, 'TEST-EXPENSE-1', '8.00', '2026-11-21 10:00:00');

        $this->travelTo('2026-11-21 12:00:00');

        $this->put(route('shifts.update', $firstShift), [
            'user_id' => $firstCashier->id,
            'start_time' => '2026-11-21 08:00:00',
            'end_time' => '2026-11-21 12:00:00',
            'closing_cash' => '120.00',
        ])->assertRedirect(route('shifts.index'));

        $firstShift->refresh();
        $this->assertSame('100.00', $firstShift->opening_cash);
        $this->assertSame('132.00', $firstShift->expected_cash);
        $this->assertSame('120.00', $firstShift->closing_cash);

        $this->post(route('shifts.store'), [
            'user_id' => $nextCashier->id,
            'start_time' => '2026-11-21 12:00:00',
        ])->assertRedirect(route('shifts.index'));

        $nextShift = Shift::query()->where('user_id', $nextCashier->id)->sole();
        $this->assertSame('120.00', $nextShift->opening_cash);

        $this->recordOrder($nextCashier, 'TEST-CASH-2', 'cash', '5.00', 'completed', '2026-11-21 12:00:00');

        $this->get(route('shifts.index'))
            ->assertOk()
            ->assertSee('Ayaan')
            ->assertSee('Hodan')
            ->assertSee('65.00')
            ->assertSee('25.00')
            ->assertSee('8.00')
            ->assertSee('132.00')
            ->assertSee('120.00');
    }

    public function test_zero_activity_shift_carries_its_actual_closing_balance_to_the_next_same_day_shift(): void
    {
        $this->travelTo('2026-11-21 08:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $firstCashier = User::factory()->create();
        $nextCashier = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('shifts.starting-balance.update'), [
                'starting_balance' => '25.00',
            ])
            ->assertRedirect(route('shifts.index'));

        $this->post(route('shifts.store'), [
            'user_id' => $firstCashier->id,
            'start_time' => '2026-11-21 08:00:00',
        ])->assertRedirect(route('shifts.index'));

        $firstShift = Shift::query()->sole();

        $this->travelTo('2026-11-21 09:00:00');
        $this->put(route('shifts.update', $firstShift), [
            'user_id' => $firstCashier->id,
            'start_time' => '2026-11-21 08:00:00',
            'end_time' => '2026-11-21 09:00:00',
        ])->assertRedirect(route('shifts.index'));

        $this->post(route('shifts.store'), [
            'user_id' => $nextCashier->id,
            'start_time' => '2026-11-21 09:00:00',
        ])->assertRedirect(route('shifts.index'));

        $nextShift = Shift::query()->where('user_id', $nextCashier->id)->sole();
        $this->assertSame('25.00', $firstShift->fresh()->expected_cash);
        $this->assertSame('25.00', $nextShift->opening_cash);
        $this->assertSame('25.00', $nextShift->expected_cash);

        $this->get(route('shifts.index'))
            ->assertOk()
            ->assertSee('0.00');
    }

    public function test_overlapping_account_shifts_are_rejected(): void
    {
        $this->travelTo('2026-11-21 08:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $firstCashier = User::factory()->create();
        $nextCashier = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('shifts.store'), [
                'user_id' => $firstCashier->id,
                'start_time' => '2026-11-21 08:00:00',
            ])
            ->assertRedirect(route('shifts.index'));

        $response = $this->from(route('shifts.create'))
            ->post(route('shifts.store'), [
                'user_id' => $nextCashier->id,
                'start_time' => '2026-11-21 09:00:00',
            ]);

        $response->assertRedirect(route('shifts.create'))
            ->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('shifts', 1);
        $this->assertSame(
            'This shift overlaps another shift. Complete the active shift before starting another.',
            session('errors')->getBag('default')->first('start_time'),
        );
    }

    private function recordOrder(
        User $staffMember,
        string $orderNumber,
        string $paymentMethod,
        string $amount,
        string $status,
        string $createdAt,
    ): Order {
        $order = $staffMember->orders()->create([
            'order_number' => $orderNumber,
            'subtotal' => $amount,
            'discount' => '0.00',
            'total_amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => $status,
        ]);
        $order->created_at = Carbon::parse($createdAt);
        $order->updated_at = Carbon::parse($createdAt);
        $order->save();

        return $order;
    }

    private function recordExpense(User $staffMember, string $title, string $amount, string $createdAt): Expense
    {
        $expense = $staffMember->expenses()->create([
            'title' => $title,
            'amount' => $amount,
            'expense_date' => Carbon::parse($createdAt)->toDateString(),
        ]);
        $expense->created_at = Carbon::parse($createdAt);
        $expense->updated_at = Carbon::parse($createdAt);
        $expense->save();

        return $expense;
    }
}
