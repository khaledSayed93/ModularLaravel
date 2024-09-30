<?php

use Modules\Order\Providers\OrderServiceProvider;
use Modules\Shipment\Providers\ShipmentServiceProvider;
use Modules\Product\Providers\ProductServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    OrderServiceProvider::class,
    ProductServiceProvider::class,
    ShipmentServiceProvider::class
];
