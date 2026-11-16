<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'create']);

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');

    // Route-yada Diiwaangelinta
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin,cashier')->group(function (): void {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/orders', [PosController::class, 'store'])->name('pos.store');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::resource('categories', CategoryController::class)
            ->except(['show']);
        Route::resource('menu', MenuController::class)
            ->except(['show'])
            ->parameters(['menu' => 'menuItem']);
        Route::resource('inventory', InventoryController::class)
            ->except(['show'])
            ->parameters(['inventory' => 'inventoryItem']);
        Route::resource('orders', OrderController::class);
        Route::resource('orders.items', OrderItemController::class)
            ->only(['create', 'store', 'edit', 'update', 'destroy'])
            ->parameters(['orders' => 'order', 'items' => 'orderItem'])
            ->scoped(['order' => 'id', 'orderItem' => 'id']);
        Route::resource('expenses', ExpenseController::class)
            ->except(['show']);
        Route::resource('shifts', ShiftController::class)
            ->except(['show']);
        Route::resource('staff', UserController::class)
            ->except(['show'])
            ->parameters(['staff' => 'staffMember']);
    });
});