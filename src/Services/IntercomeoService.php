<?php

namespace Railroad\Intercomeo\Services;

use GuzzleHttp\Exception\GuzzleException;
use Intercom\IntercomClient;
use Railroad\Intercomeo\Exceptions\IntercomeoException;
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
     * @param array $attributes
     * @return stdClass
     * @throws IntercomeoException
     */
    public function syncUser($userId, array $attributes)
    {
        try {
            return $this->intercomClient->users->create(
                array_merge(["user_id" => $this->prependToUserId . $userId], $attributes)
            );
        } catch (GuzzleException $exception) {
            throw new IntercomeoException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Makes one request *per* tag.
     *
     * @param $tag
     * @param array $userIds
     * @return stdClass
     * @throws IntercomeoException
     */
    public function tagUsers($tag, array $userIds)
    {
        $users = [];

        foreach ($userIds as $userId) {
            $users[] = ['user_id' => $this->prependToUserId . $userId];
        }

        try {
            return $this->intercomClient->tags->tag(['name' => $tag, 'users' => $users]);
        } catch (GuzzleException $exception) {
            throw new IntercomeoException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Makes one request *per* tag.
     *
     * @param $tag
     * @param array $userIds
     * @return stdClass
     * @throws IntercomeoException
     */
    public function unTagUsers($tag, array $userIds)
    {
        $users = [];

        foreach ($userIds as $userId) {
            $users[] = ['user_id' => $this->prependToUserId . $userId, "untag" => true];
        }

        try {
            return $this->intercomClient->tags->tag(['name' => $tag, 'users' => $users]);
        } catch (GuzzleException $exception) {
            throw new IntercomeoException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
