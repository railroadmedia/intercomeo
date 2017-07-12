<?php

namespace Railroad\Intercomeo\Services;

use Intercom\IntercomClient;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;

class LatestActivityService
{
    private $intercomClient;
    private $usersRepository;

    public function __construct(
        IntercomClient $intercomClient,
        IntercomUsersRepository $usersRepository
    )
    {
        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $this->intercomClient = $intercomClient;
        $this->usersRepository = $usersRepository;
    }

    /**
     * @param integer|string $userId
     * @param int $utcTimestamp
     * @return bool
     */
    public function store($userId, $utcTimestamp = null)
    {
        $utcTimestamp = $utcTimestamp ?? time();

        $this->intercomClient->users->create([
            'user_id' => $userId,
            'last_request_at' => $utcTimestamp ? $utcTimestamp : time()
        ]);

        $this->usersRepository->store($userId, $utcTimestamp);

        return true;
    }
}