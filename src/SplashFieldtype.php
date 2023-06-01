<?php

namespace AryehRaber\Splash;

use Illuminate\Support\Arr;
use Statamic\Fields\Fieldtype;

class SplashFieldtype extends Fieldtype
{
    protected static $handle = 'splash';

    protected $icon = 'assets';

    public function preload()
    {
        return config('splash', []);
    }

    public function process($data)
    {
        if (is_null($data)) {
            return $data;
        }

        if (config('splash.data_saved') === 'all') {
            return $data;
        }

        $default = [
            'description',
            'urls.raw',
            'user.name',
            'user.links.html',
        ];

        return collect($default)
            ->merge(config('splash.data_saved', []))
            ->mapWithKeys(fn ($key) => [$key => Arr::get($data, $key)])
            ->undot()
            ->all();
    }
}
