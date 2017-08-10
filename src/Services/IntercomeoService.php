<?php

namespace Railroad\Intercomeo\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Intercom\IntercomClient;
use stdClass;

class IntercomeoService
{
    public static $timeUnits = [
        'day' => 'day',
        'hour' => 'hour',
        'minute' => 'minute'
    ];

    private $intercomClient;

    /*
     * FYI IntercomClient is created as singleton in service
     * provide because we need to set the api credentials.
     */
    public function __construct(
        IntercomClient $intercomClient
    )
    {
        $this->intercomClient = $intercomClient;
    }

    /**
     * @param $userId
     * @param $email
     * @return bool|stdClass
     */
    public function storeUser($userId, $email)
    {
        $user = $this->intercomClient->users->create([ "user_id" => $userId, "email" => $email ]);

        if(!$this->validUserCreated($user, $userId, $email)){
            Log::error('\Railroad\Intercomeo\Services\IntercomeoService::storeUser failed to for user ' . $userId);
            return false;
        }

        return $user;
    }

    /**
     * @param string|integer $userId
     * @return stdClass|bool
     * @see https://developers.intercom.com/v2.0/reference#user-model
     */
    public function getUser($userId)
    {
        $user = false;

        try {
            $user = $this->intercomClient->users->getUsers(['user_id' => $userId]);
        } catch (Exception $e) {
            return $user;
        }

        return $user;
    }

    /**
     * @param $userId
     * @param $email
     * @return bool|stdClass
     */
    public function getUserCreateIfDoesNotYetExist($userId, $email)
    {
        $user = $this->getUser($userId);

        if(!$user){
            $user = $this->storeUser($userId, $email);
        }

        return $user;
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
     * @param stdClass $user
     * @param int $utcTimestamp
     * @return bool
     * @internal param int|string $userId
     */
    public function storeLatestActivity(stdClass $user, $utcTimestamp = null)
    {
        $userId = $user->user_id;
        $utcTimestamp = $utcTimestamp ?? time();

        return $this->intercomClient->users->create([
            'user_id' => $userId,
            'last_request_at' => $utcTimestamp
        ]);
    }

    /**
     * @param integer $utcTimestamp
     * @return integer
     */
    public function roundTimeDownForLatestActivityRecord($utcTimestamp)
    {
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

        return $time->timestamp;
    }


    /**
     * @param stdClass $user
     * @param int $timeOfCurrentRequest
     * @param int $timeOfPreviousRequest
     * @return bool
     */
    public function lastRequestAtUpdateEvaluationAndAction(
        $user,
        $timeOfCurrentRequest,
        $timeOfPreviousRequest
    ){
        $timeOfPreviousRequest = $this->roundTimeDownForLatestActivityRecord($timeOfPreviousRequest);
        $timeOfCurrentRequest = $this->roundTimeDownForLatestActivityRecord($timeOfCurrentRequest);

        if($timeOfCurrentRequest > $timeOfPreviousRequest){
            $this->storeLatestActivity($user, $timeOfCurrentRequest);
        }

        return true;
    }

    /**
     * @param stdClass $user
     * @param bool $checkForNew
     * @return array
     */
    public function getTagsFromUser($user, $checkForNew = true)
    {
        if($checkForNew){
            $user = $this->getUser($user->user_id);
        }

        $tags = $user->tags->tags;

        $tagsSimple = [];
        foreach($tags as $tag){
            $tagsSimple[] = $tag->name;
        }

        return $tagsSimple;
    }

    /**
     * @param stdClass|[] $users
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
                    Log::error('User was passed to \Railroad\Intercomeo\Services\IntercomeoService::tagUsers but' .
                    ' was not stdClass object as requires');
                    return false;
                }else{
                    $simplifiedUsers[] = ['user_id' => $user->user_id, 'untag' => $untag];
                }
            }
        }

        foreach($tags as $tagName){
            $tag = $this->intercomClient->tags->tag([
                'name' => $tagName,
                'users' => $simplifiedUsers
            ]);

            $successfulCreation =
                ($tag->type === 'tag') &&
                ($tag->name === $tagName) &&
                !empty($tag->id);

            if(!$successfulCreation){
                $creationFailed = true;
            }
        }

        return !$creationFailed;
    }

    /**
     * @param stdClass|[] $users
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
