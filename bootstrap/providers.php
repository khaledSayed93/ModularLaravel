<?php

use Modules\Order\Providers\OrderServiceProvider;
use Modules\Shipment\Providers\ShipmentServiceProvider;
use Modules\Product\Providers\ProductServiceProvider;
use Modules\Payment\Infrastructure\Providers\PaymentServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    OrderServiceProvider::class,
    ProductServiceProvider::class,
    ShipmentServiceProvider::class,
    PaymentServiceProvider::class
];
