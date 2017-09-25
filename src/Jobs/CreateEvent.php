<?php

namespace Railroad\Intercomeo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Intercomeo\Services\IntercomeoService;

class CreateEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $dateTime;

    /**
     * @var int
     */
    public $userId;

    /**
     * @param string $name
     * @param string $dateTime
     * @param integer $userId
     */
    public function __construct($name, $dateTime, $userId)
    {
        $this->name = $name;
        $this->dateTime = $dateTime;
        $this->userId = $userId;
    }

    /**
     * @param IntercomeoService $intercomeoService
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->createEvent($this->name, $this->dateTime, $this->userId);
    }
}