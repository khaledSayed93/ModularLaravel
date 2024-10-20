<?php

namespace Modules\Shipment\Providers;

use Illuminate\Support\ServiceProvider;

class ShipmentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->register(RouteServiceProvider::class);
    }
}
