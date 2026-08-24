<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(
                ['status', 'created_at'],
                'orders_status_created_at_idx'
            );
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->index(
                ['provider', 'status'],
                'payment_provider_status_idx'
            );
        });

        Schema::table('escrow_transactions', function (Blueprint $table) {
            $table->index(
                ['status', 'created_at'],
                'escrow_status_created_at_idx'
            );
        });

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->index(
                ['status', 'created_at'],
                'withdrawal_status_created_at_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_at_idx');
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex('payment_provider_status_idx');
        });

        Schema::table('escrow_transactions', function (Blueprint $table) {
            $table->dropIndex('escrow_status_created_at_idx');
        });

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropIndex('withdrawal_status_created_at_idx');
        });
    }
};
