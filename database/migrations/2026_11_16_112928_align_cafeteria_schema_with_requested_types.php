<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->decimal('price', 8, 2)->change();
            $table->decimal('cost_price', 8, 2)->nullable()->change();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 8, 2)->change();
            $table->decimal('discount', 8, 2)->default(0)->change();
            $table->decimal('total_amount', 8, 2)->change();
            $table->enum('payment_method_normalized', ['cash', 'zaad', 'edahab'])->nullable();
        });

        DB::table('orders')
            ->select(['id', 'payment_method'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $orders): void {
                foreach ($orders as $order) {
                    $paymentMethod = strtolower((string) $order->payment_method);

                    if (! in_array($paymentMethod, ['cash', 'zaad', 'edahab'], true)) {
                        throw new RuntimeException("Unsupported payment method [{$order->payment_method}] for order [{$order->id}].");
                    }

                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['payment_method_normalized' => $paymentMethod]);
                }
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->renameColumn('payment_method_normalized', 'payment_method');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'zaad', 'edahab'])->nullable(false)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 8, 2)->change();
            $table->decimal('total_price', 8, 2)->change();
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->string('unit')->change();
            $table->decimal('quantity', 8, 2)->change();
            $table->decimal('reorder_level', 8, 2)->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('opening_cash', 8, 2)->change();
            $table->decimal('closing_cash', 8, 2)->nullable()->change();
            $table->decimal('expected_cash', 8, 2)->nullable()->change();
        });

        if (Schema::getColumnType('users', 'status') !== 'boolean') {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('status_enabled')->nullable();
                $table->dropIndex(['role', 'status']);
            });

            DB::table('users')
                ->select(['id', 'status'])
                ->orderBy('id')
                ->chunkById(500, function (Collection $users): void {
                    foreach ($users as $user) {
                        $status = match ($user->status) {
                            'active', 1, '1' => true,
                            'inactive', 0, '0' => false,
                            default => throw new RuntimeException("Unsupported user status [{$user->status}] for user [{$user->id}]."),
                        };

                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['status_enabled' => $status]);
                    }
                });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
                $table->renameColumn('status_enabled', 'status');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->boolean('status')->default(true)->nullable(false)->change();
                $table->index(['role', 'status']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'cashier', 'kitchen'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('menu_items')->whereNull('cost_price')->exists()) {
            throw new RuntimeException('Cannot roll back while menu items have a null cost price.');
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method_legacy', ['Cash', 'Zaad', 'eDahab'])->nullable();
        });

        DB::table('orders')->update([
            'payment_method_legacy' => DB::raw("CASE payment_method WHEN 'cash' THEN 'Cash' WHEN 'zaad' THEN 'Zaad' WHEN 'edahab' THEN 'eDahab' END"),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->renameColumn('payment_method_legacy', 'payment_method');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['Cash', 'Zaad', 'eDahab'])->nullable(false)->change();
            $table->decimal('subtotal', 10, 2)->change();
            $table->decimal('discount', 10, 2)->default(0)->change();
            $table->decimal('total_amount', 10, 2)->change();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->decimal('price', 10, 2)->change();
            $table->decimal('cost_price', 10, 2)->nullable(false)->change();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->change();
            $table->decimal('total_price', 10, 2)->change();
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->enum('unit', ['kg', 'liter', 'pcs'])->change();
            $table->decimal('quantity', 10, 3)->default(0)->change();
            $table->decimal('reorder_level', 10, 3)->default(0)->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('opening_cash', 10, 2)->change();
            $table->decimal('closing_cash', 10, 2)->nullable()->change();
            $table->decimal('expected_cash', 10, 2)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'cashier', 'kitchen'])->default('cashier')->change();
            $table->enum('status_legacy', ['active', 'inactive'])->nullable();
        });

        DB::table('users')->update([
            'status_legacy' => DB::raw("CASE WHEN status = 1 THEN 'active' ELSE 'inactive' END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropColumn('status');
            $table->renameColumn('status_legacy', 'status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->nullable(false)->change();
            $table->index(['role', 'status']);
        });
    }
};
