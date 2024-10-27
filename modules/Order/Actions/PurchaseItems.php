<?php

namespace Modules\Order\Actions;

use Illuminate\Database\DatabaseManager;
use Modules\Order\Models\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\PayBuddy;
use Modules\Product\Dto\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;

/**
 * Summary of PurchaseItems
 */
class PurchaseItems
{
    public function __construct(
        private ProductStockManager $productStockManager,
        private CreatePaymentForOrder $createPaymentForOrder,
        private DatabaseManager $databaseManager
    ) {}

    public function handle(CartItemCollection $items, PayBuddy $payBuddy, string $paymentToken, int $userId): Order
    {

        return $this->databaseManager->transaction(function () use ($items, $payBuddy, $paymentToken, $userId) {

            $order = Order::startForUser($userId);
            $order->addLinesFromCartItems($items);
            $order->fulfill();

            foreach ($items->items() as $cartItem) {
                $this->productStockManager->decrement($cartItem->product->id, $cartItem->quantity);
            }

            $this->createPaymentForOrder->handle(
                $order->id,
                $userId,
                $items->totalInCents(),
                $payBuddy,
                $paymentToken
            );

            return $order;
        });
    }
}
