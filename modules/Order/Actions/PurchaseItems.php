<?php

namespace Modules\Order\Actions;

use RuntimeException;
use Modules\Payment\PayBuddy;
use Modules\Order\Models\Order;
use Modules\Product\Dto\CartItemCollection;
use Illuminate\Validation\ValidationException;
use Modules\Product\Warehouse\ProductStockManager;

class PurchaseItems
{
    public function __construct(
        private ProductStockManager $productStockManager
    ){}

    public function handle(CartItemCollection $items, PayBuddy $payBuddy, string $paymentToken, int $userId): Order
    {
        $orderTotalInCents = $items->totalInCents();

        try {
            $charge = $payBuddy->charge($paymentToken, $orderTotalInCents, 'Modularization');
        } catch (RuntimeException) {
            throw PaymentFailedException::dueToInvalidToken();
        }

        $order = Order::query()->create([
            'status' => 'completed',
            'total_in_cents' => $orderTotalInCents,
            'user_id' => $userId
        ]);

        foreach ($items->items() as $cartItem) {
            $this->productStockManager->decrement($cartItem->product->id, $cartItem->quantity);

            $order->lines()->create([
                'product_id' => $cartItem->product->id,
                'product_price_in_cents' => $cartItem->product->priceInCents,
                'quantity' => $cartItem->quantity
            ]);
        }

        $order->payments()->create([
            'total_in_cents' => $orderTotalInCents,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $userId,
        ]);

        return $order;
    }
}
