<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->index('created_at', 'wallet_transactions_created_at_index');
            $table->index(
                ['wallet_id', 'created_at'],
                'wallet_transactions_wallet_created_at_index'
            );
        });

        Schema::table('merchant_wallet_transactions', function (Blueprint $table) {
            $table->index(
                'created_at',
                'merchant_wallet_transactions_created_at_index'
            );
            $table->index(
                ['merchant_wallet_id', 'created_at'],
                'merchant_wallet_transactions_wallet_created_at_index'
            );
            $table->index(
                'status',
                'merchant_wallet_transactions_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex('wallet_transactions_created_at_index');
            $table->dropIndex('wallet_transactions_wallet_created_at_index');
        });

        Schema::table('merchant_wallet_transactions', function (Blueprint $table) {
            $table->dropIndex('merchant_wallet_transactions_created_at_index');
            $table->dropIndex('merchant_wallet_transactions_wallet_created_at_index');
            $table->dropIndex('merchant_wallet_transactions_status_index');
        });
    }
};
