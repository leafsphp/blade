<?php

namespace Leaf;

class Blade
{
    protected $blade;

    public function __construct(string $viewPaths = null, string $cachePath = null)
    {
        // Just to maintain compatibility with the original Leaf Blade
    }

    /**
     * Configure your view and cache directories
     */
    public function configure(string $viewPaths, string $cachePath)
    {
        $this->blade = new \Jenssegers\Blade\Blade($viewPaths, $cachePath);
        $this->setupDefaultDirectives();

        return $this->blade;    // Temporary Fix: Issue #5
    }

    /**
     * Render your blade template,
     *
     * A shorter version of the original `make` command.
     * You can optionally pass data into the view as a second parameter
     */
    public function render(string $view, $data = [], $mergeData = [])
    {
        return $this->make($view, $data, $mergeData);
    }

    /**
     * Render your blade template,
     *
     * You can optionally pass data into the view as a second parameter.
     * Don't forget to chain the `render` method
     */
    public function make(string $view, $data = [], $mergeData = []): string
    {
        return $this->blade->make($view, $data, $mergeData)->render();
    }

    /**
     * Add a new namespace to the loader
     */
    public function directive(string $name, callable $handler)
    {
        $this->blade->directive($name, $handler);
    }

    /**
     * Return actual blade instance
     * @return \Jenssegers\Blade\Blade
     */
    public function blade()
    {
        return $this->blade;
    }

    /**
     * Hook into the blade compiler
     */
    public function compiler()
    {
        return $this->blade->compiler();
    }

    /**
     * Setup default directives
     */
    protected function setupDefaultDirectives()
    {
        $this->directive('csrf', function ($expression) {
            return "<?php echo function_exists('csrf') && csrf()->form(); ?>";
        });

        $this->directive('method', function ($expression) {
            return "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . $expression . '\" />'; ?>";
        });

        $this->directive('json', function ($expression) {
            return "<?php echo json_encode($expression); ?>";
        });

        $this->directive('vite', function ($expression) {
            return "<?php echo vite($expression); ?>";
        });

        $this->directive('meta', function ($expression) {
            return "<?php echo Meta($expression); ?>";
        });

        $this->directive('icon', function ($expression) {
            return "<?php echo Icon($expression); ?>";
        });

        $this->directive('jsIcon', function ($expression) {
            return "<?php echo e(Icon($expression)); ?>";
        });

        $this->directive('alpine', function ($expression) {
            return '<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>';
        });

        $this->directive('isNull', function ($expression) {
            return "<?php if (is_null($expression)) : ?>";
        });

        $this->directive('endisNull', function ($expression) {
            return "<?php endif; ?>";
        });

        $this->compiler()->directive('env', function ($expression) {
            if (empty($expression)) {
                return "<?php echo _env('APP_ENV'); ?>";
            }

            return "<?php if (strtolower(_env('APP_ENV')) === strtolower($expression)) : ?>";
        });

        $this->directive('session', function ($expression) {
            return implode('', [
                "<?php if (session()->has($expression)) : ?>",
                "<?php if (isset(\$value)) { \$___originalCurrentSessionValue = \$value; } ?>",
                "<?php \$value = session()->get($expression); ?>"
            ]);
        });

        $this->directive('endsession', function ($expression) {
            return implode('', [
                "<?php unset(\$value); ?>",
                "<?php if (isset(\$___originalCurrentSessionValue)) { \$value = \$___originalCurrentSessionValue; } ?>",
                "<?php endif; ?>"
            ]);
        });

        $this->directive('flash', function ($expression) {
            return implode('', [
                "<?php if (isset(\$message)) { \$___originalCurrentFlashValue = \$message; } ?>",
                "<?php if (\$message = flash()->display($expression)) : ?>",
            ]);
        });

        $this->directive('endflash', function ($expression) {
            return implode('', [
                "<?php unset(\$message); ?>",
                "<?php if (isset(\$___originalCurrentFlashValue)) { \$message = \$___originalCurrentFlashValue; } ?>",
                "<?php endif; ?>",
            ]);
        });

        $this->directive('disabled', function ($expression) {
            return "<?php echo $expression ? 'disabled' : ''; ?>";
        });

        $this->directive('selected', function ($expression) {
            return "<?php echo $expression ? 'selected' : ''; ?>";
        });

        $this->directive('checked', function ($expression) {
            return "<?php echo $expression ? 'checked' : ''; ?>";
        });

        $this->directive('readonly', function ($expression) {
            return "<?php echo $expression ? 'readonly' : ''; ?>";
        });

        $this->directive('required', function ($expression) {
            return "<?php echo $expression ? 'required' : ''; ?>";
        });

        $this->directive('use', function ($expression) {
            $expression = preg_replace('/[\'"]/', '', $expression);
            return "<?php use $expression; ?>";
        });

        $this->compiler()->directive('auth', function ($expression) {
            return "<?php if (!!auth($expression)->user()) : ?>";
        });

        $this->compiler()->directive('guest', function ($expression) {
            return "<?php if (!auth($expression)->user()) : ?>";
        });

        $this->directive('is', function ($expression) {
            return "<?php if (auth()->user()->is($expression)) : ?>";
        });

        $this->directive('endis', function ($expression) {
            return "<?php endif; ?>";
        });

        $this->directive('isnot', function ($expression) {
            return "<?php if (auth()->user()->isNot($expression)) : ?>";
        });

        $this->directive('endisnot', function ($expression) {
            return "<?php endif; ?>";
        });

        $this->directive('can', function ($expression) {
            return "<?php if (auth()->user()->can($expression)) : ?>";
        });

        $this->directive('endcan', function ($expression) {
            return "<?php endif; ?>";
        });

        $this->directive('cannot', function ($expression) {
            return "<?php if (auth()->user()->cannot($expression)) : ?>";
        });

        $this->directive('endcannot', function ($expression) {
            return "<?php endif; ?>";
        });
    }
}
