<?php

namespace Railroad\Intercomeo\Jobs;

use GuzzleHttp\Exception\GuzzleException;
use Railroad\Intercomeo\Services\IntercomeoService;

class IntercomTagUsers extends IntercomBaseJob
{
    /**
     * @var int[]
     */
    public $userIds;

    /**
     * @var string
     */
    private $tagName;

    /**
     * TagUsers constructor.
     *
     * @param  string  $tagName
     * @param  array  $userIds
     */
    public function __construct(array $userIds, string $tagName)
    {
        $this->userIds = $userIds;
        $this->tagName = $tagName;
    }

    /**
     * @param  IntercomeoService  $intercomeoService
     *
     * @throws GuzzleException
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->tagUsers($this->userIds, $this->tagName);
    }
}