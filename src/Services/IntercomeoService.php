<?php

namespace Railroad\Intercomeo\Services;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Intercom\IntercomClient;


class IntercomeoService
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;

    /**
     * @var string
     */
    private $prependToUserId;

    /**
     * IntercomeoService constructor.
     *
     * @param IntercomClient $intercomClient
     */
    public function __construct(IntercomClient $intercomClient)
    {
        $this->intercomClient = $intercomClient;
        $this->prependToUserId = config('intercomeo.user_id_domain_prepend_string');
    }

    /**
     * @param $userId
     * @return mixed
     * @throws GuzzleException
     */
    public function getUser($userId)
    {
        return $this->intercomClient->get('users', ['user_id' => $this->prependToUserId . $userId]);
    }

    /**
     * @param $userId
     * @param array $attributes
     * @return mixed
     * @throws GuzzleException
     */
    public function syncUser($userId, array $attributes)
    {
        return $this->intercomClient->users->create(
            array_merge(["user_id" => $this->prependToUserId . $userId], $attributes)
        );
    }

    /**
     * Makes one request *per* tag.
     *
     * @param $tag
     * @param array $userIds
     * @return mixed
     * @throws GuzzleException
     */
    public function tagUsers($tag, array $userIds)
    {
        $users = [];

        foreach ($userIds as $userId) {
            $users[] = ['user_id' => $this->prependToUserId . $userId];
        }

        return $this->intercomClient->tags->tag(['name' => $tag, 'users' => $users]);
    }

    /**
     * Makes one request *per* tag.
     *
     * @param $tag
     * @param array $userIds
     * @return mixed
     * @throws GuzzleException
     */
    public function unTagUsers($tag, array $userIds)
    {
        $users = [];

        foreach ($userIds as $userId) {
            $users[] = ['user_id' => $this->prependToUserId . $userId, "untag" => true];
        }

        return $this->intercomClient->tags->tag(['name' => $tag, 'users' => $users]);
    }

    /**
     * @param $name
     * @param $dateTime
     * @param $userId
     * @return mixed
     * @throws GuzzleException
     */
    public function createEvent($name, $dateTime, $userId)
    {
        return $this->intercomClient->events->create(
            [
                'event_name' => $name,
                'created_at' => Carbon::parse($dateTime)->timestamp,
                'user_id' => $this->prependToUserId . $userId,
            ]
        );
    }

    /**
     * @param array $attributes
     * @return mixed
     * @throws GuzzleException
     */
    public function createUpdateUser(array $attributes)
    {
        if (array_key_exists('user_id', $attributes)) {
            $attributes["user_id"] = $this->prependToUserId . $attributes['user_id'];
        }

        return $this->intercomClient->users->create(
            $attributes
        );
    }

    /**
     * @param $tag
     * @param $user
     * @return mixed
     * @throws GuzzleException
     */
    public function tagUser($tag, array $user)
    {
        return $this->intercomClient->tags->tag(['name' => $tag, 'users' => [$user]]);
    }

    /**
     * @param $tag
     * @param $users
     * @return mixed
     * @throws GuzzleException
     */
    public function unTagUser($tag, array $users)
    {
        return $this->intercomClient->tags->tag(['name' => $tag, 'users' => [array_merge($users, ['untag' => true])]]);
    }
}
