<?php

use Railroad\Intercomeo\Services\LatestActivityService;

return [
    'tables' => [
        'intercom_users' => 'intercom_users',
    ],
    'access_token' => 'YOUR ACCESS TOKEN HERE',
    'last_request_buffer_amount' => 1,
    'last_request_buffer_unit' => LatestActivityService::$timeUnits['hour'],
    'level_to_round_down_to' => LatestActivityService::$timeUnits['hour']
];
