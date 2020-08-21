<?php

namespace AryehRaber\Splash;

use Statamic\Tags\Tags;
use Statamic\Fields\Value;

class SplashTags extends Tags
{
    protected static $handle = 'splash';

    protected $image;

    protected $defaultParams = [
        'q' => '80',
        'w' => '1500',
        'fit' => 'crop',
        'crop' => 'faces,edges',
        'auto' => 'format',
    ];

    public function wildcard($handle)
    {
        $this->setImage(array_get($this->context, $handle));

        return $this->image();
    }

    public function image()
    {
        return $this->raw($this->defaultParams);
    }

    public function raw($params = [])
    {
        $this->setImage();

        if (! $image = $this->getImage()) return;

        $url = $image['urls']['raw'];
        $query = $this->buildQuery($this->getParams($params));

        return $url . $query;
    }

    public function attribution()
    {
        $this->setImage();

        if (! $image = $this->getImage()) return;

        $user = $image['user'];
        $newTab = $this->getBool('new_tab', true);

        return view('splash::attribution', [
            'userName' => $user['name'] ?? '',
            'userProfileUrl' => $user['links']['html'] ?? '',
            'utm' => 'utm_source=statamic_splash&utm_medium=referral',
            'linkTarget' => $newTab ? ' target="_blank" rel="noopener"' : '',
        ])->render();
    }

    protected function setImage($image = null)
    {
        $this->image = $image ?: $this->image ?: $this->params->get('image');

        return $this;
    }

    protected function getImage()
    {
        if ($this->image instanceof Value) {
            $image = $this->image->value();
        }

        if (is_array($this->image)) {
            $image = $this->image;
        }

        return $image ?? null;
    }

    protected function getParams($defaults = [])
    {
        $params = array_filter([
            'w' => $this->params->get(['w', 'width']),
            'h' => $this->params->get(['h', 'height']),
            'q' => $this->params->get(['q', 'quality']),
            'fm' => $this->params->get(['fm', 'format']),
            'dpr' => $this->params->get('dpr'),
            'fit' => $this->params->get('fit'),
            'crop' => $this->params->get('crop'),
            'auto' => $this->params->get('auto'),
        ]);

        if ($size = $this->params->get('square')) {
            $params['w'] = $size;
            $params['h'] = $size;
        }

        return array_merge($defaults, $params);
    }

    protected function buildQuery($params = [])
    {
        if (empty($params)) return;

        return '&'.http_build_query($params);
    }
}
