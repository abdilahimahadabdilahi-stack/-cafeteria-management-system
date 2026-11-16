<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::user()->role === 'cashier') {
            return redirect()->route('pos.index');
        }

        if (Auth::user()->role === 'kitchen') {
            $orders = Order::query()
                ->with(['user', 'orderItems.menuItem'])
                ->where('status', 'pending')
                ->latest()
                ->get();

            return view('kitchen.dashboard', compact('orders'));
        }

        $todayOrders = Order::query()
            ->whereDate('created_at', today())
            ->where('status', 'completed');
        $dailySales = (clone $todayOrders)->sum('total_amount');
        $dailyOrderCount = (clone $todayOrders)->count();
        $activeTables = Order::query()
            ->where('status', 'pending')
            ->whereNotNull('table_number')
            ->distinct()
            ->count('table_number');

        $popularItems = OrderItem::query()
            ->select('menu_item_id')
            ->selectRaw('SUM(quantity) as sold_quantity')
            ->whereHas('order', function ($query): void {
                $query->whereDate('created_at', today())
                    ->where('status', 'completed');
            })
            ->with('menuItem')
            ->groupBy('menu_item_id')
            ->orderByDesc('sold_quantity')
            ->limit(5)
            ->get();

        $recentOrders = Order::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();
        $quickMenuItems = MenuItem::query()
            ->with('category')
            ->where('is_available', true)
            ->orderBy('name')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'dailySales',
            'dailyOrderCount',
            'activeTables',
            'popularItems',
            'recentOrders',
            'quickMenuItems',
        ));
    }
}
