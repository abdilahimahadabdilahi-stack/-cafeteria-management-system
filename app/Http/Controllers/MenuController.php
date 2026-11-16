<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $menuItems = MenuItem::query()
            ->with('category')
            ->latest()
            ->paginate(12);

        return view('menu.index', compact('menuItems'));
    }

    public function create(): View
    {
        return view('menu.form', [
            'menuItem' => new MenuItem,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        MenuItem::query()->create($this->validatedAttributes($request));

        return redirect()->route('menu.index')->with('success', 'Menu item created.');
    }

    public function edit(MenuItem $menuItem): View
    {
        return view('menu.form', [
            'menuItem' => $menuItem,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update($this->validatedAttributes($request));

        return redirect()->route('menu.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->orderItems()->exists()) {
            return back()->with('error', 'This menu item is attached to order history and cannot be deleted.');
        }

        $menuItem->delete();

        return redirect()->route('menu.index')->with('success', 'Menu item deleted.');
    }

    private function validatedAttributes(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'image' => ['nullable', 'string', 'max:255'],
            'is_available' => ['required', 'boolean'],
        ]);
    }
}
