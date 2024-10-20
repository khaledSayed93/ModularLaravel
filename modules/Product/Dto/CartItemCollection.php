<?php

namespace Modules\Product\Dto;

use Illuminate\Support\Collection;
use Modules\Product\Models\Product;

class CartItemCollection
{
    public function __construct(
        protected Collection $items
    ) {}

    public static function fromCheckoutData(array $data)
    {
        $items = collect($data)->map(function (array $productDetails) {
            return new CartItem(
                ProductDto::fromEloquentModel(Product::find($productDetails['id'])),
                $productDetails['quantity']
            );
        });

        return new self($items);
    }

    public function totalInCents()
    {
        return $this->items->sum(
            fn ($cartItem) => $cartItem->quantity * $cartItem->product->priceInCents
        );
    }

    public function items()
    {
        return $this->items;
    }
}
