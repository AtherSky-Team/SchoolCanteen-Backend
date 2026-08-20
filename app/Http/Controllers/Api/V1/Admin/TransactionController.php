<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminMerchantTransactionResource;
use App\Http\Resources\AdminStudentTransactionResource;
use App\Models\MerchantWalletTransaction;
use App\Models\WalletTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function studentIndex(Request $request)
    {
        $query = $this->studentQuery($request);

        $transactions = $query
            ->with([
                'wallet.user:id,name',
                'paymentTransaction',
                'referencedOrder.merchant:id,name,type',
            ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return AdminStudentTransactionResource::collection(
            $transactions
        )->additional([
            'success' => true,
        ]);
    }

    public function stats()
    {
        $stats = WalletTransaction::query()
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw(
                "SUM(CASE WHEN LOWER(status) = 'completed' THEN 1 ELSE 0 END) as completed_transactions"
            )
            ->selectRaw(
                "SUM(CASE WHEN LOWER(status) = 'pending' THEN 1 ELSE 0 END) as pending_transactions"
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN LOWER(type) IN ('payment', 'topup', 'top_up') THEN amount ELSE 0 END), 0) as transaction_value"
            )
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_transactions' =>
                    (int) ($stats?->total_transactions ?? 0),
                'completed_transactions' =>
                    (int) ($stats?->completed_transactions ?? 0),
                'pending_transactions' =>
                    (int) ($stats?->pending_transactions ?? 0),
                'transaction_value' =>
                    (int) ($stats?->transaction_value ?? 0),
            ],
        ]);
    }

    public function studentShow(string $transaction)
    {
        $walletTransaction =
            WalletTransaction::query()
                ->with([
                    'wallet.user:id,name',
                    'paymentTransaction',
                    'referencedOrder.merchant:id,name,type',
                ])
                ->find($transaction);

        if (! $walletTransaction) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' =>
                        'STUDENT_TRANSACTION_NOT_FOUND',
                    'message' =>
                        'Transaksi student tidak ditemukan.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' =>
                new AdminStudentTransactionResource(
                    $walletTransaction
                ),
        ]);
    }

    public function merchantIndex(Request $request)
    {
        $query =
            MerchantWalletTransaction::query()
                ->with([
                    'merchantWallet.merchant:id,name,type',
                ]);

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->query('search')
            );

            $query->whereHas(
                'merchantWallet.merchant',
                function ($merchantQuery) use ($search) {
                    $merchantQuery->where(
                        'name',
                        'ilike',
                        '%' . $search . '%'
                    );
                }
            );
        }

        if ($request->filled('merchant_id')) {
            $merchantId =
                $request->query('merchant_id');

            $query->whereHas(
                'merchantWallet',
                function ($walletQuery) use ($merchantId) {
                    $walletQuery->where(
                        'merchant_id',
                        $merchantId
                    );
                }
            );
        }

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->query('type')
            );
        }

        if ($request->filled('direction')) {
            $query->where(
                'direction',
                $request->query('direction')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->query('status')
            );
        }

        $this->applyDateRange($query, $request);

        $transactions = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return AdminMerchantTransactionResource::collection(
            $transactions
        )->additional([
            'success' => true,
        ]);
    }

    public function merchantShow(string $transaction)
    {
        $merchantTransaction =
            MerchantWalletTransaction::query()
                ->with([
                    'merchantWallet.merchant:id,name,type',
                ])
                ->find($transaction);

        if (! $merchantTransaction) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' =>
                        'MERCHANT_TRANSACTION_NOT_FOUND',
                    'message' =>
                        'Transaksi merchant tidak ditemukan.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' =>
                new AdminMerchantTransactionResource(
                    $merchantTransaction
                ),
        ]);
    }

    private function studentQuery(Request $request): Builder
    {
        $query = WalletTransaction::query();

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->query('search')
            );

            $query->whereHas(
                'wallet.user',
                function ($userQuery) use ($search) {
                    $userQuery->where(
                        'name',
                        'ilike',
                        '%' . $search . '%'
                    );
                }
            );
        }

        if ($request->filled('student_id')) {
            $studentId =
                $request->query('student_id');

            $query->whereHas(
                'wallet',
                function ($walletQuery) use ($studentId) {
                    $walletQuery->where(
                        'user_id',
                        $studentId
                    );
                }
            );
        }

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->query('type')
            );
        }

        if ($request->filled('direction')) {
            $query->where(
                'direction',
                $request->query('direction')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->query('status')
            );
        }

        $this->applyDateRange($query, $request);

        return $query;
    }

    private function applyDateRange(
        Builder $query,
        Request $request
    ): void {
        if ($request->filled('date_from')) {
            $dateFrom = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                (string) $request->query('date_from'),
                'Asia/Jakarta'
            );

            if ($dateFrom !== false) {
                $query->where(
                    'created_at',
                    '>=',
                    $dateFrom->utc()
                );
            }
        }

        if ($request->filled('date_to')) {
            $dateTo = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                (string) $request->query('date_to'),
                'Asia/Jakarta'
            );

            if ($dateTo !== false) {
                $query->where(
                    'created_at',
                    '<',
                    $dateTo->addDay()->utc()
                );
            }
        }
    }
}
