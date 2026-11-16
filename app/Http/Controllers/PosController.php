<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->with(['menuItems' => fn ($query) => $query->where('is_available', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', 'in:cash,zaad,edahab'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'distinct', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $quantities = collect($validated['items'])
            ->mapWithKeys(fn (array $item): array => [$item['menu_item_id'] => (int) $item['quantity']]);

        $discountCents = (int) round((float) ($validated['discount'] ?? 0) * 100);

        $order = DB::transaction(function () use ($validated, $quantities, $discountCents): Order {
            $menuItems = MenuItem::query()
                ->whereIn('id', $quantities->keys())
                ->where('is_available', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($menuItems->count() !== $quantities->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected menu items are no longer available.',
                ]);
            }

            $subtotalCents = $menuItems->sum(
                fn (MenuItem $menuItem): int => (int) round((float) $menuItem->price * 100) * $quantities->get($menuItem->id),
            );

            if ($discountCents > $subtotalCents) {
                throw ValidationException::withMessages([
                    'discount' => 'The discount cannot exceed the order subtotal.',
                ]);
            }

            $order = Order::query()->create([
                'order_number' => 'CAF-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'user_id' => Auth::id(),
                'table_number' => $validated['table_number'] ?? null,
                'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                'discount' => number_format($discountCents / 100, 2, '.', ''),
                'total_amount' => number_format(($subtotalCents - $discountCents) / 100, 2, '.', ''),
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
            ]);

            foreach ($menuItems as $menuItem) {
                $quantity = $quantities->get($menuItem->id);

                $order->orderItems()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $menuItem->price,
                    'total_price' => number_format(
                        ((int) round((float) $menuItem->price * 100) * $quantity) / 100,
                        2,
                        '.',
                        '',
                    ),
                ]);
            }

            return $order;
        });

        return redirect()
            ->route('pos.index')
            ->with('success', "Order {$order->order_number} completed successfully.");
    }
}
