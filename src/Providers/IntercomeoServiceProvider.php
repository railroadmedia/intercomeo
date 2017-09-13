<?php

namespace Railroad\Intercomeo\Providers;

use Illuminate\Contracts\Events\Dispatcher;
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
     * @param Dispatcher $events
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        parent::boot($events);

        $destination = __DIR__ . '/../../../../../config';
        $origin = __DIR__ . '/../../config';

        $this->publishes([$origin => $destination]);
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