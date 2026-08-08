<?php

use App\Http\Controllers\Api\V1\MerchantController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Student\WalletController;


/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'SchoolCanteen API is running',
        'version' => 'v1',
    ]);
});


/*
|--------------------------------------------------------------------------
| API Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    Route::middleware('supabase.auth')->group(function () {

        Route::get('/me', function (\Illuminate\Http\Request $request) {

            $profile = $request->attributes->get('profile');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'phone' => $profile->phone,
                    'avatar_url' => $profile->avatar_url,
                    'role' => $profile->role,
                ],
            ]);
        });

        Route::prefix('student')
            ->middleware('role:student')
            ->group(function () {

                Route::get('/wallet', [
                    WalletController::class,
                    'show',
                ]);

                Route::get('/wallet/transactions', [
                    WalletController::class,
                    'transactions',
                ]);

    });

    });

    /*
    |--------------------------------------------------------------------------
    | Public Catalog
    |--------------------------------------------------------------------------
    */

    Route::get('/products', [
        ProductController::class,
        'index',
    ]);

    Route::get('/products/{product}', [
        ProductController::class,
        'show',
    ]);

    Route::get('/merchants', [
        MerchantController::class,
        'index',
    ]);

    Route::get('/merchants/{merchant}', [
        MerchantController::class,
        'show',
    ]);
});