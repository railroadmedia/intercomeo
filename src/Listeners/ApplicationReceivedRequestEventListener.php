<?php

namespace Railroad\Intercomeo\Listeners;

use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Services\IntercomeoService;

class ApplicationReceivedRequestEventListener
{
    private $intercomeoService;

    public function __construct(IntercomeoService $intercomeoService)
    {
        $this->intercomeoService = $intercomeoService;
    }

    public function handle(ApplicationReceivedRequest $applicationReceivedRequest)
    {
        if (config('intercomeo.only_track_last_request_at_for_users_already_in_intercom')) {
            $this->intercomeoService->lastRequestAtUpdateEvaluationAndAction(
                $this->intercomeoService->getUser($applicationReceivedRequest->userId),
                $applicationReceivedRequest->previousRequestTimestamp,
                $applicationReceivedRequest->utcTimestamp
            );

            return true;
        }

        $this->intercomeoService->lastRequestAtUpdateEvaluationAndAction(
            $this->intercomeoService->getUserCreateIfDoesNotYetExist(
                $applicationReceivedRequest->userId,
                $applicationReceivedRequest->email
            ),
            $applicationReceivedRequest->previousRequestTimestamp,
            $applicationReceivedRequest->utcTimestamp
        );

        return true;
    }
}
