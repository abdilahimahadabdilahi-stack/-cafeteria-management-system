<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $inventoryItems = Inventory::query()->orderBy('item_name')->paginate(15);

        return view('inventory.index', compact('inventoryItems'));
    }

    public function create(): View
    {
        return view('inventory.form', ['inventoryItem' => new Inventory]);
    }

    public function store(Request $request): RedirectResponse
    {
        Inventory::query()->create($this->validatedAttributes($request));

        return redirect()->route('inventory.index')->with('success', 'Inventory item created.');
    }

    public function edit(Inventory $inventoryItem): View
    {
        return view('inventory.form', compact('inventoryItem'));
    }

    public function update(Request $request, Inventory $inventoryItem): RedirectResponse
    {
        $inventoryItem->update($this->validatedAttributes($request));

        return redirect()->route('inventory.index')->with('success', 'Inventory item updated.');
    }

    public function destroy(Inventory $inventoryItem): RedirectResponse
    {
        $inventoryItem->delete();

        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted.');
    }

    private function validatedAttributes(Request $request): array
    {
        return $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:kg,liter,pcs'],
            'quantity' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'reorder_level' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);
    }
}
