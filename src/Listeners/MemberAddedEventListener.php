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

        $intercomApiResults = [];

        $intercomApiResult = $this->intercomClient->users->create([
            "email" => $email,
            "user_id" => $userId
        ]);

        $intercomApiResults['user create'] = $intercomApiResult;

        foreach ($tags as $tag){
            $intercomApiResult = $this->intercomClient->tags->tag([
                "name" => $tag,
                "users" => [["email" => $email]] // could use laravel_user_id or intercom user id?
            ]);

            $intercomApiResults['add tag ' . $tag] = $intercomApiResult;
        }

        $intercomUserId = $intercomApiResults['user create']->id;

        $this->queryIntercomUsersTable->insert([
            'user_id' => $userId,
            'intercom_user_id' => $intercomUserId,
            'email' => $email,
        ]);

        return true;
    }
}