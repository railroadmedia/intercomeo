<?php

namespace Railroad\Intercomeo\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Events\UserCreated;
use Railroad\Intercomeo\Listeners\ApplicationReceivedRequestEventListener;
use Railroad\Intercomeo\Listeners\UserCreatedEventListener;

class IntercomeoServiceProvider extends ServiceProvider
{
    protected $intercomAppId;
    protected $intercomAccessToken;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $destination = __DIR__ . '/../../../../../config';
        $origin = __DIR__ . '/../../config';
        
        $this->publishes([$origin => $destination]);
        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
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