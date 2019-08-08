<?php

namespace Railroad\Intercomeo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Intercomeo\Services\IntercomeoService;

class SyncUserByAttributes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var array
     */
    private $attributes;

    /**
     * List of attributes available here: https://developers.intercom.com/v2.0/reference#user-model
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @param IntercomeoService $intercomeoService
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->createUpdateUser($this->attributes);
    }
}