<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CafeteriaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_login_redirects_to_the_pos(): void
    {
        $cashier = User::factory()->create([
            'username' => 'pos-cashier',
            'password' => 'secret-password',
            'role' => 'cashier',
        ]);

        $this->post(route('login.store'), [
            'username' => $cashier->username,
            'password' => 'secret-password',
        ])->assertRedirect(route('pos.index'));

        $this->assertAuthenticatedAs($cashier);

        $this->get(route('pos.index'))
            ->assertOk()
            ->assertSee('Point of sale');
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        $inactiveUser = User::factory()->create([
            'username' => 'inactive-staff',
            'password' => 'secret-password',
            'status' => false,
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'username' => $inactiveUser->username,
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_cashier_cannot_open_menu_management(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('menu.index'))
            ->assertForbidden();
    }

    public function test_admin_and_kitchen_users_land_on_their_role_dashboards(): void
    {
        $admin = User::factory()->create([
            'username' => 'cafeteria-admin',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
        $kitchenUser = User::factory()->create([
            'username' => 'kitchen-user',
            'password' => 'secret-password',
            'role' => 'kitchen',
        ]);

        $this->post(route('login.store'), [
            'username' => $admin->username,
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'));

        auth()->logout();

        $this->post(route('login.store'), [
            'username' => $kitchenUser->username,
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Orders to prepare');
    }

    public function test_logout_invalidates_the_authenticated_session(): void
    {
        $cashier = User::factory()->create();

        $response = $this->actingAs($cashier)
            ->withSession(['temporary-value' => 'must be removed'])
            ->post(route('logout'));

        $response->assertRedirect(route('login'))
            ->assertSessionMissing('temporary-value');
        $this->assertGuest();
    }

    public function test_pos_records_order_and_lines_using_database_prices(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::query()->create(['name' => 'Shaah']);
        $menuItem = $category->menuItems()->create([
            'name' => 'Shaah cadays',
            'price' => '4.25',
            'cost_price' => null,
        ]);

        $this->actingAs($cashier)
            ->post(route('pos.store'), [
                'table_number' => '5',
                'payment_method' => 'zaad',
                'discount' => '1.00',
                'items' => [
                    ['menu_item_id' => $menuItem->id, 'quantity' => 2],
                ],
            ])
            ->assertRedirect(route('pos.index'));

        $order = Order::query()->sole();

        $this->assertSame('8.50', $order->subtotal);
        $this->assertSame('1.00', $order->discount);
        $this->assertSame('7.50', $order->total_amount);
        $this->assertSame('zaad', $order->payment_method);
        $this->assertSame('completed', $order->status);
        $this->assertSame($cashier->id, $order->user_id);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => 2,
            'unit_price' => '4.25',
            'total_price' => '8.50',
        ]);
    }

    public function test_pos_rejects_discount_larger_than_subtotal_without_creating_an_order(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::query()->create(['name' => 'Sharaab']);
        $menuItem = $category->menuItems()->create([
            'name' => 'Biyo',
            'price' => '2.00',
        ]);

        $this->actingAs($cashier)
            ->from(route('pos.index'))
            ->post(route('pos.store'), [
                'payment_method' => 'cash',
                'discount' => '2.01',
                'items' => [
                    ['menu_item_id' => $menuItem->id, 'quantity' => 1],
                ],
            ])
            ->assertRedirect(route('pos.index'))
            ->assertSessionHasErrors('discount');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_admin_can_create_update_and_delete_menu_and_inventory_items(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create(['name' => 'Fadiirad']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Cafeteria dashboard');

        $this->actingAs($admin)
            ->post(route('menu.store'), [
                'category_id' => $category->id,
                'name' => 'Canjeero',
                'price' => '3.50',
                'cost_price' => '',
                'image' => '',
                'is_available' => '1',
            ])
            ->assertRedirect(route('menu.index'));

        $menuItem = MenuItem::query()->sole();

        $this->put(route('menu.update', $menuItem), [
            'category_id' => $category->id,
            'name' => 'Canjeero cusub',
            'price' => '4.00',
            'cost_price' => '',
            'image' => '',
            'is_available' => '1',
        ])->assertRedirect(route('menu.index'));

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuItem->id,
            'name' => 'Canjeero cusub',
            'price' => '4.00',
        ]);

        $this->delete(route('menu.destroy', $menuItem))->assertRedirect(route('menu.index'));
        $this->assertDatabaseMissing('menu_items', ['id' => $menuItem->id]);

        $this->post(route('inventory.store'), [
            'item_name' => 'Caano',
            'unit' => 'liter',
            'quantity' => '12.50',
            'reorder_level' => '3.00',
        ])->assertRedirect(route('inventory.index'));

        $inventoryItem = Inventory::query()->sole();

        $this->put(route('inventory.update', $inventoryItem), [
            'item_name' => 'Caano cusub',
            'unit' => 'liter',
            'quantity' => '10.50',
            'reorder_level' => '3.00',
        ])->assertRedirect(route('inventory.index'));

        $this->assertDatabaseHas('inventory', [
            'id' => $inventoryItem->id,
            'item_name' => 'Caano cusub',
            'quantity' => '10.50',
        ]);

        $this->delete(route('inventory.destroy', $inventoryItem))->assertRedirect(route('inventory.index'));
        $this->assertDatabaseMissing('inventory', ['id' => $inventoryItem->id]);
    }

    public function test_admin_can_manage_categories_staff_expenses_and_shifts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('categories.store'), ['name' => 'Cunto Waddani', 'icon' => 'basket'])
            ->assertRedirect(route('categories.index'));
        $category = Category::query()->sole();

        $this->put(route('categories.update', $category), ['name' => 'Cunto', 'icon' => 'basket'])
            ->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Cunto']);
        $this->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        $this->post(route('staff.store'), [
            'name' => 'Kitchen Staff',
            'username' => 'kitchen-staff',
            'password' => 'password-123',
            'role' => 'kitchen',
            'phone' => '',
            'status' => '1',
        ])->assertRedirect(route('staff.index'));

        $staffMember = User::query()->where('username', 'kitchen-staff')->sole();
        $this->assertTrue(Hash::check('password-123', $staffMember->password));

        $this->put(route('staff.update', $staffMember), [
            'name' => 'Kitchen Lead',
            'username' => 'kitchen-lead',
            'password' => '',
            'role' => 'kitchen',
            'phone' => '',
            'status' => '0',
        ])->assertRedirect(route('staff.index'));
        $this->assertDatabaseHas('users', [
            'id' => $staffMember->id,
            'username' => 'kitchen-lead',
            'status' => 0,
        ]);
        $this->assertTrue(Hash::check('password-123', $staffMember->fresh()->password));

        $this->delete(route('staff.destroy', $staffMember))
            ->assertRedirect(route('staff.index'));
        $this->assertDatabaseMissing('users', ['id' => $staffMember->id]);

        $this->post(route('expenses.store'), [
            'user_id' => $admin->id,
            'title' => 'Coffee beans',
            'amount' => '15.50',
            'expense_date' => today()->toDateString(),
            'notes' => '',
        ])->assertRedirect(route('expenses.index'));
        $expense = Expense::query()->sole();

        $this->put(route('expenses.update', $expense), [
            'user_id' => $admin->id,
            'title' => 'Coffee beans delivery',
            'amount' => '18.50',
            'expense_date' => today()->toDateString(),
            'notes' => 'Weekly stock',
        ])->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => '18.50']);
        $this->delete(route('expenses.destroy', $expense))
            ->assertRedirect(route('expenses.index'));
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);

        $startTime = now()->startOfMinute();
        $this->post(route('shifts.store'), [
            'user_id' => $admin->id,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'opening_cash' => '40.00',
            'expected_cash' => '',
            'closing_cash' => '',
            'end_time' => '',
        ])->assertRedirect(route('shifts.index'));
        $shift = Shift::query()->sole();

        $this->put(route('shifts.update', $shift), [
            'user_id' => $admin->id,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $startTime->copy()->addHours(8)->format('Y-m-d H:i:s'),
            'opening_cash' => '40.00',
            'expected_cash' => '125.00',
            'closing_cash' => '120.00',
        ])->assertRedirect(route('shifts.index'));
        $this->assertDatabaseHas('shifts', ['id' => $shift->id, 'closing_cash' => '120.00']);
        $this->delete(route('shifts.destroy', $shift))
            ->assertRedirect(route('shifts.index'));
        $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
    }

    public function test_admin_can_manage_order_lines_and_cancel_without_deleting_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::query()->create(['name' => 'Sharaab']);
        $firstItem = $category->menuItems()->create(['name' => 'Shaah', 'price' => '2.50']);
        $secondItem = $category->menuItems()->create(['name' => 'Biyo', 'price' => '1.25']);

        $this->actingAs($admin)
            ->post(route('orders.store'), [
                'user_id' => $cashier->id,
                'table_number' => '7',
                'payment_method' => 'cash',
                'discount' => '0.50',
                'status' => 'pending',
                'items' => [
                    ['menu_item_id' => $firstItem->id, 'quantity' => 2],
                ],
            ])
            ->assertRedirect();
        $order = Order::query()->sole();

        $this->post(route('orders.items.store', $order), [
            'menu_item_id' => $secondItem->id,
            'quantity' => 2,
        ])->assertRedirect(route('orders.show', $order));
        $addedLine = $order->orderItems()->where('menu_item_id', $secondItem->id)->sole();

        $this->assertSame('7.50', $order->fresh()->subtotal);
        $this->assertSame('7.00', $order->fresh()->total_amount);

        $this->put(route('orders.items.update', [$order, $addedLine]), [
            'menu_item_id' => $secondItem->id,
            'quantity' => 3,
        ])->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('order_items', [
            'id' => $addedLine->id,
            'quantity' => 3,
            'total_price' => '3.75',
        ]);

        $this->delete(route('orders.items.destroy', [$order, $addedLine]))
            ->assertRedirect(route('orders.show', $order));
        $remainingLine = $order->orderItems()->sole();
        $this->delete(route('orders.items.destroy', [$order, $remainingLine]))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('order_items', ['id' => $remainingLine->id]);

        $this->from(route('orders.edit', $order))
            ->put(route('orders.update', $order), [
            'user_id' => $cashier->id,
            'table_number' => '7',
            'payment_method' => 'zaad',
            'discount' => '0.00',
            'status' => 'completed',
            ])->assertSessionHasErrors();
        $this->assertSame('completed', $order->fresh()->status);

        $this->delete(route('orders.destroy', $order))
            ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('order_items', ['id' => $remainingLine->id]);
    }
}
