<?php

namespace Modules\Payment\Actions;

use RuntimeException;
use Modules\Payment\Payment;
use Modules\Payment\PayBuddy;
use Modules\Order\Exceptions\PaymentFailedException;

class CreatePaymentForOrder
{
    public function handle(int $orderId, int $userId, int $totalInCents, PayBuddy $payBuddy, string $paymentToken)
    {
        try {
            $charge = $payBuddy->charge($paymentToken, $totalInCents, 'Modularization');
        } catch (RuntimeException) {
            throw PaymentFailedException::dueToInvalidToken();
        }

        Payment::query()->create([
            'total_in_cents' => $totalInCents,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $userId,
            'order_id' => $orderId
        ]);
    }
}
