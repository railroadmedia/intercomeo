<?php

namespace Railroad\Intercomeo\Listeners;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use Railroad\Intercomeo\Services\UserService;

class ApplicationReceivedRequestEventListener
{
    /**
     * @var IntercomUsersRepository
     */
    private $intercomUsersRepository;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        IntercomUsersRepository $intercomUsersRepository,
        UserService $userService
    )
    {
        $this->intercomUsersRepository = $intercomUsersRepository;
        $this->userService = $userService;
    }

    public function handle(ApplicationReceivedRequest $applicationReceivedRequest)
    {
        /*
         * rename from "buffer"?
         * rename from "buffer"?
         * rename from "buffer"?
         *
         * maybe "selected acceptable inaccuracy?"
         */

        $userId = $applicationReceivedRequest->userId;
        $utcTimestamp = $applicationReceivedRequest->utcTimestamp;

        $lastRequestAt = Carbon::createFromTimestampUTC($this->intercomUsersRepository->getLastRequestAt($userId));

        if(is_null($utcTimestamp)){
            $utcTimestamp = time();
        }

        $time = $this->userService->calculateLatestActivityTimeToStoreCarbon($utcTimestamp);

        if($lastRequestAt->lt($time)){
            $this->userService->storeLatestActivity($userId, $time->timestamp);
        }
    }
}
