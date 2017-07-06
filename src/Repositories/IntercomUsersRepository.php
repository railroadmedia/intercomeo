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
     * @param null $email
     * @param null $userId
     * @return mixed
     */
    public function get($email = null, $userId = null)
    {
        if(!is_null($email)){
            return $this->query->where(['email' => $email])->first();
        }else{
            return $this->query->where(['user_id' => $userId])->first();
        }
    }

    public function store()
    {

    }
}