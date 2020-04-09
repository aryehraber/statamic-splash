<?php

namespace AryehRaber\Splash;

use Statamic\Fields\Fieldtype;

class SplashFieldtype extends Fieldtype
{
    protected static $handle = 'splash';

    protected $icon = 'assets';

    public function preload()
    {
        return config('splash', []);
    }
}
