<?php

namespace Modules\Product\Dto;

class CartItem
{
    public function __construct(
        public ProductDto $product,
        public int $quantity
    ) {}
}
