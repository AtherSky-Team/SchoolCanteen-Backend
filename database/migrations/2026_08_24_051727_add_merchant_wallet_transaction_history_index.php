<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'merchant_wallet_transactions',
            function (Blueprint $table) {

                $table->index(
                    [
                        'merchant_wallet_id',
                        'created_at'
                    ],
                    'merchant_wallet_transaction_history_idx'
                );

            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'merchant_wallet_transactions',
            function (Blueprint $table) {

                $table->dropIndex(
                    'merchant_wallet_transaction_history_idx'
                );

            }
        );
    }
};
