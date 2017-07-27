<?php

namespace Railroad\Intercomeo\Listeners;

use Illuminate\Database\DatabaseManager;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Events\MemberAdded;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use Railroad\Intercomeo\Services\IntercomeoService;

class MemberAddedEventListener
{
    private $intercomClient;
    private $queryIntercomUsersTable;
    private $databaseManager;
    private $intercomUsersRepository;
    private $intercomeoService;

    public function __construct(
        IntercomClient $intercomClient,
        DatabaseManager $databaseManager,
        IntercomeoService $intercomeoService,
        IntercomUsersRepository $intercomUsersRepository
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
        $this->intercomUsersRepository = $intercomUsersRepository;
    }

    public function handle(MemberAdded $event)
    {
        $userId = $event->userId;
        $email = $event->email;
        $tags = $event->tags;

        if(!$this->intercomeoService->doesUserExistInIntercomAlready($userId)){
            $this->intercomClient->users->create([
                "email" => $email,
                "user_id" => $userId
            ]);

            $this->intercomUsersRepository->store($userId);
        }

        foreach ($tags as $tag){
            $this->intercomeoService->tagUsers($userId, $tag);
        }

        return true;
    }
}