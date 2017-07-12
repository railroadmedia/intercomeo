<?php

namespace Railroad\Intercomeo\Listeners;

use Railroad\Intercomeo\Events\ApplicationReceivedRequest;

class ApplicationReceivedRequestEventListener
{
    public function __construct()
    {

    }

    public function handle(ApplicationReceivedRequest $applicationReceivedRequest)
    {
        $userId = $applicationReceivedRequest->userId;
        $utcTimestamp = $applicationReceivedRequest->utcTimestamp;


        $winning = !is_null($userId);
    }
}