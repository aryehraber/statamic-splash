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
}
