<?php

namespace Railroad\Intercomeo\Jobs;

use Exception;
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
        try {
            $intercomeoService->unTagUsers($this->userIds, $this->tagName);
        } catch (Exception $exception) {
            $this->failed($exception);
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     */
    public function failed(Exception $exception)
    {
        error_log('Error un-tagging users in intercom. User IDs: '
            .print_r($this->userIds, true)
            .' - Tag Name: '
            .$this->tagName);

        parent::failed($exception);
    }
}