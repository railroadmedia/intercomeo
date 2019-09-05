<?php

namespace Railroad\Intercomeo\Providers;

use Illuminate\Support\ServiceProvider;
use Intercom\IntercomClient;

class IntercomeoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/../../config' => __DIR__.'/../../../../../config']);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'Intercom\IntercomClient',
            function ($app) {
                return new IntercomClient(config('intercomeo.access_token'), null);
            }
        );
    }
}