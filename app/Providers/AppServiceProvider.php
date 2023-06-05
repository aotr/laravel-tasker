<?php

namespace App\Providers;

use App\Support\TaskManifest;
use Illuminate\Support\Env;
use Illuminate\Support\ServiceProvider;
use App\Facades\Configuration;
use App\Support\ConfigurationRepository;

class AppServiceProvider extends ServiceProvider
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
