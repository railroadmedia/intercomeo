<?php

namespace Railroad\Intercomeo\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Events\MemberAdded;
use Railroad\Intercomeo\Listeners\MemberAddedEventListener;

class IntercomeoServiceProvider extends ServiceProvider
{
    protected $intercomAppId;
    protected $intercomAccessToken;

    protected $listen = [
        MemberAdded::class => [MemberAddedEventListener::class . '@handle']
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton( 'Intercom\IntercomClient', function ($app){
            return new IntercomClient(env('INTERCOM_ACCESS_TOKEN'), null);
        } );
    }
}