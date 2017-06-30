<?php

namespace Railroad\Intercomeo\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Events\AddMember;
use Railroad\Intercomeo\Listeners\AddMemberEventListener;

class IntercomeoServiceProvider extends ServiceProvider
{
    protected $intercomAppId;
    protected $intercomAccessToken;

    protected $listen = [
        AddMember::class => [AddMemberEventListener::class . '@handle']
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        $destination = __DIR__ . '/../../../../../laravel/config';
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
        $this->app->singleton( 'Intercom\IntercomClient', function ($app){
            return new IntercomClient(env('INTERCOM_ACCESS_TOKEN'), null);
        } );
    }
}