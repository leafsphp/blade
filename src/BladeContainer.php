<?php

namespace Leaf;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

class BladeContainer extends Container implements Application
{
    /**
     * @var callable[]
     */
    protected $terminatingCallbacks = [];

    /**
     * @param  callable|string  $callback
     *
     * @return $this
     */
    public function terminating($callback)
    {
        $this->terminatingCallbacks[] = $callback;

        return $this;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function version()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function basePath($path = '')
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function bootstrapPath($path = '')
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function configPath($path = '')
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function databasePath($path = '')
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function langPath($path = '')
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function publicPath($path = '')
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function resourcePath($path = '')
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @throws \Exception
     */
    public function storagePath($path = '')
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param ...$environments
     *
     * @return bool|string
     *
     * @throws \Exception
     */
    public function environment(...$environments)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function runningInConsole()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function runningUnitTests()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function hasDebugModeEnabled()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return \Illuminate\Contracts\Foundation\MaintenanceMode
     *
     * @throws \Exception
     */
    public function maintenanceMode()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function isDownForMaintenance()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function registerConfiguredProviders()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $provider
     * @param $force
     *
     * @return \Illuminate\Support\ServiceProvider
     *
     * @throws \Exception
     */
    public function register($provider, $force = false)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $provider
     * @param $service
     *
     * @return void
     *
     * @throws \Exception
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $provider
     *
     * @return \Illuminate\Support\ServiceProvider
     *
     * @throws \Exception
     */
    public function resolveProvider($provider)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function boot()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $callback
     *
     * @return void
     *
     * @throws \Exception
     */
    public function booting($callback)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $callback
     *
     * @return void
     *
     * @throws \Exception
     */
    public function booted($callback)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param array $bootstrappers
     *
     * @return void
     *
     * @throws \Exception
     */
    public function bootstrapWith(array $bootstrappers)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getLocale()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getNamespace()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $provider
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getProviders($provider)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function hasBeenBootstrapped()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function loadDeferredProviders()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @param $locale
     *
     * @return void
     *
     * @throws \Exception
     */
    public function setLocale($locale)
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return bool
     *
     * @throws \Exception
     */
    public function shouldSkipMiddleware()
    {
        throw new \Exception("Not implemented");
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function terminate()
    {
        throw new \Exception("Not implemented");
    }
}
