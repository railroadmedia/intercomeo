<?php

namespace Railroad\Intercomeo\Events;

class ApplicationReceivedRequest
{
    public $userId;
    public $email;
    public $previousRequestTimestamp;
    public $utcTimestamp;

    public function __construct($userId, $email, $previousRequestTimestamp, $utcTimestamp)
    {
        $this->userId = $userId;
        $this->email= $email;
        $this->previousRequestTimestamp = $previousRequestTimestamp;
        $this->utcTimestamp = $utcTimestamp;
    }
}