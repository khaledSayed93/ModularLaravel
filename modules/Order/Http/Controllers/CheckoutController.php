<?php

namespace Modules\Order\Http\Controllers;

use RuntimeException;
use Modules\Order\Models\Order;
use Modules\Product\Dto\CartItem;
use Modules\Product\Dto\CartItemCollection;
use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Payment\PayBuddy; // cross boundary communication
use Modules\Product\Models\Product; // cross boundary communication
use Modules\Product\Warehouse\ProductStockManager;

class CheckoutController
{
    /**
     * Class constructor.
     */
    public function __construct(protected ProductStockManager $productStockManager)
    {
    }

    public function __invoke(CheckoutRequest $request)
    {
        $cartItemsCollection = CartItemCollection::fromCheckoutData($request->input('products'));
        $orderTotalInCents = $cartItemsCollection->totalInCents();

        $payBuddy = PayBuddy::make();

        try {
            $charge = $payBuddy->charge($request->input('payment_token'), $orderTotalInCents, 'Modularization');
        } catch (RuntimeException) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment.'
            ]);
        }

        $order = Order::query()->create([
            'status' => 'completed',
            'total_in_cents' => $orderTotalInCents,
            'user_id' => $request->user()->id
        ]);

        foreach ($cartItemsCollection->items() as $cartItem) {
            $this->productStockManager->decrement($cartItem->product->id, $cartItem->quantity);

            $order->lines()->create([
                'product_id' => $cartItem->product->id,
                'product_price_in_cents' => $cartItem->product->priceInCents,
                'quantity' => $cartItem->quantity
            ]);
        }

        $payment = $order->payments()->create([
            'total_in_cents' => $orderTotalInCents,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json([], 201);
    }
}
