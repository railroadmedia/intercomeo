<?php

namespace Railroad\Intercomeo\Jobs;

use GuzzleHttp\Exception\GuzzleException;
use Railroad\Intercomeo\Services\IntercomeoService;

class IntercomTriggerEventForUser extends IntercomBaseJob
{
    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $dateTimeString;

    /**
     * @param  string  $userId
     * @param  string  $name
     * @param  string  $dateTimeString
     */
    public function __construct($userId, string $name, string $dateTimeString)
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->dateTimeString = $dateTimeString;
    }

    /**
     * @param  IntercomeoService  $intercomeoService
     *
     * @throws GuzzleException
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->triggerEventForUser($this->userId, $this->name, $this->dateTimeString);
    }
}