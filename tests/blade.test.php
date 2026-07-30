<?php

test('renders a template with data and escapes output', function () {
    template('hello', 'Hello, {{ $name }}!');

    expect(blade()->render('hello', ['name' => 'Leaf']))->toBe('Hello, Leaf!');
    expect(blade()->render('hello', ['name' => '<b>x</b>']))->toBe('Hello, &lt;b&gt;x&lt;/b&gt;!');
});

test('unescaped echo and core directives work', function () {
    template('core', '{!! $html !!} @if($on) on @else off @endif');

    expect(blade()->render('core', ['html' => '<i>raw</i>', 'on' => true]))->toContain('<i>raw</i>', 'on');
    expect(blade()->render('core', ['html' => '', 'on' => false]))->toContain('off');
});

test('make is an alias of render and returns a string', function () {
    template('hello', 'Hi {{ $name }}');

    expect(blade()->make('hello', ['name' => 'Leaf']))->toBe('Hi Leaf');
});

test('templates in subfolders resolve with dot notation', function () {
    template('emails.welcome', 'welcome!');

    expect(blade()->render('emails.welcome'))->toBe('welcome!');
});

test('layouts, sections and includes compose', function () {
    template('layout', "<main>@yield('content')</main>");
    template('partial', 'partial-bit');
    template('page', "@extends('layout') @section('content')page @include('partial')@endsection");

    expect(blade()->render('page'))->toContain('<main>page partial-bit</main>');
});

test('configure can be called after construction', function () {
    $blade = new \Leaf\Blade();
    $blade->configure(SANDBOX . '/views', SANDBOX . '/cache');

    template('hello', 'hey');

    expect($blade->render('hello'))->toBe('hey');
});

test('configure accepts an array of views and cache paths', function () {
    $blade = new \Leaf\Blade();
    $blade->configure(['views' => SANDBOX . '/views', 'cache' => SANDBOX . '/cache']);

    template('hello', 'hey again');

    expect($blade->render('hello'))->toBe('hey again');
});

test('exists and share pass through to the view factory', function () {
    template('real', 'shared: {{ $global }}');

    $blade = blade();
    $blade->share('global', 'everywhere');

    expect($blade->exists('real'))->toBeTrue();
    expect($blade->exists('fake'))->toBeFalse();
    expect($blade->render('real'))->toBe('shared: everywhere');
});

test('custom directives can be registered', function () {
    template('custom', 'Time: @datetime($date)');

    $blade = blade();
    $blade->directive('datetime', function ($expression) {
        return "<?php echo ($expression)->format('Y-m-d'); ?>";
    });

    expect($blade->render('custom', ['date' => new DateTime('2026-01-15')]))->toBe('Time: 2026-01-15');
});

test('custom conditions can be registered with if', function () {
    template('cond', "@leaf('yes') leafy @endleaf");

    $blade = blade();
    $blade->if('leaf', fn ($value) => $value === 'yes');

    expect($blade->render('cond'))->toContain('leafy');
});

test('@json encodes data', function () {
    template('json', '@json($data)');

    expect(blade()->render('json', ['data' => ['a' => 1, 'b' => [2, 3]]]))->toBe('{"a":1,"b":[2,3]}');
});

test('form state directives print their attribute only when truthy', function () {
    template('form', '<input @checked($a) @disabled($b) @selected($c) @readonly($d) @required($e) />');

    expect(blade()->render('form', ['a' => true, 'b' => false, 'c' => true, 'd' => false, 'e' => true]))
        ->toContain('checked')
        ->toContain('selected')
        ->toContain('required')
        ->not->toContain('disabled')
        ->not->toContain('readonly');
});

test('@isNull renders only for null values', function () {
    template('null', '@isNull($value) was-null @endisNull');

    expect(blade()->render('null', ['value' => null]))->toContain('was-null');
    expect(blade()->render('null', ['value' => 'set']))->not->toContain('was-null');
});

test('@method renders a hidden method field', function () {
    template('method', "@method('PUT')");

    expect(blade()->render('method'))->toBe('<input type="hidden" name="_METHOD" value="PUT" />');
});

test('@env branches on APP_ENV and @getenv echoes values', function () {
    template('env', "@env('production') live @endenv\n@getenv('APP_ENV')");

    $_ENV['APP_ENV'] = 'production';
    expect(blade()->render('env'))->toContain('live')->toContain('production');

    $_ENV['APP_ENV'] = 'local';
    expect(blade()->render('env'))->not->toContain('live')->toContain('local');
});

test('@auth hides content and @guest shows it when leaf auth is not installed', function () {
    template('auth', "@auth secret @endauth\n@guest public @endguest");

    $out = blade()->render('auth');
    expect($out)->not->toContain('secret');
    expect($out)->toContain('public');
});

test('@csrf renders nothing when leaf csrf is not installed', function () {
    template('csrf', '[@csrf]');

    expect(blade()->render('csrf'))->toBe('[]');
});

test('@use imports a class for the template', function () {
    template('use', "@use('Illuminate\\Support\\Str')<?php echo Str::upper('leaf'); ?>");

    expect(blade()->render('use'))->toBe('LEAF');
});

test('@alpine outputs the alpinejs script tag', function () {
    template('alpine', '@alpine');

    expect(blade()->render('alpine'))->toContain('<script defer src="https://cdn.jsdelivr.net/npm/alpinejs');
});

test('templates are compiled to the cache directory and reused', function () {
    template('cached', 'v1 {{ $x }}');

    $blade = blade();
    expect($blade->render('cached', ['x' => 'a']))->toBe('v1 a');
    expect(glob(SANDBOX . '/cache/*.php'))->not->toBeEmpty();

    // stale cache is reused until the template changes on disk
    expect($blade->render('cached', ['x' => 'b']))->toBe('v1 b');
});
