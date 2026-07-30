<p align="center">
    <br><br>
    <img src="https://leaf-docs.netlify.com/images/logo.png" height="100"/>
    <h1 align="center">Leaf Blade</h1>
    <br>
    <br><br><br>
</p>

[![Latest Stable Version](https://img.shields.io/packagist/v/leafs/blade)](https://packagist.org/packages/leafs/blade) [![Total Downloads](https://img.shields.io/packagist/dt/leafs/blade)](https://packagist.org/packages/leafs/blade) [![License](https://img.shields.io/packagist/l/leafs/blade)](https://packagist.org/packages/leafs/blade)

Leaf's adaptation of [Laravel's Blade templating engine](https://laravel.com/docs/12.x/blade) for use outside of Laravel, with extra directives for the Leaf ecosystem.

## Installation

Install using composer:

```bash
composer require leafs/blade
```

## Usage

Create a Blade instance by passing it the folder(s) where your view files are located, and a cache folder. Render a template by calling the `make` method. More information about the Blade templating engine can be found on https://laravel.com/docs/12.x/blade.

```php
use Leaf\Blade;

$blade = new Blade('app/views', 'app/views/cache');

```
You can also initialise it globally and point to template directories later.

```php
$blade = new Blade;

// somewhere, maybe in a different file
$blade->configure("app/views", "app/views/cache");
```

```php
echo $blade->make('index', ['name' => 'Michael Darko'])->render();
```

Alternatively you can use the shorthand method `render`:

```php
echo $blade->render('index', ['name' => 'Michael Darko']);
```

We can have this as our template `index.blade.php`
```php
<!Doctype html>
<html>
    <head>
        <title>{{ $name }}</title>
    </head>
    <body>
        <div class="container">{{ $name }}</div>
    </body>
</html>
```

You can also extend Blade using the `directive()` function:

```php
$blade->directive('datetime', function ($expression) {
    return "<?php echo with({$expression})->format('F d, Y g:i a'); ?>";
});
```

Which allows you to use the following in your blade template:

```
Current date: @datetime($date)
```

The Blade instances passes all methods to the internal view factory. So methods such as `exists`, `file`, `share`, `composer` and `creator` are available as well. Check out the [original documentation](https://laravel.com/docs/12.x/views) for more information.
