<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminTransactionPerformanceTest extends TestCase
{
    private const ADMIN =
        '11111111-1111-4111-8111-111111111111';

    private const STUDENT =
        '22222222-2222-4222-8222-222222222222';

    private const MERCHANT_OWNER =
        '33333333-3333-4333-8333-333333333333';

    private const MERCHANT =
        '44444444-4444-4444-8444-444444444444';

    private const WALLET =
        '55555555-5555-4555-8555-555555555555';

    private const ORDER =
        '66666666-6666-4666-8666-666666666666';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.supabase.url' =>
                'https://supabase.test',
            'services.supabase.publishable_key' =>
                'test-publishable-key',
        ]);

        Cache::flush();
        Http::preventStrayRequests();

        $this->createSchema();
        $this->seedBaseData();
    }

    public function test_student_transaction_page_returns_pagination_meta_and_merchant(): void
    {
        DB::table('wallet_transactions')->insert([
            'id' =>
                '77777777-7777-4777-8777-777777777777',
            'wallet_id' => self::WALLET,
            'type' => 'payment',
            'direction' => 'debit',
            'amount' => 25000,
            'status' => 'completed',
            'reference_type' => 'order',
            'reference_id' => self::ORDER,
            'description' => 'Pembayaran pesanan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->withHeaders($this->adminHeaders())
            ->getJson('/api/v1/admin/transactions/student?page=1');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.merchant.id', self::MERCHANT)
            ->assertJsonPath('data.0.merchant.name', 'Kantin Test');
    }

    public function test_transaction_stats_are_calculated_in_database(): void
    {
        DB::table('wallet_transactions')->insert([
            [
                'id' =>
                    '77777777-7777-4777-8777-777777777771',
                'wallet_id' => self::WALLET,
                'type' => 'payment',
                'direction' => 'debit',
                'amount' => 25000,
                'status' => 'completed',
                'reference_type' => 'order',
                'reference_id' => self::ORDER,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' =>
                    '77777777-7777-4777-8777-777777777772',
                'wallet_id' => self::WALLET,
                'type' => 'topup',
                'direction' => 'credit',
                'amount' => 50000,
                'status' => 'pending',
                'reference_type' => null,
                'reference_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' =>
                    '77777777-7777-4777-8777-777777777773',
                'wallet_id' => self::WALLET,
                'type' => 'adjustment',
                'direction' => 'credit',
                'amount' => 10000,
                'status' => 'completed',
                'reference_type' => null,
                'reference_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this
            ->withHeaders($this->adminHeaders())
            ->getJson('/api/v1/admin/transactions/stats');

        $response
            ->assertOk()
            ->assertJsonPath('data.total_transactions', 3)
            ->assertJsonPath('data.completed_transactions', 2)
            ->assertJsonPath('data.pending_transactions', 1)
            ->assertJsonPath('data.transaction_value', 75000);
    }

    private function adminHeaders(): array
    {
        Http::fake([
            'https://supabase.test/auth/v1/user' =>
                Http::response([
                    'id' => self::ADMIN,
                ], 200),
        ]);

        return [
            'Authorization' =>
                'Bearer admin-transaction-test-token',
            'Accept' => 'application/json',
        ];
    }

    private function createSchema(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('role');
            $table->timestamps();
        });

        Schema::create('merchants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_user_id');
            $table->string('name');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->unsignedBigInteger('balance')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('merchant_id');
            $table->uuid('pickup_slot_id')->nullable();
            $table->string('order_code');
            $table->string('status');
            $table->unsignedBigInteger('total_amount');
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->string('type');
            $table->string('direction');
            $table->unsignedBigInteger('amount');
            $table->string('status');
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_transaction_id');
            $table->string('provider')->nullable();
            $table->string('provider_order_id')->nullable();
            $table->string('provider_transaction_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('gross_amount')->default(0);
            $table->text('provider_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    private function seedBaseData(): void
    {
        DB::table('profiles')->insert([
            [
                'id' => self::ADMIN,
                'name' => 'Admin Test',
                'role' => 'admin',
            ],
            [
                'id' => self::STUDENT,
                'name' => 'Student Test',
                'role' => 'student',
            ],
            [
                'id' => self::MERCHANT_OWNER,
                'name' => 'Merchant Owner',
                'role' => 'merchant',
            ],
        ]);

        DB::table('merchants')->insert([
            'id' => self::MERCHANT,
            'owner_user_id' => self::MERCHANT_OWNER,
            'name' => 'Kantin Test',
            'type' => 'canteen',
        ]);

        DB::table('wallets')->insert([
            'id' => self::WALLET,
            'user_id' => self::STUDENT,
            'balance' => 100000,
            'is_active' => true,
        ]);

        DB::table('orders')->insert([
            'id' => self::ORDER,
            'user_id' => self::STUDENT,
            'merchant_id' => self::MERCHANT,
            'order_code' => 'ORD-TEST-001',
            'status' => 'completed',
            'total_amount' => 25000,
        ]);
    }
}
