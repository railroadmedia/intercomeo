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
        $this->intercomeoService->lastRequestAtUpdateEvaluationAndAction(
            $applicationReceivedRequest->userId,
            $applicationReceivedRequest->email,
            $applicationReceivedRequest->utcTimestamp,
            $applicationReceivedRequest->previousRequestTimestamp
        );
    }
}
