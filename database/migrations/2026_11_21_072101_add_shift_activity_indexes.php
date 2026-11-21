<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status', 'created_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropIndex(['start_time', 'end_time']);
        });
    }
};
