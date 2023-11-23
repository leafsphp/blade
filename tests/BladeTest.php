<?php

use Leaf\Blade;

$blade = null;

beforeEach(function () use (&$blade) {
    $blade = new Blade('tests/views', 'tests/cache');
});

test('basic', function () use (&$blade) {
    $output = $blade->make('basic');
    expect(trim($output))->toBe('hello world');
});

test('variables', function () use (&$blade) {
    $output = $blade->make('variables', ['name' => 'John Doe']);
    expect(trim($output))->toBe('hello John Doe');
});

test('non-blade', function () use (&$blade) {
    $output = $blade->make('plain');
    expect(trim($output))->toBe('this is plain php');
});

test('render alias', function () use (&$blade) {
    $output = $blade->make('basic');
    expect(trim($output))->toBe('hello world');
});

test('directive', function () use (&$blade) {
    $blade->directive('datetime', function ($expression) {
        return "<?php echo with({$expression})->format('F d, Y g:i a'); ?>";
    });

    $output = $blade->make('directive', ['birthday' => new DateTime('1989/08/19')]);
    expect(trim($output))->toBe('Your birthday is August 19, 1989 12:00 am');
});

test('other', function () use (&$blade) {
    $users = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@doe.com',
        ],
        [
            'id' => 2,
            'name' => 'Jen Doe',
            'email' => 'jen.doe@example.com',
        ],
        [
            'id' => 3,
            'name' => 'Jerry Doe',
            'email' => 'jerry.doe@doe.com',
        ],
    ];

    $output = $blade->make('other', [
        'users' => $users,
        'name' => '<strong>John</strong>',
        'authenticated' => false,
    ]);

    write($output, 'other');

    expect((string)$output)->toBe(expected('other'));
});

function write(string $output, string $file)
{
    $file_path = __DIR__ . '/expected/' . $file . '.html';

    file_put_contents($file_path, $output);
}

function expected(string $file): string
{
    $file_path = __DIR__ . '/expected/' . $file . '.html';

    return file_get_contents($file_path);
}
