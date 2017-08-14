
Intercom Integration
====================

Installation
------------

1. Copy secrets to .env from secure note. The "access token" is key. "App ID" and "HMAC Secret" are optional. 
1. Add IntercomeoServiceProvider to list of "providers" in `config/app.php`.
1. Add to composer.json "require".
1. In `laravel` directory, run the following:
    1. `composer install`
    1. `php artisan migrate`
    1. `php artisan vendor:publish`
1. Integrate into your application by firing and calling `Intercomeo` events and methods (respectively) as needed.


Note about the term "user_id"
-----------------------------

***Note**: that below the term "user_id" refers to your user's id *in your application* (**not** their Intercom id). We will likely only ever use this "user_id" and very likely have no use for the intercom id.* 


Overview of Functionality
-------------------------


### Add user to Intercom

Fire `Railroad\Intercomeo\Events\MemberAdded` event, passing in user-id, email, and (optionally) tags for that user.


### Storing When a User Was Last Active

On every request, trigger a *ApplicationReceivedRequest* event, passing the necessary parameters as detailed below.

This will evaluate whether the user's activity for the current "time block" as already been recorded and interact with Intercom accordingly.

[Railtracker](http://github.com/railroadmedia/railtracker) is very useful here, but you can use whatever you want.

#### Required parameters

1. user_id
2. email
3. request time (timestamp, UTC)
4. previous request (timestamp, UTC)


#### Details

Note that all all time is handled in UTC timestamps (Unix Time).

Intercom's user model has a "last_request_at" property ([reference](
https://developers.intercom.com/v2.0/reference#user-model)). If we were to set this with every request, it would an inefficient use of our API rate-limits though. So, we'll decide a "buffer time amount", and save the last_request_time with that amount as an acceptable amount of inaccuracy.

*The default "buffer time amount" is set and can be overridden in the config.*


Frontend
------------------

Follow instructions provided by Intercom (https://developers.intercom.com/docs/basic-javascript).


Testing
-------

### Environmental Variables

To get environmental variables for running your tests, add them to the package's *phpunit.xml* like this:

```xml
<?xml version="1.0" encoding="UTF-8"?>
    <!-- ... there would likely be things here... -->
    <php>
        <!-- ... there would likely be things here... -->
        <env name="INTERCOM_APP_ID" value="xxxxxx"/>
        <env name="INTERCOM_HMAC_SECRET" value="xxxxxx"/>
        <env name="INTERCOM_ACCESS_TOKEN" value="xxxxxx"/>
    </php>
</phpunit>
```

***BE VERY CAREFUL NOT TO COMMIT THIS IF YOU'VE ADDED SECRETS*** 


### API Secrets for Integration-Testing

Set in *\Railroad\Intercomeo\Tests\**TestCase::getEnvironmentSetUp*** like this:

```php
$app['config']->set('intercomeo.access_token', env('INTERCOM_ACCESS_TOKEN'));