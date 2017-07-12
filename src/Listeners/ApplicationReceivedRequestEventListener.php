<?php

namespace Railroad\Intercomeo\Listeners;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use Railroad\Intercomeo\Services\LatestActivityService;

class ApplicationReceivedRequestEventListener
{
    /**
     * @var IntercomUsersRepository
     */
    private $intercomUsersRepository;

    /**
     * @var LatestActivityService
     */
    private $latestActivityService;

    public function __construct(
        IntercomUsersRepository $intercomUsersRepository,
        LatestActivityService $latestActivityService
    )
    {
        $this->intercomUsersRepository = $intercomUsersRepository;
        $this->latestActivityService = $latestActivityService;
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

        $time = $this->latestActivityService->calculateTimeToStoreCarbon($utcTimestamp);

        if($lastRequestAt->lt($time)){
            $this->latestActivityService->store($userId, $time->timestamp);
        }
    }
}
