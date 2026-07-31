<?php

namespace Webkul\PaymentId\Providers;

use Illuminate\Support\ServiceProvider;

class PaymentIdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/payment-methods.php', 'payment_methods'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(dirname(__DIR__).'/Http/routes.php');
        $this->loadViewsFrom(dirname(__DIR__).'/Resources/views', 'paymentid');
        $this->loadTranslationsFrom(dirname(__DIR__).'/Resources/lang', 'paymentid');
    }
}
