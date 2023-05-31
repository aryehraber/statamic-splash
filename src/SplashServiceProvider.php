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

    protected $scripts = [
        __DIR__.'/../dist/splash-fieldtype.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../dist/splash-fieldtype.css',
        __DIR__.'/../dist/splash-fieldtype2.css',
    ];
}
