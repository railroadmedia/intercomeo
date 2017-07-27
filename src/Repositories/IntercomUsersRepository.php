<?php

namespace Railroad\Intercomeo\Repositories;

use Illuminate\Database\DatabaseManager;
use stdClass;

class IntercomUsersRepository
{
    public $databaseManager;
    public $query;

    public function __construct(
        DatabaseManager $databaseManager
    )
    {
        $this->databaseManager = $databaseManager;

        $tableName = config('intercomeo.tables.intercom_users');

        $this->query = $this->databaseManager->connection()->table($tableName);
    }

    /**
     * @param null $userId
     * @return stdClass
     */
    public function get($userId)
    {
        return $this->query->where('user_id', $userId)->get()->first();
    }

    public function getLastRequestAt($userOrUserId)
    {
        if(is_object($userOrUserId)){
            if(get_class($userOrUserId) == stdClass::class){
                return (integer) $userOrUserId->last_request_at;
            }
        }

        return (integer) $this->get($userOrUserId)->last_request_at;
    }

    /*
     * $lastRequestAt must be unix timestamp
     */
    public function store($userId, $lastRequestAt = null)
    {
        if(is_null($lastRequestAt)){
            $lastRequestAt = time();
        }

        $update = $this->query->where('user_id', $userId)->update(['last_request_at' => $lastRequestAt ]);

        $insert = false;

        if(!$update){
            $insert = $this->query->insert([ 'user_id' => $userId, 'last_request_at' => $lastRequestAt ]);
        }

        return $update || $insert;
    }
}
