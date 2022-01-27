<?php

namespace Chameleon\YigimMagnet;

use Illuminate\Support\ServiceProvider;

class MagnetServiceProvider extends ServiceProvider
{
    /**
     * Boot
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/yigim-magnet.php' => config_path('yigim-magnet.php'),
        ]);
    }

    /**
     * Register
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/yigim-magnet.php',
            'yigim-magnet'
        );
        $this->app->singleton(Magnet::class, function () {
            return Magnet::create($this->app['config']->get('yigim'));
        });
        $this->app->alias(Magnet::class, 'yigim-magnet');
    }
}
