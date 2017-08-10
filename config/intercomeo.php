<?php

use Railroad\Intercomeo\Services\IntercomeoService;

return [
    'app_id' => 'YOUR APP ID HERE',
    'hmac_secret' => 'YOUR HMAC SECRET HERE',
    'access_token' => 'YOUR ACCESS TOKEN HERE',
    'last_request_buffer_amount' => 1,
    'last_request_buffer_unit' => IntercomeoService::$timeUnits['hour'],
    'level_to_round_down_to' => IntercomeoService::$timeUnits['hour'],
    'only_track_last_request_at_for_users_already_in_intercom' => true
];
