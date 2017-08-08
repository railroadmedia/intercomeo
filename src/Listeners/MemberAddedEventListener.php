<?php

namespace Railroad\Intercomeo\Listeners;

use Illuminate\Database\DatabaseManager;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Events\MemberAdded;
use Railroad\Intercomeo\Services\IntercomeoService;

class MemberAddedEventListener
{
    private $intercomClient;
    private $queryIntercomUsersTable;
    private $databaseManager;
    private $intercomeoService;

    public function __construct(
        IntercomClient $intercomClient,
        DatabaseManager $databaseManager,
        IntercomeoService $intercomeoService
    )
    {
        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $this->intercomClient = $intercomClient;

        $this->queryIntercomUsersTable = $databaseManager->connection()->table(
            config('intercomeo.tables.intercom_users')
        );

        $this->databaseManager = $databaseManager;
        $this->intercomeoService = $intercomeoService;
    }

    public function handle(MemberAdded $event)
    {
        $userId = $event->userId;
        $email = $event->email;
        $tags = $event->tags;

        $user = $this->intercomeoService->storeUser($userId, $email);

        if(!$user){
            return false;
        }

        foreach ($tags as $tag){
            $this->intercomeoService->tagUsers($user, $tag);
        }

        return true;
    }
}