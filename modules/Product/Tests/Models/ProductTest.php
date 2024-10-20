<?php

namespace Modules\Product\Tests\Models;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Product\Models\Product;
use Modules\Product\Tests\ProductTestCase;

class ProductTest extends ProductTestCase
{
    use DatabaseMigrations;

    public function test_it_creates_a_product()
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Product::class, $product);
    }
}
