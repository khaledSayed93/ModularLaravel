<?php

namespace Modules\Order\Http\Controllers;

use Modules\Order\Actions\PurchaseItems;
use Modules\Product\Dto\CartItemCollection;
use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Payment\PayBuddy; // cross boundary communication

class CheckoutController
{
    /**
     * Class constructor.
     */
    public function __construct(protected PurchaseItems $purchaseItems) {}

    public function __invoke(CheckoutRequest $request)
    {
        $cartItemsCollection = CartItemCollection::fromCheckoutData($request->input('products'));

        try {
            $order = $this->purchaseItems->handle(
                $cartItemsCollection,
                PayBuddy::make(),
                $request->payment_token,
                $request->user()->id
            );
        } catch (PaymentFailedException $e) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment.'
            ]);
        }

        return response()->json([
            'order_url' => $order->url()
        ], 201);
    }
}
