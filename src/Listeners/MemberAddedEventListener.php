<?php

namespace Railroad\Intercomeo\Listeners;

use Illuminate\Database\DatabaseManager;
use Railroad\Intercomeo\Events\MemberAdded;
use Railroad\Intercomeo\Services\TagService;

class MemberAddedEventListener
{
    private $intercomClient;
    private $queryIntercomUsersTable;
    private $tagService;

    public function __construct(
        DatabaseManager $databaseManager,
        TagService $tagService
    )
    {
        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $intercomClient = resolve('Intercom\IntercomClient');

        $this->intercomClient = $intercomClient;
        $this->queryIntercomUsersTable = $databaseManager->connection()->table(
            config('intercomeo.tables.intercom_users')
        );

        $this->tagService = $tagService;
    }

    public function handle(MemberAdded $event)
    {
        $userId = $event->userId;
        $email = $event->email;
        $tags = $event->tags;

        $this->intercomClient->users->create([
            "email" => $email,
            "user_id" => $userId
        ]);

        foreach ($tags as $tag){
            $this->tagService->tagUsers($userId, $tag);
        }

        return true;
    }
}