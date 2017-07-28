<?php

namespace Railroad\Intercomeo\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
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

    public function storeUser($userId, $email)
    {
        $user = $this->intercomClient->users->create([ "user_id" => $userId, "email" => $email ]);

        $success = $this->validUserCreated($user, $userId, $email ) && $this->intercomUsersRepository->store($userId);

        if(!$success){
            Log::error('\Railroad\Intercomeo\Services\IntercomeoService::storeUser failed to for user ' . $userId);
            return false;
        }

        return $user;
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
     * @param stdClass[] $users
     * @param array|string $tags
     * @param bool $untag
     * @return bool
     * Makes one request *per* tag. Sad!
     */
    public function tagUsers($users, $tags, $untag = false)
    {
        $simplifiedUsers = [];
        $creationFailed = false;

        if(!is_array($users)){
            $users = [$users];
        }

        if(!is_array($tags)){
            $tags = [$tags];
        }

        foreach($users as $user){
            if(!is_object($user)){
                return false;
            }else{
                if(!get_class($user) === stdClass::class){
                    return false;
                    Log::error('User was passed to \Railroad\Intercomeo\Services\IntercomeoService::tagUsers but' .
                    ' was not stdClass object as requires');
                }else{
                    $simplifiedUsers[] = ['user_id' => $user->user_id, 'untag' => $untag];
                }
            }
        }

        foreach($tags as $tag){
            $tag = $this->intercomClient->tags->tag([
                'name' => $tag,
                'users' => $users,
                'untag' => $untag
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
     * @param stdClass[] $users
     * @param array|string $tags
     * @return bool
     * Makes one request *per* tag. Sad!
     */
    public function untagUsers($users, $tags)
    {
        return $this->tagUsers($users, $tags, true);
    }

    public function validUserCreated($apiCallResult, $userId, $email = null)
    {
        if(is_object($apiCallResult)){
            if(get_class($apiCallResult) == stdClass::class){
                return ($apiCallResult->type === 'user') &&
                    !empty($apiCallResult->id) &&
                    (!is_null($userId) ? $apiCallResult->user_id == $userId : true) &&
                    (!is_null($email) ? $apiCallResult->email == $email : true) &&
                    ($apiCallResult->app_id === config('intercomeo.app_id'));
            }
        }

        return false;
    }
}