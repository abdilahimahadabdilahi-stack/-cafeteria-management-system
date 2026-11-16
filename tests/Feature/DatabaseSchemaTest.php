<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_cafeteria_categories_are_seeded_idempotently(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('categories', 4);
        $this->assertDatabaseHas('categories', [
            'name' => 'Cunto Waddani',
            'icon' => 'basket',
        ]);
    }

    public function test_related_records_can_be_created_through_cafeteria_relationships(): void
    {
        $cashier = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Shaah',
            'icon' => 'cup-hot',
        ]);
        $menuItem = $category->menuItems()->create([
            'name' => 'Shaah cadays',
            'price' => 2.50,
            'cost_price' => null,
        ]);
        $order = $cashier->orders()->create([
            'order_number' => 'ORD-20261116-0001',
            'table_number' => '1',
            'subtotal' => 5.00,
            'discount' => 0,
            'total_amount' => 5.00,
            'payment_method' => 'cash',
        ]);
        $orderItem = $order->orderItems()->create([
            'menu_item_id' => $menuItem->id,
            'quantity' => 2,
            'unit_price' => 2.50,
            'total_price' => 5.00,
        ]);
        $expense = $cashier->expenses()->create([
            'title' => 'Milk',
            'amount' => 10.00,
            'expense_date' => today(),
        ]);
        $shift = $cashier->shifts()->create([
            'start_time' => now(),
            'opening_cash' => 100.00,
        ]);

        $this->assertSame($category->id, $menuItem->category->id);
        $this->assertSame($cashier->id, $order->user->id);
        $this->assertSame($order->id, $orderItem->order->id);
        $this->assertSame($menuItem->id, $orderItem->menuItem->id);
        $this->assertSame($cashier->id, $expense->user->id);
        $this->assertSame($cashier->id, $shift->user->id);
        $this->assertTrue($cashier->status);
        $this->assertNull($menuItem->cost_price);
    }

    public function test_deleting_a_category_cascades_to_its_menu_items(): void
    {
        $category = Category::query()->create(['name' => 'Shaah']);
        $menuItem = $category->menuItems()->create([
            'name' => 'Shaah cadays',
            'price' => 2.50,
        ]);

        $category->delete();

        $this->assertDatabaseMissing('menu_items', ['id' => $menuItem->id]);
    }
}
