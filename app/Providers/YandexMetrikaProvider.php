<?php

declare(strict_types=1);

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Services\YandexMetrika\YandexMetrika;

class YandexMetrikaProvider extends ServiceProvider
{
    /**
     * Additional service providers to register for the environment.
     */
    public function register()
    {

        $this->app->bind('yandexMetrikaApi', function () {
            return new YandexMetrika;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/yandex-config.php' => config_path('yandex-config.php'),
        ], 'config');
    }
}
