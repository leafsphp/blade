<?php

/*
|--------------------------------------------------------------------------
| Test harness for leafs/blade
|--------------------------------------------------------------------------
| Each test gets a fresh sandbox with a views/ and cache/ directory.
| Templates are written on the fly with template() and rendered through
| a real Leaf\Blade instance. _env() (normally provided by leafs/anchor)
| is shimmed so the @env/@getenv directives can be exercised.
*/

define('SANDBOX', '/tmp/blade-test-sandbox' . (getenv('TEST_TOKEN') ? '-' . getenv('TEST_TOKEN') : ''));

if (!function_exists('_env')) {
    function _env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

function setupBladeEnv(): void
{
    if (is_dir(SANDBOX)) {
        exec('rm -rf ' . escapeshellarg(SANDBOX));
    }

    mkdir(SANDBOX . '/views', 0777, true);
    mkdir(SANDBOX . '/cache', 0777, true);
}

function blade(): \Leaf\Blade
{
    return new \Leaf\Blade(SANDBOX . '/views', SANDBOX . '/cache');
}

/** Write a blade template into the sandbox views dir */
function template(string $name, string $content): void
{
    $file = SANDBOX . '/views/' . str_replace('.', '/', $name) . '.blade.php';

    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }

    file_put_contents($file, $content);
}

uses()->beforeEach(fn () => setupBladeEnv())->in(__DIR__);
