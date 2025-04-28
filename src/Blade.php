<?php

namespace Leaf;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Illuminate\View\ViewServiceProvider;
use Leaf\Blade\Container;

class Blade
{
    /**
     * @var Application
     */
    protected $container;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var BladeCompiler
     */
    private $compiler;

    public function __construct(?string $viewPaths = null, ?string $cachePath = null)
    {
        if ($viewPaths) {
            $this->configure($viewPaths, $cachePath);
        }
    }

    /**
     * Configure your view and cache directories
     * 
     * @param string|array $viewPaths The path to your view or an array of paths
     * @param string|null $cachePath The path to your cache directory
     * 
     * @return \Leaf\Blade
     */
    public function configure($viewPaths, ?string $cachePath = null)
    {
        if (is_array($viewPaths)) {
            $cachePath = $viewPaths['cache'] ?? null;
            $viewPaths = $viewPaths['views'] ?? null;
        }

        $this->container = Container::getInstance();

        $this->setupContainer((array) $viewPaths, $cachePath);
        (new ViewServiceProvider($this->container))->register();

        $this->factory = $this->container->get('view');
        $this->compiler = $this->container->get('blade.compiler');

        $this->setupDefaultDirectives();

        return $this;
    }

    /**
     * Configure your view and cache directories
     * 
     * @param string|array $viewPaths The path to your view or an array of paths
     * @param string|null $cachePath The path to your cache directory
     * 
     * @return \Leaf\Blade
     */
    public function config($viewPaths, ?string $cachePath = null)
    {
        return $this->configure($viewPaths, $cachePath);
    }

    /**
     * Render your blade template,
     *
     * A shorter version of the original `make` command.
     * You can optionally pass data into the view as a second parameter
     */
    public function render(string $view, $data = [], $mergeData = []): string
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
        return $this->factory->make($view, $data, $mergeData)->render();
    }

    /**
     * Add a new namespace to the loader
     */
    public function directive(string $name, callable $handler)
    {
        $this->compiler()->directive($name, $handler);
    }

    /**
     * Return actual blade instance
     * @return \Leaf\Blade
     */
    public function blade()
    {
        return $this;
    }

    /**
     * Hook into the blade compiler
     */
    public function compiler()
    {
        return $this->compiler;
    }

    public function if($name, callable $callback)
    {
        $this->compiler->if($name, $callback);
    }

    public function exists($view): bool
    {
        return $this->factory->exists($view);
    }

    public function file($path, $data = [], $mergeData = []): View
    {
        return $this->factory->file($path, $data, $mergeData);
    }

    public function share($key, $value = null)
    {
        return $this->factory->share($key, $value);
    }

    public function composer($views, $callback): array
    {
        return $this->factory->composer($views, $callback);
    }

    public function creator($views, $callback): array
    {
        return $this->factory->creator($views, $callback);
    }

    public function addNamespace($namespace, $hints): self
    {
        $this->factory->addNamespace($namespace, $hints);

        return $this;
    }

    public function replaceNamespace($namespace, $hints): self
    {
        $this->factory->replaceNamespace($namespace, $hints);

        return $this;
    }

    public function __call(string $method, array $params)
    {
        return call_user_func_array([$this->factory, $method], $params);
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
            return "<?php echo '<input type=\"hidden\" name=\"_METHOD\" value=\"' . $expression . '\" />'; ?>";
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

        $this->compiler()->directive('endenv', function ($expression) {
            return "<?php endif; ?>";
        });

        $this->directive('getenv', function ($expression) {
            return "<?php echo _env($expression); ?>";
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

        $this->directive('assets', function ($expression) {
            return "<?php echo assets($expression); ?>";
        });

        $this->directive('storage', function ($expression) {
            return "<?php echo StoragePath($expression); ?>";
        });

        $this->directive('views', function ($expression) {
            return "<?php echo ViewsPath($expression); ?>";
        });

        $this->compiler()->directive('route', function ($expression) {
            return "<?php echo app()->route($expression)['path'] ?? ''; ?>";
        });

        $this->compiler()->directive('viteReactRefresh', function ($expression) {
            return "<?php echo \Leaf\Vite::reactRefresh(); ?>";
        });

        $this->compiler()->directive('inertiaHead', function ($expression) {
            return '<?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = (new \Leaf\Inertia\Ssr\Gateway())->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->head; } ?>';
        });

        $this->compiler()->directive('inertia', function ($expression) {
            return '<?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = (new \Leaf\Inertia\Ssr\Gateway())->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->body; } else { ?><div id="app" data-page="<?php echo e(json_encode($page)); ?>"></div><?php } ?>';
        });

        $this->directive('toastContainer', function ($expression) {
            return <<<HTML
            <div class="relative w-auto h-auto" x-data>
                <template x-teleport="body">
                    <ul x-data="{
                        toasts: [],
                        toastsHovered: false,
                        position: 'top-center',
                        paddingBetweenToasts: 15,
                        deleteToastWithId(id) {
                            for (let i = 0; i < this.toasts.length; i++) {
                                if (this.toasts[i].id === id) {
                                    this.toasts.splice(i, 1);
                                    break;
                                }
                            }
                        },
                        burnToast(id) {
                            burnToast = this.getToastWithId(id);
                            burnToastElement = document.getElementById(burnToast.id);
                            if (burnToastElement) {
                                if (this.toasts.length == 1) {
                                    burnToastElement.classList.remove('translate-y-0');
                                    if (this.position.includes('bottom')) {
                                        burnToastElement.classList.add('translate-y-full');
                                    } else {
                                        burnToastElement.classList.add('-translate-y-full');
                                    }
                                    burnToastElement.classList.add('-translate-y-full');
                                }
                                burnToastElement.classList.add('opacity-0');
                                let that = this;
                                setTimeout(function() {
                                    that.deleteToastWithId(id);
                                    setTimeout(function() {
                                        that.stackToasts();
                                    }, 1)
                                }, 300);
                            }
                        },
                        getToastWithId(id) {
                            for (let i = 0; i < this.toasts.length; i++) {
                                if (this.toasts[i].id === id) {
                                    return this.toasts[i];
                                }
                            }
                        },
                        stackToasts() {
                            this.positionToasts();
                            this.calculateHeightOfToastsContainer();
                            let that = this;
                            setTimeout(function() {
                                that.calculateHeightOfToastsContainer();
                            }, 300);
                        },
                        positionToasts() {
                            if (this.toasts.length == 0) return;
                            let topToast = document.getElementById(this.toasts[0].id);
                            topToast.style.zIndex = 100;

                            if (this.position.includes('bottom')) {
                                topToast.style.top = 'auto';
                                topToast.style.bottom = '0px';
                            } else {
                                topToast.style.top = '0px';
                            }

                            let bottomPositionOfFirstToast = this.getBottomPositionOfElement(topToast);

                            if (this.toasts.length == 1) return;
                            let middleToast = document.getElementById(this.toasts[1].id);
                            middleToast.style.zIndex = 90;

                            middleToastPosition = topToast.getBoundingClientRect().height +
                                this.paddingBetweenToasts + 'px';

                            if (this.position.includes('bottom')) {
                                middleToast.style.top = 'auto';
                                middleToast.style.bottom = middleToastPosition;
                            } else {
                                middleToast.style.top = middleToastPosition;
                            }

                            middleToast.style.scale = '100%';
                            middleToast.style.transform = 'translateY(0px)';


                            if (this.toasts.length == 2) return;
                            let bottomToast = document.getElementById(this.toasts[2].id);
                            bottomToast.style.zIndex = 80;

                            bottomToastPosition = topToast.getBoundingClientRect().height +
                                this.paddingBetweenToasts +
                                middleToast.getBoundingClientRect().height +
                                this.paddingBetweenToasts + 'px';

                            if (this.position.includes('bottom')) {
                                bottomToast.style.top = 'auto';
                                bottomToast.style.bottom = bottomToastPosition;
                            } else {
                                bottomToast.style.top = bottomToastPosition;
                            }

                            bottomToast.style.scale = '100%';
                            bottomToast.style.transform = 'translateY(0px)';



                            if (this.toasts.length == 3) return;
                            let burnToast = document.getElementById(this.toasts[3].id);
                            burnToast.style.zIndex = 70;

                            burnToastPosition = topToast.getBoundingClientRect().height +
                                this.paddingBetweenToasts +
                                middleToast.getBoundingClientRect().height +
                                this.paddingBetweenToasts +
                                bottomToast.getBoundingClientRect().height +
                                this.paddingBetweenToasts + 'px';

                            if (this.position.includes('bottom')) {
                                burnToast.style.top = 'auto';
                                burnToast.style.bottom = burnToastPosition;
                            } else {
                                burnToast.style.top = burnToastPosition;
                            }

                            burnToast.style.scale = '100%';
                            burnToast.style.transform = 'translateY(0px)';

                            burnToast.firstElementChild.classList.remove('opacity-100');
                            burnToast.firstElementChild.classList.add('opacity-0');

                            let that = this;
                            // Burn 🔥 (remove) last toast
                            setTimeout(function() {
                                that.toasts.pop();
                            }, 300);

                            if (this.position.includes('bottom')) {
                                middleToast.style.top = 'auto';
                            }

                            return;
                        },
                        alignBottom(element1, element2) {
                            // Get the top position and height of the first element
                            let top1 = element1.offsetTop;
                            let height1 = element1.offsetHeight;

                            // Get the height of the second element
                            let height2 = element2.offsetHeight;

                            // Calculate the top position for the second element
                            let top2 = top1 + (height1 - height2);

                            // Apply the calculated top position to the second element
                            element2.style.top = top2 + 'px';
                        },
                        alignTop(element1, element2) {
                            // Get the top position of the first element
                            let top1 = element1.offsetTop;

                            // Apply the same top position to the second element
                            element2.style.top = top1 + 'px';
                        },
                        resetBottom() {
                            for (let i = 0; i < this.toasts.length; i++) {
                                if (document.getElementById(this.toasts[i].id)) {
                                    let toastElement = document.getElementById(this.toasts[i].id);
                                    toastElement.style.bottom = '0px';
                                }
                            }
                        },
                        resetTop() {
                            for (let i = 0; i < this.toasts.length; i++) {
                                if (document.getElementById(this.toasts[i].id)) {
                                    let toastElement = document.getElementById(this.toasts[i].id);
                                    toastElement.style.top = '0px';
                                }
                            }
                        },
                        getBottomPositionOfElement(el) {
                            return (el.getBoundingClientRect().height + el.getBoundingClientRect().top);
                        },
                        calculateHeightOfToastsContainer() {
                            if (this.toasts.length == 0) {
                                \$el.style.height = '0px';
                                return;
                            }

                            lastToast = this.toasts[this.toasts.length - 1];
                            lastToastRectangle = document.getElementById(lastToast.id).getBoundingClientRect();

                            firstToast = this.toasts[0];
                            firstToastRectangle = document.getElementById(firstToast.id).getBoundingClientRect();

                            if (this.toastsHovered) {
                                if (this.position.includes('bottom')) {
                                    \$el.style.height = ((firstToastRectangle.top + firstToastRectangle.height) - lastToastRectangle.top) + 'px';
                                } else {
                                    \$el.style.height = ((lastToastRectangle.top + lastToastRectangle.height) - firstToastRectangle.top) + 'px';
                                }
                            } else {
                                \$el.style.height = firstToastRectangle.height + 'px';
                            }
                        }
                    }"
                        @set-toasts-layout.window="
                        layout=event.detail.layout;
                        stackToasts();
                    "
                        @toast-show.window="
                        event.stopPropagation();
                        if(event.detail.position){
                            position = event.detail.position;
                        }
                        toasts.unshift({
                            id: 'toast-' + Math.random().toString(16).slice(2),
                            show: false,
                            message: event.detail.message,
                            description: event.detail.description,
                            type: event.detail.type,
                            html: event.detail.html
                        });
                    "
                        @mouseenter="toastsHovered=true;" @mouseleave="toastsHovered=false" x-init="stackToasts();"
                        class="fixed block w-full group z-[99] sm:max-w-xs"
                        :class="{ 'right-0 top-0 sm:mt-6 sm:mr-6': position=='top-right', 'left-0 top-0 sm:mt-6 sm:ml-6': position=='top-left', 'left-1/2 -translate-x-1/2 top-0 sm:mt-6': position=='top-center', 'right-0 bottom-0 sm:mr-6 sm:mb-6': position=='bottom-right', 'left-0 bottom-0 sm:ml-6 sm:mb-6': position=='bottom-left', 'left-1/2 -translate-x-1/2 bottom-0 sm:mb-6': position=='bottom-center' }"
                        x-cloak>

                        <template x-for="(toast, index) in toasts" :key="toast.id">
                            <li :id="toast.id" x-data="{
                                toastHovered: false
                            }" x-init="if (position.includes('bottom')) {
                                \$el.firstElementChild.classList.add('toast-bottom');
                                \$el.firstElementChild.classList.add('opacity-0', 'translate-y-full');
                            } else {
                                \$el.firstElementChild.classList.add('opacity-0', '-translate-y-full');
                            }
                            setTimeout(function() {

                                setTimeout(function() {
                                    if (position.includes('bottom')) {
                                        \$el.firstElementChild.classList.remove('opacity-0', 'translate-y-full');
                                    } else {
                                        \$el.firstElementChild.classList.remove('opacity-0', '-translate-y-full');
                                    }
                                    \$el.firstElementChild.classList.add('opacity-100', 'translate-y-0');

                                    setTimeout(function() {
                                        stackToasts();
                                    }, 10);
                                }, 5);
                            }, 50);

                            setTimeout(function() {
                                setTimeout(function() {
                                    \$el.firstElementChild.classList.remove('opacity-100');
                                    \$el.firstElementChild.classList.add('opacity-0');
                                    if (toasts.length == 1) {
                                        \$el.firstElementChild.classList.remove('translate-y-0');
                                        \$el.firstElementChild.classList.add('-translate-y-full');
                                    }
                                    setTimeout(function() {
                                        deleteToastWithId(toast.id)
                                    }, 300);
                                }, 5);
                            }, 4000);"
                                @mouseover="toastHovered=true" @mouseout="toastHovered=false"
                                class="absolute w-full duration-300 ease-out select-none sm:max-w-xs"
                                :class="{ 'toast-no-description': !toast.description }">
                                <span
                                    class="relative flex flex-col items-start shadow-[0_5px_15px_-3px_rgb(0_0_0_/_0.08)] w-full transition-all duration-300 ease-out bg-white border border-gray-100 sm:rounded-md sm:max-w-xs group"
                                    :class="{ 'p-4': !toast.html, 'p-0': toast.html }">
                                    <template x-if="!toast.html">
                                        <div class="relative">
                                            <div class="flex items-center"
                                                :class="{ 'text-green-500': toast.type=='success', 'text-blue-500': toast.type=='info', 'text-orange-400': toast.type=='warning', 'text-red-500': toast.type=='danger', 'text-gray-800': toast.type=='default' }">

                                                <svg x-show="toast.type=='success'" class="w-[18px] h-[18px] mr-1.5 -ml-1"
                                                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM16.7744 9.63269C17.1238 9.20501 17.0604 8.57503 16.6327 8.22559C16.2051 7.87615 15.5751 7.93957 15.2256 8.36725L10.6321 13.9892L8.65936 12.2524C8.24484 11.8874 7.61295 11.9276 7.248 12.3421C6.88304 12.7566 6.92322 13.3885 7.33774 13.7535L9.31046 15.4903C10.1612 16.2393 11.4637 16.1324 12.1808 15.2547L16.7744 9.63269Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                                <svg x-show="toast.type=='info'" class="w-[18px] h-[18px] mr-1.5 -ml-1"
                                                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12 9C12.5523 9 13 8.55228 13 8C13 7.44772 12.5523 7 12 7C11.4477 7 11 7.44772 11 8C11 8.55228 11.4477 9 12 9ZM13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12V16C11 16.5523 11.4477 17 12 17C12.5523 17 13 16.5523 13 16V12Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                                <svg x-show="toast.type=='warning'" class="w-[18px] h-[18px] mr-1.5 -ml-1"
                                                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M9.44829 4.46472C10.5836 2.51208 13.4105 2.51168 14.5464 4.46401L21.5988 16.5855C22.7423 18.5509 21.3145 21 19.05 21L4.94967 21C2.68547 21 1.25762 18.5516 2.4004 16.5862L9.44829 4.46472ZM11.9995 8C12.5518 8 12.9995 8.44772 12.9995 9V13C12.9995 13.5523 12.5518 14 11.9995 14C11.4473 14 10.9995 13.5523 10.9995 13V9C10.9995 8.44772 11.4473 8 11.9995 8ZM12.0009 15.99C11.4486 15.9892 11.0003 16.4363 10.9995 16.9886L10.9995 16.9986C10.9987 17.5509 11.4458 17.9992 11.9981 18C12.5504 18.0008 12.9987 17.5537 12.9995 17.0014L12.9995 16.9914C13.0003 16.4391 12.5532 15.9908 12.0009 15.99Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                                <svg x-show="toast.type=='danger'" class="w-[18px] h-[18px] mr-1.5 -ml-1"
                                                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9996 7C12.5519 7 12.9996 7.44772 12.9996 8V12C12.9996 12.5523 12.5519 13 11.9996 13C11.4474 13 10.9996 12.5523 10.9996 12V8C10.9996 7.44772 11.4474 7 11.9996 7ZM12.001 14.99C11.4488 14.9892 11.0004 15.4363 10.9997 15.9886L10.9996 15.9986C10.9989 16.5509 11.446 16.9992 11.9982 17C12.5505 17.0008 12.9989 16.5537 12.9996 16.0014L12.9996 15.9914C13.0004 15.4391 12.5533 14.9908 12.001 14.99Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                                <p class="text-[13px] font-medium leading-none text-gray-800"
                                                    x-text="toast.message"></p>
                                            </div>
                                            <p x-show="toast.description" :class="{ 'pl-5': toast.type!='default' }"
                                                class="text-gray-600 mt-1.5 text-xs leading-none opacity-70" x-text="toast.description"></p>
                                        </div>
                                    </template>
                                    <template x-if="toast.html">
                                        <div x-html="toast.html"></div>
                                    </template>
                                    <span @click="burnToast(toast.id)"
                                        class="absolute right-0 p-1.5 mr-2.5 text-gray-400 duration-100 ease-in-out rounded-full opacity-0 cursor-pointer hover:bg-gray-50 hover:text-gray-500"
                                        :class="{
                                            'top-1/2 -translate-y-1/2': !toast.description && !toast.html,
                                            'top-0 mt-2.5': (toast
                                                .description || toast.html),
                                            'opacity-100': toastHovered,
                                            'opacity-0': !
                                                toastHovered
                                        }">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                </span>
                            </li>
                        </template>
                    </ul>
                </template>
            </div>

            <script>
                const toast = function(options = {}) {
                    let title = 'Default Toast Notification';
                    let description = '';
                    let type = 'default';
                    let position = 'top-right';
                    let html = '';

                    if (typeof options.title != 'undefined') title = options.title;
                    if (typeof options.description != 'undefined') description = options.description;
                    if (typeof options.type != 'undefined') type = options.type;
                    if (typeof options.position != 'undefined') position = options.position;
                    if (typeof options.html != 'undefined') html = options.html;

                    window.dispatchEvent(new CustomEvent('toast-show', {
                        detail: {
                            type: type,
                            message: title,
                            description: description,
                            position: position,
                            html: html
                        }
                    }));
                };

                <?php if(\$_toastFlashMessage = flash()->display('leaf.toast')) : ?>
                    setTimeout(() => {
                        toast(<?php echo json_encode(\$_toastFlashMessage); ?>);
                    }, 10);
                <?php endif; ?>
            </script>
HTML;
        });
    }

    protected function setupContainer(array $viewPaths, string $cachePath)
    {
        $this->container->bindIf('files', function () {
            return new Filesystem;
        }, true);

        $this->container->bindIf('events', function () {
            return new Dispatcher;
        }, true);

        $this->container->bindIf('config', function () use ($viewPaths, $cachePath) {
            return new Repository([
                'view.paths' => $viewPaths,
                'view.compiled' => $cachePath,
            ]);
        }, true);

        $this->container->bindIf('blade.compiler', function ($app) use ($cachePath) {
            return new BladeCompiler($app['files'], $cachePath);
        });

        Container::setInstance($this->container);
        Facade::setFacadeApplication($this->container);
    }
}
