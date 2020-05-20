<?php

namespace App\Providers;

use App\Exchanges\Base as Exchange;
use App\Exchanges\Binance;
use App\Uuid;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Exchange::class, function ($app) {
            return new Binance();
        });

        $this->app->singleton(Uuid::class, function ($app) {
            return new Uuid();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
