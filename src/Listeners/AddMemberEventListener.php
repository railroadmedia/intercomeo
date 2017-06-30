<?php

namespace Railroad\Intercomeo\Listeners;

use Illuminate\Database\DatabaseManager;
use Railroad\Intercomeo\Events\AddMember;

class AddMemberEventListener
{

    private $intercomClient;
    private $databaseManager;
    private $connection;

    public function __construct(DatabaseManager $databaseManager)
    {
        /*
         * created as singleton in service provide because we need to set the api credentials
         */

        $intercomClient = resolve('Intercom\IntercomClient');

        $this->intercomClient = $intercomClient;
        $this->databaseManager = $databaseManager;
        $this->connection = $databaseManager->connection();
    }

    public function handle(AddMember $event)
    {
        $userId = $event->userId;
        $email = $event->email;
        $tags = $event->tags;

        $intercomApiResults = [];

        $intercomApiResult = $this->intercomClient->users->create([
            "email" => $email,
            "custom_attributes" => [
                "laravel_user_id" => $userId
            ]
        ]);

        $intercomApiResults['user create'] = $intercomApiResult;

        foreach ($tags as $tag){
            $intercomApiResult = $this->intercomClient->tags->tag([
                "name" => $tag,
                "users" => [["email" => $email]] // could use laravel_user_id or intercom user id?
            ]);

            $intercomApiResults['add tag ' . $tag] = $intercomApiResult;
        }

        $query = $this->connection->table(config('intercomeo.tables.intercom_users'));

        $query->insert([
            'user_id' => $userId,
            'intercom_user_id' => $intercomApiResults['user create']->id,
            'email' => $email,
        ]);

        return true;
    }
}