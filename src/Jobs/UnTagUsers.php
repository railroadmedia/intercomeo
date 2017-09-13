<?php

namespace Railroad\Intercomeo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Intercomeo\Services\IntercomeoService;

class UnTagUsers implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @var int[]
     */
    public $userIds;

    /**
     * TagUsers constructor.
     *
     * @param string $tagName
     * @param array $userIds
     */
    public function __construct($tagName, array $userIds)
    {
        $this->tagName = $tagName;
        $this->userIds = $userIds;
    }

    /**
     * @param IntercomeoService $intercomeoService
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->unTagUsers($this->tagName, $this->userIds);
    }
}