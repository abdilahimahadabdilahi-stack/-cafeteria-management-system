<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with('user')
            ->withCount('orderItems')
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('orders.form', [
            'order' => new Order,
            'staff' => $this->activeStaff(),
            'menuItems' => MenuItem::query()->where('is_available', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
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

            $subtotalCents = $this->subtotalInCents($menuItems, $quantities);
            $this->ensureDiscountFits($discountCents, $subtotalCents);

            $order = Order::query()->create([
                'order_number' => 'CAF-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'user_id' => $validated['user_id'],
                'table_number' => $validated['table_number'] ?? null,
                'subtotal' => $this->formatCents($subtotalCents),
                'discount' => $this->formatCents($discountCents),
                'total_amount' => $this->formatCents($subtotalCents - $discountCents),
                'payment_method' => $validated['payment_method'],
                'status' => $validated['status'],
            ]);

            foreach ($menuItems as $menuItem) {
                $quantity = $quantities->get($menuItem->id);

                $order->orderItems()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $menuItem->price,
                    'total_price' => $this->formatCents((int) round((float) $menuItem->price * 100) * $quantity),
                ]);
            }

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Order created.');
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'orderItems.menuItem']);

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        return view('orders.form', [
            'order' => $order,
            'staff' => $this->activeStaff(),
            'menuItems' => MenuItem::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        if ($order->status === 'cancelled') {
            return back()->with('error', 'Cancelled orders cannot be edited.');
        }

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('status', true)->whereIn('role', ['admin', 'cashier']),
                ),
            ],
            'table_number' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', Rule::in(['cash', 'zaad', 'edahab'])],
            'discount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
        ]);

        $order->load('orderItems');
        $subtotalCents = $order->orderItems->sum(
            fn (OrderItem $orderItem): int => (int) round((float) $orderItem->total_price * 100),
        );
        $discountCents = (int) round((float) $validated['discount'] * 100);

        if ($discountCents > $subtotalCents) {
            throw ValidationException::withMessages([
                'discount' => 'The discount cannot exceed the order subtotal.',
            ]);
        }

        $order->update([
            'user_id' => $validated['user_id'],
            'table_number' => $validated['table_number'] ?? null,
            'payment_method' => $validated['payment_method'],
            'discount' => $this->formatCents($discountCents),
            'total_amount' => $this->formatCents($subtotalCents - $discountCents),
            'status' => $validated['status'],
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Order updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        if ($order->status !== 'cancelled') {
            $order->update(['status' => 'cancelled']);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order cancelled; its transaction history was preserved.');
    }

    private function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('status', true)->whereIn('role', ['admin', 'cashier']),
                ),
            ],
            'table_number' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', Rule::in(['cash', 'zaad', 'edahab'])],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'distinct', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return EloquentCollection<int, User>
     */
    private function activeStaff(): EloquentCollection
    {
        return User::query()
            ->where('status', true)
            ->whereIn('role', ['admin', 'cashier'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  EloquentCollection<int, MenuItem>  $menuItems
     * @param  SupportCollection<int|string, int>  $quantities
     */
    private function subtotalInCents(EloquentCollection $menuItems, SupportCollection $quantities): int
    {
        return $menuItems->sum(
            fn (MenuItem $menuItem): int => (int) round((float) $menuItem->price * 100) * $quantities->get($menuItem->id),
        );
    }

    private function ensureDiscountFits(int $discountCents, int $subtotalCents): void
    {
        if ($discountCents > $subtotalCents) {
            throw ValidationException::withMessages([
                'discount' => 'The discount cannot exceed the order subtotal.',
            ]);
        }
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
