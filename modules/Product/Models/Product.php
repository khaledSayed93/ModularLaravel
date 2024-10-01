<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Database\Factories\ProductFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_in_cents',
        'stock'
    ];

    protected static function newFactory()
    {
        return ProductFactory::new();
    }
}
