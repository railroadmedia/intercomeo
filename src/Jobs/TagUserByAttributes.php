<?php

namespace Railroad\Intercomeo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Intercomeo\Services\IntercomeoService;

class TagUserByAttributes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @var array
     */
    public $attributes;

    /**
     * TagUserByAttributes constructor.
     *
     * @param $tagName
     * @param array $attributes
     */
    public function __construct($tagName, array $attributes)
    {
        $this->tagName = $tagName;
        $this->attributes = $attributes;
    }

    /**
     * @param IntercomeoService $intercomeoService
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->tagUser($this->tagName, $this->attributes);
    }
}