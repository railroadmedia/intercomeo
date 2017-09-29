<?php

namespace Railroad\Intercomeo\Services;

use Carbon\Carbon;
use Intercom\IntercomClient;
use stdClass;

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
     */
    public function getUser($userId)
    {
        return $this->intercomClient->get('users', ['user_id' => $this->prependToUserId . $userId]);
    }
    
    /**
     * @param $userId
     * @param array $attributes
     * @return stdClass
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
     * @return stdClass
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
     * @return stdClass
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
     * @param string $name
     * @param string $dateTime
     * @param int $userId
     * @return mixed
     */
    public function createEvent($name, $dateTime, $userId)
    {
        return $this->intercomClient->events->create(
            [
                'event_name' => $name,
                'created_at' => Carbon::parse($dateTime)->timestamp,
                'user_id' => $this->prependToUserId . $userId
            ]
        );
    }
}
