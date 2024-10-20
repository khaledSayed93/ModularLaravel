<?php

namespace Modules\Order\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as BaseRouteServiceProvider;

class RouteServiceProvider extends BaseRouteServiceProvider
{

    public function boot()
    {
        $this->routes(function () {
            Route::middleware('web')
                ->as('order::')
                ->group(__DIR__ . '/../routes.php');
        });
    }
}
