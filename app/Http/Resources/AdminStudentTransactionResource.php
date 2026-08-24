<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminStudentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'ledger' => 'student_wallet',

            'student' => [
                'id' =>
                    $this->wallet?->user?->id,

                'name' =>
                    $this->wallet?->user?->name,

                'phone' =>
                    $this->wallet?->user?->phone,

                'avatar_url' =>
                    $this->wallet?->user?->avatar_url,

                'nis' =>
                    $this->wallet?->user?->studentProfile?->nis,

                'class' =>
                    $this->wallet?->user?->studentProfile?->class,

                'major' =>
                    $this->wallet?->user?->studentProfile?->major,
            ],

            'merchant' => $this->whenLoaded(
                'referencedOrder',
                function () {
                    if (
                        strtolower((string) $this->reference_type) !== 'order' ||
                        ! $this->referencedOrder?->merchant
                    ) {
                        return null;
                    }

                    return [
                        'id' => $this->referencedOrder->merchant->id,
                        'name' => $this->referencedOrder->merchant->name,
                        'type' => $this->referencedOrder->merchant->type,
                    ];
                }
            ),

            'type' => $this->type,
            'direction' => $this->direction,

            'amount' => (int) $this->amount,
            'status' => $this->status,

            'reference' => [
                'type' => $this->reference_type,
                'id' => $this->reference_id,
            ],

            'description' => $this->description,

            'payment' => $this->whenLoaded(
                'paymentTransaction',
                function () {
                    if (!$this->paymentTransaction) {
                        return null;
                    }

                    return [
                        'id' =>
                            $this->paymentTransaction->id,

                        'provider' =>
                            $this->paymentTransaction->provider,

                        'provider_order_id' =>
                            $this->paymentTransaction
                                ->provider_order_id,

                        'provider_transaction_id' =>
                            $this->paymentTransaction
                                ->provider_transaction_id,

                        'payment_type' =>
                            $this->paymentTransaction
                                ->payment_type,

                        'status' =>
                            $this->paymentTransaction->status,

                        'gross_amount' =>
                            (int) $this->paymentTransaction
                                ->gross_amount,

                        'paid_at' =>
                            $this->paymentTransaction
                                ->paid_at?->toISOString(),

                        'expired_at' =>
                            $this->paymentTransaction
                                ->expired_at?->toISOString(),
                    ];
                }
            ),

            'created_at' =>
                $this->created_at?->toISOString(),
        ];
    }
}