<?php

namespace Railroad\Intercomeo\Jobs;

use Railroad\Intercomeo\Services\IntercomeoService;

class IntercomUnTagUsers extends IntercomBaseJob
{
    /**
     * @var string[]
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->unTagUsers($this->userIds, $this->tagName);
    }
}