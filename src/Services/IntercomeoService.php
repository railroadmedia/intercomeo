<?php

namespace Railroad\Intercomeo\Services;

use Carbon\Carbon;
use Exception;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use stdClass;

class IntercomeoService
{
    public static $timeUnits = [
        'day' => 'day',
        'hour' => 'hour',
        'minute' => 'minute'
    ];

    private $intercomClient;
    private $intercomUsersRepository;

    /*
     * FYI IntercomClient is created as singleton in service
     * provide because we need to set the api credentials.
     */
    public function __construct(
        IntercomClient $intercomClient,
        IntercomUsersRepository $intercomUsersRepository
    )
    {
        $this->intercomClient = $intercomClient;
        $this->intercomUsersRepository = $intercomUsersRepository;
    }

    /*
     * One request per user create. Sad!
     */
    public function createUsers($userIds, $tags = null)
    {
        $intercomUsers = null;
        $creationFailed = false;
        $successfullyCreated = [];

        if(!is_array($userIds)){
            $userIds = [$userIds];
        }

        foreach($userIds as $userId){

            if(!$this->doesUserExistInIntercomAlready($userId)){

                $intercomUser = $this->intercomClient->users->create([
                    'user_id' => $userId
                ]);

                $successfulCreation =
                    ($intercomUser->type === 'user') &&
                    !empty($intercomUser->id) &&
                    ($intercomUser->app_id === config('intercomeo.app_id'));

                if(!$successfulCreation){
                    $creationFailed = true;
                }else{
                    $successfullyCreated[] = $userId;
                }

            }

        }

        if(!is_null($tags)){
            $successfulCreation = $this->tagUsers($successfullyCreated, $tags);

            if(!$successfulCreation){
                $creationFailed = true;
            }
        }

        $success = !$creationFailed;

        return $success;
    }

    /**
     * @param string|integer $userId
     * @return stdClass|null
     * @see https://developers.intercom.com/v2.0/reference#user-model
     */
    public function getUser($userId)
    {
        $user = null;

        try {
            $user = $this->intercomClient->users->getUsers(['user_id' => $userId]);
        } catch (Exception $e) {
            return $user;
        }

        return $user;
    }

    public function doesUserExistInIntercomAlready($userId)
    {
        $exists = !empty($this->intercomUsersRepository->get($userId));

        if(!$exists){

            $user = $this->getUser($userId);

            if(!empty($user)){
                $exists = (
                    ($user->type === 'user') &&
                    ($user->user_id === $userId) &&
                    !empty($user->id) &&
                    ($user->app_id === config('intercomeo.app_id'))
                );
            }
        }

        return $exists;
    }

    /**
     *
     * @param stdClass $user
     * @return integer
     *
     * Designed to have the result of the `getUser` method passed in. Example:
     *
     *     $lastRequestAt = Carbon::parse(
     *         $this->userService->getLastRequestAt(
     *             $this->userService->getUser($userId)
     *         )
     *     );
     */
    public function getLastRequestAt(stdClass $user)
    {
        return (integer) $user->last_request_at;
    }

    /**
     * @param integer|string $userId
     * @param int $utcTimestamp
     * @return bool
     */
    public function storeLatestActivity($userId, $utcTimestamp = null)
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
    public function calculateLatestActivityTimeToStore($utcTimestamp)
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
    public function calculateLatestActivityTimeToStoreCarbon($utcTimestamp){
        return Carbon::createFromTimestampUTC(self::calculateLatestActivityTimeToStore($utcTimestamp));
    }

    /**
     * @param $userId
     * @return array
     */
    public function getTagsForUser($userId)
    {
        $user = $this->intercomClient->users->getUsers(['user_id' => $userId]);

        // todo: what if user does not exist? Make sure that does not break this.
        // todo: what if user does not exist? Make sure that does not break this.
        // todo: what if user does not exist? Make sure that does not break this.

        $tags = $user->tags->tags;

        $tagsSimple = [];
        foreach($tags as $tag){
            $tagsSimple[] = $tag->name;
        }

        return $tagsSimple;
    }

    /**
     * @param int|string|array $userIds
     * @param array|string $tags
     * @param bool $untag
     * @return boolean
     *
     * Makes one request *per* tag. Sad!
     */
    public function tagUsers($userIds, $tags, $untag = false)
    {
        $creationFailed = false;

        if(!is_array($userIds)){
            $userIds = [$userIds];
        }

        if(!is_array($tags)){
            $tags = [$tags];
        }

        $users = [];

        foreach($userIds as $userId){
            $creationFailed = false;

            if(!$this->userService->doesUserExistInIntercomAlready($userId)){
                $creationFailed = !$this->userService->createUsers($userId);
            }

            if(!$creationFailed){
                $users[] = [
                    'user_id' => $userId,
                    'untag' => $untag
                ];
            }
        }

        foreach($tags as $tag){
            $tag = $this->intercomClient->tags->tag([
                'name' => $tag,
                'users' => $users
            ]);

            $successfulCreation =
                ($tag->type === 'tag') &&
                !empty($tag->id) &&
                ($tag->app_id === config('intercomeo.app_id'));

            if(!$successfulCreation){
                $creationFailed = true;
            }
        }

        return !$creationFailed;
    }

    /**
     * @param array|integer|string $userIds
     * @param array|string $tags
     *
     * Makes one request *per* tag. Sad!
     */
    public function untagUsers($userIds, $tags)
    {
        if(!is_array($userIds)){
            $userIds = [$userIds];
        }
        if(!is_array($tags)){
            $tags = [$tags];
        }

        foreach($tags as $tag){
            $this->tagUsers($userIds, $tag, true);
        }
    }
}