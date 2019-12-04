<?php

namespace Railroad\Intercomeo\Jobs;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Railroad\Intercomeo\Services\IntercomeoService;

class IntercomUnTagUserByAttributes extends IntercomBaseJob
{
    /**
     * @var string
     */
    private $tagName;

    /**
     * @var array
     */
    private $attributes;

    /**
     * IntercomTagUserByAttributes constructor.
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
     * @param  IntercomeoService  $intercomeoService
     *
     * @throws GuzzleException
     */
    public function handle(IntercomeoService $intercomeoService)
    {
        try {
            $intercomeoService->unTagUser($this->tagName, $this->attributes);
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
        error_log('Error tag user to intercom. Tag: '.$this->tagName.' User attributes: '.print_r($this->attributes,
                true));

        parent::failed($exception);
    }
}