<?php

namespace Railroad\Intercomeo\Events;

class ApplicationReceivedRequest
{
    public $userId;
    public $utcTimestamp;
    public $email;

    public function __construct($userId, $email, $utcTimestamp = null)
    {
        $this->userId = $userId;
        $this->email= $email;
        $this->utcTimestamp = $utcTimestamp;
    }
}