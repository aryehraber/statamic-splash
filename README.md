# Splash

**Browse Unsplash images right from the CP.**

## Installation

Install the addon via composer:

```
composer require aryehraber/statamic-splash
```

Publish the fieldtype assets & config file:

```
php please vendor:publish --tag=splash-config
```

Once installed, you will need to add an Unsplash API `Access Key` to `config/splash.php`.

You can register for a free account via the [Unsplash Developers page](https://unsplash.com/developers). Next, simply create a "New Application" and use the Demo `Access Key`, which allows 50 requests/hour, or go through the approval process to increase to 5000 requests/hour (this is still free but takes a little more effort). Note: "requests" only refers to search queries in the CP, once an image has been selected you will be referencing the CDN link which doesn't go through the API.

## Usage

Simply add a field inside your blueprint and use `type: splash` to get started.

**Blueprint**

```yaml
fields:
  -
    handle: hero_image
    field:
      type: splash
```

After selecting an image and saving your content, the Unsplash response data will be accessible in templates using Antlers ([more on saved photo data below](#photo-data)). The image itself *will not* be saved inside your Asset Container, instead you will be referencing images using Unsplash's CDN, also called Hotlinking (this is required by their [API Guidelines](https://help.unsplash.com/en/articles/2511271-guideline-hotlinking-images)).

The benefit here is that it takes a significant load off your server, since images are often the heaviest assets on a page. Additionally, since Unsplash's CDN is spread worldwide, images will load super fast regardless of the visitor's location.

## Tags

### Raw

`{{ splash:raw :image="field_name" }}`

This tag outputs the CDN link to the image's `raw` URL. Due to the high-quality nature of Unsplash photos, images are often 10-20MB, which is overkill for _most_ websites; you can therefore use a few Glide-like parameters to request exactly what you need (more info below).

**Example**

```html
{{ splash:raw :image="hero_image" width="2000" quality="80" }}
```

---

### Image

`{{ splash:image :image="field_name" }}` or `{{ splash:field_name }}`

This tag also outputs the CDN link to the image's `raw` URL, but includes a few sensible defaults to reduce the image filesize a whole lot.

Any of these params can be overriden by using the parameters listed below, the defaults used are:

```php
'q' => '80',
'w' => '1500',
'fit' => 'crop',
'crop' => 'faces,edges',
'auto' => 'format',
```

**Example**

```html
{{ splash:image :image="hero_image" width="1000" }}
```
Or using the shorthand:
```html
{{ splash:hero_image width="1000" }}
```

---

### Attribution

`{{ splash:attribution :image="field_name" }}`

This tag outputs the Unsplash attribution text from their [API Guidelines](https://help.unsplash.com/en/articles/2511315-guideline-attribution), including links to the photographer's profile and Unsplash website.

**Example**

```html
{{ splash:attribution :image="hero_image" }}
```

**Output**

```html
Photo by <a href="https://unsplash.com/@anniespratt?utm_source=statamic_splash&utm_medium=referral">Annie Spratt</a> on <a href="https://unsplash.com/?utm_source=statamic_splash&utm_medium=referral">Unsplash</a>
```

Since the photo's meta data is stored inside your content, you can also loop over the photographer's data using Antlers:

```html
{{ hero_image:user }}
  <!-- access available user data -->
{{ /hero_image:user }}
```

---

### Photo Data

By default, only the required photo data is saved from the Unsplash response data for this addon to work. You may specify additional keys using dot notation in the `data_saved` config option inside `config/splash.php`.

**Example**

```php
<?php

return [
    'data_saved' => [
        'description',
        'links',
        'user.username',
        'user.bio',
    ],
    // ...
];
```

Alternatively, if you would like to save ALL photo data, you may use the `all` option instead.

```php
<?php

return [
    'data_saved' => 'all',
    // ...
];
```

---

### Parameters

Splash offers a number of Glide-like parameters to transform images to your needs. You can pass any [Unsplash parameter](https://unsplash.com/documentation#supported-parameters) into the `raw` and `image` tags to get started. Just like when using Glide within Statamic, you can also use the easier-to-read alias parameters below:

| Param | Description |
|-------|-------------|
| `width` | Sets the width (in pixels). |
| `height` | Sets the height (in pixels). |
| `square` | Shortcut to set width and height to the same value. |
| `quality` | Sets the compression quality (value between 0 and 100).  |
| `format` | Encodes the image to a specific format (recommended to use `auto="format"` instead to auto-pick based on visitor's browser). |
| `dpr` | Adjusts the device pixel ratio of the image. |
| `fit` | Changes the fit of the image within the specified dimensions ([see all available values on Imgix](https://docs.imgix.com/apis/url/size/fit)). |
| `crop` | Applies cropping to the image ([see all available values on Imgix](https://docs.imgix.com/apis/url/size/crop)). |
| `auto` | Automates a baseline level of optimization for the image ([see all available values on Imgix](https://docs.imgix.com/apis/url/auto/auto)). |
