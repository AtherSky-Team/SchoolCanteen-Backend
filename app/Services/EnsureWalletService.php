<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnsureWalletService
{
    public function ensure(Profile $profile): ?Wallet
    {
        if ($profile->role !== 'student') {
            return null;
        }

        return DB::transaction(function () use ($profile) {

            $wallet = Wallet::firstOrCreate(
                [
                    'user_id' => $profile->id,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'balance' => 100000,
                    'is_active' => true,
                ]
            );

            $hasInitialTransaction = WalletTransaction::query()
                ->where('wallet_id', $wallet->id)
                ->where('type', 'adjustment')
                ->where('direction', 'credit')
                ->exists();

            if (! $hasInitialTransaction) {
                $wallet->update([
                    'balance' => 100000,
                    'is_active' => true,
                ]);

                WalletTransaction::create([
                    'id' => (string) Str::uuid(),
                    'wallet_id' => $wallet->id,
                    'type' => 'adjustment',
                    'direction' => 'credit',
                    'amount' => 100000,
                    'status' => 'completed',
                    'reference_type' => null,
                    'reference_id' => null,
                    'description' => 'Saldo awal akun student',
                ]);
            }

            return $wallet;
        });
    }
}
