# Intercom Integration

**Under Construction**

## Installation

1. Copy secrets to .env from secure note. The "access token" is key. "App ID" and "HMAC Secret" are optional. 
1. Add to list of "providers" in `config/app.php` (Ideally defined in project - update things as you see fit!)
1. Add to composer.json "require".
1. In `laravel` directory, run the following:
    1. `composer install`
    1. `php artisan migrate`
    1. `php artisan vendor:publish`
1. Run `composer install` in `railroad/intercomeo` directory.
2. Integrate into your application by firing `Intercomeo` events as needed.

## Add a user to Intercom

Trigger a `Railroad\Intercomeo\Events\MemberAdded` event, passing the user's email and ID (*in your application* - Intercom will store this as the `user_id`). The third parameter is optional - you can also pass in an array of strings that will be set as tags.

## Update a user's `last_request_at` attribute

Do this by calling a `UpdateLatestActivity@send`. You'll need to inject it (`Railroad\Intercomeo\Services\UpdateLatestActivity`) where needed.

Only one parameter is required. Either the user's email address, or their ID *in your application* (**not** their Intercom id). The ID must be an integer.

A second parameter is available if you want to explicitly specify a time to set (rather than have the script just grab a timestamp when it runs - potentially relevant if you have a busy queue with this in the "low priority" pile?).

