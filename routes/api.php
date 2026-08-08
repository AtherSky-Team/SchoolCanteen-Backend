<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'SakuSekolah API is running',
        'version' => 'v1',
    ]);
});