<?php

namespace Modules\Product\Providers;

use Modules\Order\Events\OrderFulfilled;
use Modules\Product\Events\DecreaseProductStock;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as BaseEventServiceProvider;

class EventServiceProvider extends BaseEventServiceProvider
{
    protected $listen = [
        OrderFulfilled::class => [
            DecreaseProductStock::class
        ]
    ];
}
