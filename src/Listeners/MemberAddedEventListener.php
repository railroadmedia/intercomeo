<?php

namespace Railroad\Intercomeo\Listeners;

use Illuminate\Database\DatabaseManager;
use Railroad\Intercomeo\Events\MemberAdded;

class MemberAddedEventListener
{
    private $intercomClient;
    private $queryIntercomUsersTable;

    public function __construct(DatabaseManager $databaseManager)
    {
        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $intercomClient = resolve('Intercom\IntercomClient');

        $this->intercomClient = $intercomClient;
        $this->queryIntercomUsersTable = $databaseManager->connection()->table(
            config('intercomeo.tables.intercom_users')
        );
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
            $this->intercomClient->tags->tag([
                "name" => $tag,
                "users" => [["email" => $email]] // could use laravel_user_id or intercom user id?
            ]);
        }

        return true;
    }
}