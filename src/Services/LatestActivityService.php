<?php

namespace Railroad\Intercomeo\Services;

use Carbon\Carbon;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;

class LatestActivityService
{
    private $intercomClient;
    private $intercomUsersRepository;

    public static $timeUnits = [
        'day' => 'day',
        'hour' => 'hour',
        'minute' => 'minute'
    ];

    public function __construct(
        IntercomClient $intercomClient,
        IntercomUsersRepository $intercomUsersRepository
    )
    {
        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $this->intercomClient = $intercomClient;
        $this->intercomUsersRepository = $intercomUsersRepository;
    }

    /**
     * @param integer|string $userId
     * @param int $utcTimestamp
     * @return bool
     */
    public function store($userId, $utcTimestamp = null)
    {
        $utcTimestamp = $utcTimestamp ?? time();

        $this->intercomClient->users->create([
            'user_id' => $userId,
            'last_request_at' => $utcTimestamp
        ]);

        $this->intercomUsersRepository->store($userId, $utcTimestamp);

        return true;
    }

    /**
     * @param integer $utcTimestamp
     * @return integer
     */
    public function calculateTimeToStore($utcTimestamp)
    {
        /*
         * rename from "buffer"?
         * rename from "buffer"?
         * rename from "buffer"?
         *
         * maybe "selected acceptable inaccuracy?"
         */

        $buffer = config('intercomeo.last_request_buffer_amount');

        $time = Carbon::createFromTimestampUTC($utcTimestamp);

        switch(config('intercomeo.level_to_round_down_to')){
            case self::$timeUnits['day']:
                $time->hour(0)->minute(0)->second(0);
                break;
            case self::$timeUnits['hour']:
                $time->minute(0)->second(0);
                break;
            case self::$timeUnits['minute']:
                $time->second(0);
                break;
        }

        if($buffer > 1){
            switch(config('intercomeo.last_request_buffer_unit')){
                case self::$timeUnits['day']:
                    $time->subDays($buffer + 1);
                    break;
                case self::$timeUnits['hour']:
                    $time->subHours($buffer + 1);
                    break;
                case self::$timeUnits['minute']:
                    $time->subMinutes($buffer + 1);
                    break;
            }
        }

        return $time->getTimestamp();
    }

    /**
     * @param integer $utcTimestamp
     * @return Carbon
     */
    public function calculateTimeToStoreCarbon($utcTimestamp){
        return Carbon::createFromTimestampUTC(self::calculateTimeToStore($utcTimestamp));
    }

}