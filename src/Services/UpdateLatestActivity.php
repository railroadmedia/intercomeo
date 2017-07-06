<?php

namespace Railroad\Intercomeo\Services;

class UpdateLatestActivity
{
    private $intercomClient;


    public function __construct()
    {
        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $intercomClient = resolve('Intercom\IntercomClient');

        $this->intercomClient = $intercomClient;
    }

    /**
     * @param integer|string $emailOrUserId
     * @param int $utcTimestamp
     *
     * The first param must either be a **string** that is the email, or an **integer** that is the user_id
     *
     * If the second param is specified it must be a Unix timestamp for the UTC time.
     */
    public function store($emailOrUserId, $utcTimestamp = null)
    {
        $this->intercomClient->users->create([
            is_integer($emailOrUserId) ? 'user_id' : 'email' => $emailOrUserId,
            'last_request_at' => $utcTimestamp ? $utcTimestamp : time()
        ]);
    }
}