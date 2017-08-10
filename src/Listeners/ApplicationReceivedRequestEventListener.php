<?php

namespace Railroad\Intercomeo\Listeners;

use Illuminate\Support\Facades\Log;
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
        $user = null;

        try {
            $user = $this->intercomeoService->getUserCreateIfDoesNotYetExist(
                $applicationReceivedRequest->userId,
                $applicationReceivedRequest->email
            );
        } catch (\Exception $exception) {
            Log::error(
                'user_id: ' .
                $applicationReceivedRequest->userId .
                ' was not successfully processed by ' .
                '"\Railroad\Intercomeo\Listeners\ApplicationReceivedRequestEventListener::handle". ' .
                'With error ' .
                print_r($exception, true)
            );
        }

        try {
            $this->intercomeoService->lastRequestAtUpdateEvaluationAndAction(
                $user,
                $applicationReceivedRequest->previousRequestTimestamp,
                $applicationReceivedRequest->utcTimestamp
            );
        } catch (\Exception $exception) {
            Log::error(
                'user_id: ' .
                $applicationReceivedRequest->userId .
                ' was not successfully processed by ' .
                '"\Railroad\Intercomeo\Listeners\ApplicationReceivedRequestEventListener::handle". ' .
                'With error ' .
                print_r($exception, true)
            );
        }

        return true;
    }
}
