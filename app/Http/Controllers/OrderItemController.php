<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderItemController extends Controller
{
    public function create(Order $order): View
    {
        $this->ensureEditable($order);

        return view('orders.items.form', [
            'order' => $order,
            'orderItem' => new OrderItem,
            'menuItems' => MenuItem::query()->where('is_available', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->ensureEditable($order);
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($validated, $order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $this->ensureEditable($lockedOrder);

            $menuItem = MenuItem::query()
                ->where('is_available', true)
                ->lockForUpdate()
                ->findOrFail($validated['menu_item_id']);

            $lineCents = (int) round((float) $menuItem->price * 100) * (int) $validated['quantity'];
            $subtotalCents = $this->subtotalAfterChange($lockedOrder, null, $lineCents);
            $this->ensureDiscountFits($lockedOrder, $subtotalCents);

            $lockedOrder->orderItems()->create([
                'menu_item_id' => $menuItem->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $menuItem->price,
                'total_price' => $this->formatCents($lineCents),
            ]);

            $this->updateOrderTotals($lockedOrder, $subtotalCents);
        });

        return redirect()->route('orders.show', $order)->with('success', 'Order item added.');
    }

    public function edit(Order $order, OrderItem $orderItem): View
    {
        $this->ensureEditable($order);

        return view('orders.items.form', [
            'order' => $order,
            'orderItem' => $orderItem,
            'menuItems' => MenuItem::query()
                ->where('is_available', true)
                ->orWhereKey($orderItem->menu_item_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Order $order, OrderItem $orderItem): RedirectResponse
    {
        $this->ensureEditable($order);
        $validated = $request->validate($this->rules($orderItem));

        DB::transaction(function () use ($validated, $order, $orderItem): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $this->ensureEditable($lockedOrder);
            $lockedItem = $lockedOrder->orderItems()->lockForUpdate()->findOrFail($orderItem->id);

            $menuItem = MenuItem::query()
                ->where(function ($query) use ($validated): void {
                    $query->where('is_available', true)
                        ->orWhere('id', $validated['menu_item_id']);
                })
                ->lockForUpdate()
                ->findOrFail($validated['menu_item_id']);

            $lineCents = (int) round((float) $menuItem->price * 100) * (int) $validated['quantity'];
            $subtotalCents = $this->subtotalAfterChange($lockedOrder, $lockedItem, $lineCents);
            $this->ensureDiscountFits($lockedOrder, $subtotalCents);

            $lockedItem->update([
                'menu_item_id' => $menuItem->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $menuItem->price,
                'total_price' => $this->formatCents($lineCents),
            ]);

            $this->updateOrderTotals($lockedOrder, $subtotalCents);
        });

        return redirect()->route('orders.show', $order)->with('success', 'Order item updated.');
    }

    public function destroy(Order $order, OrderItem $orderItem): RedirectResponse
    {
        $this->ensureEditable($order);

        if ($order->orderItems()->count() <= 1) {
            return back()->with('error', 'An order must retain at least one line item.');
        }

        DB::transaction(function () use ($order, $orderItem): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $this->ensureEditable($lockedOrder);
            $lockedItem = $lockedOrder->orderItems()->lockForUpdate()->findOrFail($orderItem->id);
            $subtotalCents = $this->subtotalAfterChange($lockedOrder, $lockedItem, 0);
            $this->ensureDiscountFits($lockedOrder, $subtotalCents);

            $lockedItem->delete();
            $this->updateOrderTotals($lockedOrder, $subtotalCents);
        });

        return redirect()->route('orders.show', $order)->with('success', 'Order item removed.');
    }

    private function ensureEditable(Order $order): void
    {
        abort_unless($order->status === 'pending', 403, 'Only pending orders can have their items changed.');
    }

    private function rules(?OrderItem $orderItem = null): array
    {
        return [
            'menu_item_id' => [
                'required',
                'integer',
                Rule::exists('menu_items', 'id')->where(function ($query) use ($orderItem): void {
                    $query->where('is_available', true);

                    if ($orderItem) {
                        $query->orWhere('id', $orderItem->menu_item_id);
                    }
                }),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    private function subtotalAfterChange(Order $order, ?OrderItem $replacedItem, int $newLineCents): int
    {
        $subtotalCents = $order->orderItems()
            ->get()
            ->sum(fn (OrderItem $orderItem): int => (int) round((float) $orderItem->total_price * 100));

        if ($replacedItem) {
            $subtotalCents -= (int) round((float) $replacedItem->total_price * 100);
        }

        return $subtotalCents + $newLineCents;
    }

    private function ensureDiscountFits(Order $order, int $subtotalCents): void
    {
        if ((int) round((float) $order->discount * 100) > $subtotalCents) {
            throw ValidationException::withMessages([
                'discount' => 'Reduce the order discount before lowering its subtotal.',
            ]);
        }
    }

    private function updateOrderTotals(Order $order, int $subtotalCents): void
    {
        $discountCents = (int) round((float) $order->discount * 100);
        $order->update([
            'subtotal' => $this->formatCents($subtotalCents),
            'total_amount' => $this->formatCents($subtotalCents - $discountCents),
        ]);
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
