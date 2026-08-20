<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupabaseAuthPerformanceTest extends TestCase
{
    private const USER_ID =
        '11111111-1111-4111-8111-111111111111';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.supabase.url' =>
                'https://supabase.test',
            'services.supabase.publishable_key' =>
                'test-publishable-key',
            'services.supabase.auth_cache_seconds' =>
                30,
            'services.supabase.auth_connect_timeout' =>
                1,
            'services.supabase.auth_timeout' =>
                2,
        ]);

        Cache::flush();
        Http::preventStrayRequests();

        Schema::dropIfExists('profiles');

        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('role');
            $table->timestamps();
        });

        DB::table('profiles')->insert([
            'id' => self::USER_ID,
            'name' => 'Student Test',
            'role' => 'student',
        ]);
    }

    public function test_repeated_requests_with_same_token_reuse_verified_user_cache(): void
    {
        Http::fake([
            'https://supabase.test/auth/v1/user' =>
                Http::response([
                    'id' => self::USER_ID,
                ], 200),
        ]);

        $headers = [
            'Authorization' =>
                'Bearer same-access-token',
            'Accept' => 'application/json',
        ];

        $this->withHeaders($headers)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', self::USER_ID);

        $this->withHeaders($headers)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', self::USER_ID);

        Http::assertSentCount(1);
    }

    public function test_invalid_token_is_not_cached_as_valid(): void
    {
        Http::fake([
            'https://supabase.test/auth/v1/user' =>
                Http::response([
                    'message' => 'invalid token',
                ], 401),
        ]);

        $this->withHeaders([
            'Authorization' =>
                'Bearer invalid-access-token',
            'Accept' => 'application/json',
        ])
            ->getJson('/api/v1/me')
            ->assertStatus(401)
            ->assertJsonPath(
                'error.code',
                'INVALID_TOKEN'
            );
    }

    public function test_supabase_server_failure_returns_service_unavailable(): void
    {
        Http::fake([
            'https://supabase.test/auth/v1/user' =>
                Http::response([], 503),
        ]);

        $this->withHeaders([
            'Authorization' =>
                'Bearer temporary-failure-token',
            'Accept' => 'application/json',
        ])
            ->getJson('/api/v1/me')
            ->assertStatus(503)
            ->assertJsonPath(
                'error.code',
                'AUTH_SERVICE_UNAVAILABLE'
            );
    }
}
