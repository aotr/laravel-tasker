<?php

namespace Aotr\Tasker\Providers;

use Aotr\Tasker\Support\TaskManifest;
use Illuminate\Support\Env;
use Illuminate\Support\ServiceProvider;
use Aotr\Tasker\Facades\Configuration;
use Aotr\Tasker\Support\ConfigurationRepository;

class TaskerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Code to execute during application bootstrapping
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TaskManifest::class, function ($app) {
            return new TaskManifest(Env::get('COMPOSER_VENDOR_DIR') ?: getcwd() . '/vendor');
        });

        $this->app->singleton(Configuration::class, ConfigurationRepository::class);
    }
}
