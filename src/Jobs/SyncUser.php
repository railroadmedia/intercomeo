<?php

namespace Railroad\Intercomeo\Jobs;

use App\Jobs\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Railroad\Intercomeo\Services\IntercomeoService;

class SyncUser extends Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

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
     * @param integer $userId
     * @param array $attributes
     */
    public function __construct($userId, array $attributes = [])
    {
        $this->userId = $userId;
        $this->attributes = $attributes;
    }

    /**
     * @param IntercomeoService $intercomeoService
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        $intercomeoService->syncUser($this->userId, $this->attributes);
    }
}