
Intercom Integration
====================

**Under Construction**


Installation
------------

1. Copy secrets to .env from secure note. The "access token" is key. "App ID" and "HMAC Secret" are optional. 
1. ~~Add IntercomeoServiceProvider to list of "providers" in `config/app.php`.~~ **(but maybe not because it breaks *composer install*...?)** 
1. Add to composer.json "require". (**No releases yet... see "Development Installation Note" below**)
1. In `laravel` directory, run the following:
    1. `composer install`
    1. `php artisan migrate`
    1. `php artisan vendor:publish`
1. Run `composer install` in `railroad/intercomeo` directory.
1. Integrate into your application by firing and calling `Intercomeo` events and methods (respectively) as needed.

### Development Installation Note

Composer.json details for your application required for 'Add to composer.json "require"' step above: 

```
"repositories": [
    {
        "type": "path",
        "url": "../packages/railroad/intercomeo"
    }
],

"require": {
    "railroad/intercomeo": "dev-intercomeo"
}
```

Add a user to Intercom
----------------------

Trigger a `Railroad\Intercomeo\Events\MemberAdded` event, passing the user's email and ID (*in your application* - Intercom will store this as the `user_id`). The third parameter is optional - you can also pass in an array of strings that will be set as tags.


Update a user's `last_request_at` attribute
-------------------------------------------

Do this by calling a `LatestActivityService@store`. You'll need to inject it (`Railroad\Intercomeo\Services\UpdateLatestActivity`) where needed.

Only one parameter is required. Either the user's email address, or their ID *in your application* (**not** their Intercom id). The ID must be an integer.

A second parameter is available if you want to explicitly specify a time to set (rather than have the script just grab a timestamp when it runs - potentially relevant if you have a busy queue with this in the "low priority" pile?).


Overview of Functionality
-------------------------

### Add user to Intercom

Fire `Railroad\Intercomeo\Events\MemberAdded` event, passing in user-id, email, and (optionally) tags for that user.

### Storing When a User Was Last Active

Set `last_request_at` user attribute pass - the user-id and the service will use the current time to set the last_request_at attribute. 

Or you can pass in a specific time (in the form of a UTC timestamp (read: seconds since Unix Epoch)). This might be useful if queuing these jobs and you expect a considerable delay between calling service method and actually storing value.

#### Details

Intercom's user model has a "last_request_at" property ([reference](
https://developers.intercom.com/v2.0/reference#user-model)). If we were to set this with every request, it would an inefficient use of our API rate-limits though. So, we'll decide a "buffer time amount", and save the last_request_time with that amount as an acceptable amount of inaccuracy.

*You can change this from the default value (of one hour) in the config.*

Say for example we decide on one hour as the "buffer time amount" (BTA)...

The user visits a page. A RailTracker request Event is fired. This triggers an event-listener in the application, 

I'm not sure if we'll do this following in the application or in the Intercomeo package, but anyways this is perhaps how it'll work:

(Note that all All time is handled in UTC)

Store these values:

* the request time (pass in from request, because if we queue this we can't use "now")
* Current time 
* for buffer-time-amount of one-hour:
    * Current time rounded up to next hour-on-the-hour
    * Current time rounded down to previous hour-on-the-hour
    * hour-on-the-hour one back from the rounded-down one.
    
So, if the user makes a request at 1:37pm (Pacific Time Zone) on Monday July 10th, that would be 20:13 UTC. The 

* for buffer-time-amount of one-hour...
    * Current time rounded up to next hour-on-the-hour -------- 21:00
    * Current time rounded down to previous hour-on-the-hour -- 20:00 
    * hour-on-the-hour one back from the rounded-down one ----- 19:00

From this we can then create two "time blocks":
1. 19:00 → 20:00 (this is the "previous time block")
2. 20:00 → 21:00 (this is the one that "we are in" — as the request time is 20:13)

Then pull from the ~~Railtracker DB~~ **Intercomeo DB** the "last_request_at" for the user_id. If this value is within the current timeblock, do nothing. If that value not in the current timeblock — with because null or an earlier time — then save request time (rounded down to the hour — 20:00 for example), and persist that to our local Intecomeo DB on the successful Intercom API request.


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


### API Secrets for Integration-Testing

Set in *\Railroad\Intercomeo\Tests\**TestCase::getEnvironmentSetUp*** like this:

```php
$app['config']->set('intercomeo.access_token', env('INTERCOM_ACCESS_TOKEN'));
```

Don't commit them - remove that file from version control if need be.


Questions, Ruminations, Ponderings
---------------------------------

Instead of adding IntercomeoServiceProvider to list of "providers" in `larevel/config/app.php`, can we add it the package somewhere?
