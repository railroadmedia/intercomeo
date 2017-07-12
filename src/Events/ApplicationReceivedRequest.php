<?php

namespace Railroad\Intercomeo\Events;

class ApplicationReceivedRequest
{
    public $userId;
    public $utcTimestamp;

    public function __construct($userId, $utcTimestamp = null)
    {
        $this->userId = $userId;
        $this->utcTimestamp = $utcTimestamp;
    }
}