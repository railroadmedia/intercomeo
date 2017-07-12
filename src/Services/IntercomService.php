<?php

namespace Railroad\Intercomeo\Services;

use Intercom\IntercomClient;

class IntercomService
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;

    public function __construct(IntercomClient $intercomClient)
    {
        $this->intercomClient = $intercomClient;
    }

    public function getUser($userId)
    {
        return $this->intercomClient->users->getUsers(['user_id' => $userId]);
    }

    /**
     * @param \stdClass $user // pass in the result of the `getUser` method above
     * @return integer
     */
    public function getLastRequestAt(\stdClass $user)
    {
        return (integer) $user->last_request_at;
    }
}