<?php

namespace Railroad\Intercomeo\Listeners;

use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use Railroad\Intercomeo\Services\IntercomeoService;

class ApplicationReceivedRequestEventListener
{
    private $intercomUsersRepository;
    private $intercomeoService;

    public function __construct(
        IntercomUsersRepository $intercomUsersRepository,
        IntercomeoService $intercomeoService
    )
    {
        $this->intercomUsersRepository = $intercomUsersRepository;
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

        if(!$this->intercomeoService->doesUserExistInIntercomAlready($userId)){
            $user = $this->intercomeoService->storeUser($userId, $email);
        }else{
            $user = $this->intercomeoService->getUser($userId);
        };

        $newTime = $this->intercomeoService->calculateLatestActivityTimeToStore($utcTimestamp);
        $stored = $this->intercomUsersRepository->getLastRequestAt($userId);

        if($newTime > $stored){
            $this->intercomeoService->storeLatestActivity($user, $newTime);
        }
    }
}
