<?php

namespace Railroad\Intercomeo\Events;

class MemberAdded
{
    public $userId;
    public $email;
    public $tags;

    /**
     * MemberAdded constructor.
     * @param integer $userId
     * @param string $email
     * @param array $tags
     */
    public function __construct($userId, $email, $tags = [])
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->tags = $tags;
    }
}