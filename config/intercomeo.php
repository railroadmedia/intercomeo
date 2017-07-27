<?php

use Railroad\Intercomeo\Services\IntercomeoService;

return [
    'tables' => [
        'intercom_users' => 'intercom_users',
    ],
    'app_id' => 'YOUR APP ID HERE',
    'hmac_secret' => 'YOUR HMAC SECRET HERE',
    'access_token' => 'YOUR ACCESS TOKEN HERE',
    'last_request_buffer_amount' => 1,
    'last_request_buffer_unit' => IntercomeoService::$timeUnits['hour'],
    'level_to_round_down_to' => IntercomeoService::$timeUnits['hour']
];
