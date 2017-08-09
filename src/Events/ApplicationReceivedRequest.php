<?php

namespace Railroad\Intercomeo\Events;

class ApplicationReceivedRequest
{
    public $userId;
    public $email;
    public $utcTimestamp;
    public $previousRequestTimestamp;

    public function __construct($userId, $email, $utcTimestamp, $previousRequestTimestamp)
    {
        $this->userId = $userId;
        $this->email= $email;
        $this->utcTimestamp = $utcTimestamp;
        $this->previousRequestTimestamp = $previousRequestTimestamp;
    }
}