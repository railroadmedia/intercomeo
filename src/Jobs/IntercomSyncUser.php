<?php

namespace Railroad\Intercomeo\Jobs;

use GuzzleHttp\Exception\GuzzleException;
use Railroad\Intercomeo\Services\IntercomeoService;

class IntercomSyncUser extends IntercomBaseJob
{
    /**
     * @var int
     */
    public $userId;

    /**
     * @var array
     */
    private $attributes;

    /**
     * List of attributes available here: https://developers.intercom.com/v2.0/reference#user-model
     *
     * @param  integer  $userId
     * @param  array  $attributes
     */
    public function __construct(int $userId, array $attributes = [])
    {
        $this->userId = $userId;
        $this->attributes = $attributes;
    }

    /**
     * @param  IntercomeoService  $intercomeoService
     *
     * @throws GuzzleException
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->syncUser($this->userId, $this->attributes);
    }
}