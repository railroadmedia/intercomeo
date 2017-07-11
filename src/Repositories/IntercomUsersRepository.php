<?php

namespace Railroad\Intercomeo\Repositories;

use Illuminate\Database\DatabaseManager;

class IntercomUsersRepository
{
    public $databaseManager;
    public $query;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;

        $tableName = config('intercomeo.tables.intercom_users');

        $this->query = $this->databaseManager->connection()->table($tableName);
    }

    /**
     * @param null $userId
     * @return mixed
     */
    public function get($userId)
    {
        return $this->query->where('user_id', $userId)->get()->first();
    }

    public function getLastRequestAt($userId)
    {
        $result = $this->query->where('user_id', $userId)->first();

        return $result->last_request_at;
    }

    public function store($userId, $lastRequestAt)
    {
        return $this->query->insert([ 'user_id' => $userId, 'last_request_at' => $lastRequestAt ]);
    }

//    --- No need for "storeLastRequestAt" right now because it's the only column that isn't ID or timestamps ---
//    public function storeLastRequestAt($userId, $lastRequestAt)
//    {
//        return $this->query->update(['user_id' => $userId, 'last_request_at' => $lastRequestAt]);
//    }

}
