<?php

namespace Modules\Order\Actions;

use Modules\Payment\PayBuddy;
use Modules\Order\Models\Order;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Mail\OrderReceived;
use Illuminate\Database\DatabaseManager;
use Modules\Order\Events\OrderFulfilled;
use Illuminate\Contracts\Events\Dispatcher;
use Modules\Product\Dto\CartItemCollection;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Product\Warehouse\ProductStockManager;

/**
 * Summary of PurchaseItems
 */
class PurchaseItems
{
    public function __construct(
        private ProductStockManager $productStockManager,
        private CreatePaymentForOrder $createPaymentForOrder,
        private DatabaseManager $databaseManager,
        private Dispatcher $dispatcher
    ) {}

    public function handle(CartItemCollection $items, PayBuddy $payBuddy, string $paymentToken, int $userId, string $userEmail): Order
    {

        return $this->databaseManager->transaction(function () use ($items, $payBuddy, $paymentToken, $userId, $userEmail) {

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

            Mail::to($userEmail)->send(new OrderReceived($order->localizedTotal()));

            // $this->dispatcher->dispatch(
            //     new OrderFulfilled(
            //         $order->id,
            //         $order->total_in_cents,
            //         $order->localizedTotal(),
            //         $items,
            //         $userId,
            //         $userEmail
            //     )
            // );


            return $order;
        });
    }
}
