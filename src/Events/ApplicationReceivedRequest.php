<?php

namespace Railroad\Intercomeo\Events;

class ApplicationReceivedRequest
{
    public $userId;
    public $email;
    public $requestUtcTimestamp;
    public $previousRequestUtcTimestamp;

    public function __construct($userId, $email, $requestUtcTimestamp, $previousRequestUtcTimestamp)
    {
        $this->userId = $userId;
        $this->email= $email;
        $this->requestUtcTimestamp = $requestUtcTimestamp;
        $this->previousRequestUtcTimestamp = $previousRequestUtcTimestamp;
    }
}