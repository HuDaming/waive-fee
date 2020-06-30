<?php

namespace App\Providers;

use Utils\Alipay\Alipay;
use Illuminate\Support\ServiceProvider;
use Utils\Alipay\AlipayContract;

class AlipayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('alipay', function ($app) {
            return new Alipay(config('services.alipay'));
        });

        $this->app->bind(AlipayContract::class, function () {
            return new Alipay(config('services.alipay'));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    public function provides()
    {
        return [Alipay::class];
    }
}
