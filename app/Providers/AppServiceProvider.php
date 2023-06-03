<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TaskManifest::class, function () {
            return new TaskManifest(Env::get('COMPOSER_VENDOR_DIR') ?: getcwd() . '/vendor');
        });

        $this->app->singleton(Configuration::class, ConfigurationRepository::class);
    }
}
