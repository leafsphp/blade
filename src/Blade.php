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
}
