<?php

namespace Railroad\Intercomeo\Listeners;

use Illuminate\Database\DatabaseManager;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Events\MemberAdded;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use Railroad\Intercomeo\Services\TagService;

class MemberAddedEventListener
{
    private $intercomClient;
    private $queryIntercomUsersTable;
    private $tagService;
    /**
     * @var DatabaseManager
     */
    private $databaseManager;
    /**
     * @var IntercomUsersRepository
     */
    private $intercomUsersRepository;

    public function __construct(
        IntercomClient $intercomClient,
        DatabaseManager $databaseManager,
        TagService $tagService,
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

        $this->tagService = $tagService;
        $this->databaseManager = $databaseManager;
        $this->intercomUsersRepository = $intercomUsersRepository;
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

        $this->intercomUsersRepository->store($userId);
        return true;
    }
}