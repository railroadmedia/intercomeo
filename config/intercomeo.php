<?php

/*
 * This should return an array.
 */

return [
    'tables' => [
        'intercomeo_users' => 'intercomeo_users',
    ],
    'intercomAppId' => getenv('INTERCOM_APP_ID'),
    'intercomAccessToken' => getenv('INTERCOM_ACCESS_TOKEN')
];