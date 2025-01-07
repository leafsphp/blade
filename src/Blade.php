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
     * Summary of blade
     * @return \Jenssegers\Blade\Blade
     */
    public function blade()
    {
        return $this->blade;
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
    }
}
