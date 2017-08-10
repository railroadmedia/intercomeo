<?php

namespace Railroad\Intercomeo\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Intercom\IntercomClient;
use stdClass;

class IntercomeoService
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;

    /**
     * IntercomeoService constructor.
     *
     * @param IntercomClient $intercomClient
     */
    public function __construct(IntercomClient $intercomClient)
    {
        $this->intercomClient = $intercomClient;
    }

    /**
     * @param $userId
     * @param $email
     * @return stdClass|Exception
     */
    public function storeUser($userId, $email)
    {
        return $this->intercomClient->users->create(["user_id" => $userId, "email" => $email]);
    }

    /**
     * @param string|integer $userId
     * @return stdClass|Exception
     * @see https://developers.intercom.com/v2.0/reference#user-model
     */
    public function getUser($userId)
    {
        return $this->intercomClient->users->getUsers(['user_id' => $userId]);
    }

    /**
     * @param $userId
     * @param $email
     * @return stdClass|Exception
     */
    public function getUserCreateIfDoesNotYetExist($userId, $email)
    {
        $user = null;

        try {
            $user = $this->getUser($userId);
        } catch (Exception $e) {
            try {
                $user = $this->storeUser($userId, $email);
            } catch (Exception $e) {
                return $e;
            }
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
        return (integer)$user->last_request_at;
    }

    /**
     * @param stdClass $user
     * @param int $utcTimestamp
     * @return stdClass|Exception
     */
    public function storeLatestActivity(stdClass $user, $utcTimestamp = null)
    {
        $userId = $user->user_id;
        $utcTimestamp = $utcTimestamp ?? time();

        return $this->intercomClient->users->create(
            [
                'user_id' => $userId,
                'last_request_at' => $utcTimestamp
            ]
        );
    }

    /**
     * @param stdClass $user
     * @param int $timeOfCurrentRequest
     * @param int|null $timeOfPreviousRequest
     * @return bool|stdClass|Exception
     */
    public function lastRequestAtUpdateEvaluationAndAction(
        $user,
        $timeOfCurrentRequest,
        $timeOfPreviousRequest = null
    ) {
        if (is_null($timeOfPreviousRequest) ||
            $timeOfCurrentRequest - $timeOfPreviousRequest > config('intercomeo.last_request_buffer_unit')) {
            return $this->storeLatestActivity($user, $timeOfCurrentRequest);
        }

        return true;
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

        if (!is_array($users)) {
            $users = [$users];
        }

        if (!is_array($tags)) {
            $tags = [$tags];
        }

        foreach ($users as $user) {
            $simplifiedUsers[] = ['user_id' => $user->user_id, 'untag' => $untag];
        }

        foreach ($tags as $tagName) {
            try {
                $this->intercomClient->tags->tag(
                    [
                        'name' => $tagName,
                        'users' => $simplifiedUsers
                    ]
                );
            } catch (Exception $exception) {
                Log::error(
                    '\Railroad\Intercomeo\Services\IntercomeoService::tagUsers failed for tag ' .
                    $tagName .
                    ' for users ' .
                    print_r($simplifiedUsers, true) .
                    ' with error: ' .
                    $exception->getMessage()
                );
            }
        }

        return true;
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
}
