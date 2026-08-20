<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateOwner = DB::table('merchants')
            ->select('owner_user_id')
            ->groupBy('owner_user_id')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicateOwner) {
            throw new \RuntimeException(
                'Tidak dapat menambahkan unique constraint merchants.owner_user_id: '
                .'terdapat owner yang terhubung ke lebih dari satu merchant.'
            );
        }

        Schema::table('merchants', function (Blueprint $table) {
            $table->unique(
                'owner_user_id',
                'merchants_owner_user_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropUnique('merchants_owner_user_id_unique');
        });
    }
};
