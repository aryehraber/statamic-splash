<?php

namespace AryehRaber\Splash;

use Statamic\Providers\AddonServiceProvider;

class SplashServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        SplashFieldtype::class,
    ];

    protected $tags = [
        SplashTags::class,
    ];

    protected $scripts = [
        __DIR__.'/../resources/dist/js/splash-fieldtype.js',
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'splash');

        $this->mergeConfigFrom(__DIR__.'/../config/splash.php', 'splash');

        $this->publishes([
            __DIR__.'/../config/splash.php' => config_path('splash.php'),
        ], 'config');
    }
}
