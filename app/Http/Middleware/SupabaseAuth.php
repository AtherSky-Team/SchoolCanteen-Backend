<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Illuminate\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class SupabaseAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->error(
                'UNAUTHENTICATED',
                'Access token tidak ditemukan.',
                401
            );
        }

        $supabaseUrl = config('services.supabase.url');
        $publishableKey = config('services.supabase.publishable_key');

        if (! $supabaseUrl || ! $publishableKey) {
            return $this->error(
                'AUTH_CONFIGURATION_ERROR',
                'Konfigurasi Supabase Auth belum tersedia.',
                500
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Short-lived verification cache
        |--------------------------------------------------------------------------
        |
        | The old middleware called Supabase /auth/v1/user for every protected API
        | request. A page that loaded several endpoints therefore repeated the same
        | network round-trip several times for one access token.
        |
        | Cache only a verified user response and key it with a SHA-256 digest of
        | the token, never the raw bearer token. The TTL is deliberately short so
        | this remains a safe transition step before full local JWKS verification.
        |
        */
        $tokenHash = hash('sha256', $token);
        $cacheKey = "supabase:verified-user:{$tokenHash}";

        $supabaseUser = Cache::get($cacheKey);

        if (! is_array($supabaseUser)) {
            try {
                $supabaseUser = Cache::lock(
                    "supabase:verify-lock:{$tokenHash}",
                    8
                )->block(3, function () use (
                    $cacheKey,
                    $supabaseUrl,
                    $publishableKey,
                    $token
                ) {
                    $cached = Cache::get($cacheKey);

                    if (is_array($cached)) {
                        return $cached;
                    }

                    $verifiedUser = $this->fetchVerifiedUser(
                        $supabaseUrl,
                        $publishableKey,
                        $token
                    );

                    if ($verifiedUser === null) {
                        return null;
                    }

                    Cache::put(
                        $cacheKey,
                        $verifiedUser,
                        max(
                            1,
                            (int) config(
                                'services.supabase.auth_cache_seconds',
                                30
                            )
                        )
                    );

                    return $verifiedUser;
                });
            } catch (LockTimeoutException) {
                // Do not fail a valid request only because another process held
                // the coalescing lock longer than expected.
                try {
                    $supabaseUser = $this->fetchVerifiedUser(
                        $supabaseUrl,
                        $publishableKey,
                        $token
                    );
                } catch (ConnectionException|RuntimeException) {
                    return $this->authServiceUnavailable();
                }
            } catch (ConnectionException|RuntimeException) {
                return $this->authServiceUnavailable();
            }
        }

        if (! is_array($supabaseUser) || empty($supabaseUser['id'])) {
            return $this->error(
                'INVALID_TOKEN',
                'Access token tidak valid atau sudah kedaluwarsa.',
                401
            );
        }

        $profileCacheKey =
            "profile:{$supabaseUser['id']}";

        $profile = Cache::remember(
            $profileCacheKey,
            60,
            function () use ($supabaseUser) {
                return Profile::query()
                    ->find($supabaseUser['id']);
            }
        );

        if (! $profile) {
            return $this->error(
                'PROFILE_NOT_FOUND',
                'Profile pengguna tidak ditemukan.',
                404
            );
        }

        $request->attributes->set('supabase_user', $supabaseUser);
        $request->attributes->set('profile', $profile);

        return $next($request);
    }

    /**
     * @throws ConnectionException
     */
    private function fetchVerifiedUser(
        string $supabaseUrl,
        string $publishableKey,
        string $token
    ): ?array {
        $response = Http::connectTimeout(
            max(
                1,
                (int) config(
                    'services.supabase.auth_connect_timeout',
                    2
                )
            )
        )
            ->timeout(
                max(
                    1,
                    (int) config(
                        'services.supabase.auth_timeout',
                        5
                    )
                )
            )
            ->withHeaders([
                'apikey' => $publishableKey,
                'Authorization' => 'Bearer '.$token,
            ])
            ->get(
                rtrim($supabaseUrl, '/').'/auth/v1/user'
            );

        if ($response->serverError() || $response->status() === 429) {
            throw new RuntimeException(
                'Supabase Auth upstream is temporarily unavailable.'
            );
        }

        if (! $response->successful()) {
            return null;
        }

        $user = $response->json();

        return is_array($user) ? $user : null;
    }

    private function authServiceUnavailable(): Response
    {
        return $this->error(
            'AUTH_SERVICE_UNAVAILABLE',
            'Layanan autentikasi sedang tidak dapat dijangkau. Silakan coba lagi.',
            503
        );
    }

    private function error(
        string $code,
        string $message,
        int $status
    ): Response {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }
}
