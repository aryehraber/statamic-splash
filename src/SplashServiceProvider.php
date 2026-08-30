<?php

namespace AryehRaber\Splash;

use Statamic\Providers\AddonServiceProvider;

class SplashServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'splash';

    protected $fieldtypes = [
        SplashFieldtype::class,
    ];

    protected $tags = [
        SplashTags::class,
    ];

    protected $vite = [
        'input' => [
            'resources/js/splash.js',
            'resources/css/splash.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];
}
