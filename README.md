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


