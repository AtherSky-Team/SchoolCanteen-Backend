<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(
                [
                    'merchant_id',
                    'is_active',
                    'created_at'
                ],
                'products_public_catalog_idx'
            );
        });

        Schema::table('product_modifier_groups', function (Blueprint $table) {
            $table->index(
                [
                    'product_id',
                    'is_active'
                ],
                'modifier_groups_product_active_idx'
            );
        });

        Schema::table('product_modifier_options', function (Blueprint $table) {
            $table->index(
                [
                    'modifier_group_id',
                    'is_active'
                ],
                'modifier_options_group_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_public_catalog_idx');
        });

        Schema::table('product_modifier_groups', function (Blueprint $table) {
            $table->dropIndex('modifier_groups_product_active_idx');
        });

        Schema::table('product_modifier_options', function (Blueprint $table) {
            $table->dropIndex('modifier_options_group_active_idx');
        });
    }
};
