<?php

namespace Railroad\Intercomeo\Listeners;

use Railroad\Intercomeo\Events\UserCreated;
use Railroad\Intercomeo\Services\IntercomeoService;

class UserCreatedEventListener
{
    private $intercomeoService;

    public function __construct(IntercomeoService $intercomeoService)
    {
        $this->intercomeoService = $intercomeoService;
    }

    public function handle(UserCreated $event)
    {
        $user = null;

        $userId = $event->userId;
        $email = $event->email;
        $tags = $event->tags;

        try{
            $user = $this->intercomeoService->storeUser($userId, $email);
        }catch(\Exception $exception){
            Log::error(
                    '"\Railroad\Intercomeo\Listeners\UserCreatedEventListener::handle"' .
                    ' call of ' .
                    '"\Railroad\Intercomeo\Services\IntercomeoService::storeUser"' .
                    ' failed with exception: ' .
                    var_export($exception, true)
            );
        }

        foreach ($tags as $tag) {
            try{
                $this->intercomeoService->tagUsers($user, $tag);
            }catch(\Exception $exception){
                Log::error(
                    '"\Railroad\Intercomeo\Listeners\UserCreatedEventListener::handle"' .
                    ' call of ' .
                    '"\Railroad\Intercomeo\Services\IntercomeoService::tagUsers"' .
                    ' failed with exception: ' .
                    var_export($exception, true)
                );
            }
        }

        return true;
    }
}
