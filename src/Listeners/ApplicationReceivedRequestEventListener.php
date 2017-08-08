<?php

namespace Railroad\Intercomeo\Listeners;

use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Services\IntercomeoService;

class ApplicationReceivedRequestEventListener
{
    private $intercomeoService;

    public function __construct(
        IntercomeoService $intercomeoService
    )
    {
        $this->intercomeoService = $intercomeoService;
    }

    public function handle(ApplicationReceivedRequest $applicationReceivedRequest)
    {
        $userId = $applicationReceivedRequest->userId;
        $email = $applicationReceivedRequest->email;
        $utcTimestamp = $applicationReceivedRequest->utcTimestamp;

        if(is_null($utcTimestamp)){
            $utcTimestamp = time();
        }

        $user = $this->intercomeoService->getUserCreateIfDoesNotYetExist($userId, $email);

        $newTime = $this->intercomeoService->calculateLatestActivityTimeToStore($utcTimestamp);

        // todo: remove & replace given that we're getting rid of DB usage (170808) - PICK UP HERE WEDNESDAY (?)
        // todo: remove & replace given that we're getting rid of DB usage (170808) - PICK UP HERE WEDNESDAY (?)
        // todo: remove & replace given that we're getting rid of DB usage (170808) - PICK UP HERE WEDNESDAY (?)
        // $stored = $this->intercomUsersRepository->getLastRequestAt($userId);

        if($newTime > $stored){
            $this->intercomeoService->storeLatestActivity($user, $newTime);
        }
    }
}
