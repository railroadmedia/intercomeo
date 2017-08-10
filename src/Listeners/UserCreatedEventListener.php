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
        $userId = $event->userId;
        $email = $event->email;
        $tags = $event->tags;

        $user = $this->intercomeoService->storeUser($userId, $email);

        if (!$user) {
            return false;
        }

        foreach ($tags as $tag) {
            $this->intercomeoService->tagUsers($user, $tag);
        }

        return true;
    }
}