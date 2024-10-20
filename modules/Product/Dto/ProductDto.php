<?php

namespace Modules\Product\Dto;

use Modules\Product\Models\Product;

class ProductDto
{
    public function __construct(
        public $id,
        public $priceInCents,
        public $unitInStock
    ) {}

    public static function fromEloquentModel(Product $product): self
    {
        return new self(
            $product->id,
            $product->price_in_cents,
            $product->stock
        );
    }
}
