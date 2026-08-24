<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'wallet_transactions',
            function (Blueprint $table) {
                $table->index(
                    [
                        'wallet_id',
                        'created_at'
                    ],
                    'wallet_transaction_history_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'wallet_transactions',
            function (Blueprint $table) {
                $table->dropIndex(
                    'wallet_transaction_history_idx'
                );
            }
        );
    }
};
