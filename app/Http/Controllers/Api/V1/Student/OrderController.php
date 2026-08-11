<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Student\StoreOrderRequest;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Checkout request valid.',
            'data' => $request->validated(),
        ]);
    }
}