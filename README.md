# Intercom Integration

**Under Construction**

## Installation

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

## Add a user to Intercom

Trigger a `Railroad\Intercomeo\Events\MemberAdded` event, passing the user's email and ID (*in your application* - Intercom will store this as the `user_id`). The third parameter is optional - you can also pass in an array of strings that will be set as tags.

## Update a user's `last_request_at` attribute

Do this by calling a `UpdateLatestActivity@send`. You'll need to inject it (`Railroad\Intercomeo\Services\UpdateLatestActivity`) where needed.

Only one parameter is required. Either the user's email address, or their ID *in your application* (**not** their Intercom id). The ID must be an integer.

A second parameter is available if you want to explicitly specify a time to set (rather than have the script just grab a timestamp when it runs - potentially relevant if you have a busy queue with this in the "low priority" pile?).

## Question, Ruminations, Ponderings

Instead of adding IntercomeoServiceProvider to list of "providers" in `larevel/config/app.php`, can we add it the package somewhere?