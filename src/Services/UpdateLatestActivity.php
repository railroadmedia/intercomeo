<?php

namespace Railroad\Intercomeo\Services;

use Intercom\IntercomClient;

class UpdateLatestActivity
{
    private $intercomClient;

    public function __construct(IntercomClient $intercomClient)
    {
        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $this->intercomClient = $intercomClient;
    }

    /**
     * @param integer|string $userId
     * @param int $utcTimestamp
     *
     * If the second param is specified it must be a Unix timestamp for *the UTC time*.
     */
    public function store($userId, $utcTimestamp = null)
    {
        $this->intercomClient->users->create([
            'user_id' => $userId,
            'last_request_at' => $utcTimestamp ? $utcTimestamp : time()
        ]);
    }
}