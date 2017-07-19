<?php

namespace Railroad\Intercomeo\Services;

use Intercom\IntercomClient;

class TagService
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;

    public function __construct(IntercomClient $intercomClient)
    {
        $this->intercomClient = $intercomClient;
    }

    /**
     * @param $userId
     * @return array
     */
    public function getTagsForUser($userId)
    {
        $user = $this->intercomClient->users->getUsers(['user_id' => $userId]);

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
     *
     * Makes one request *per* tag. Sad!
     */
    public function tagUsers($userIds, $tags, $untag = false)
    {
        if(!is_array($userIds)){
            $userIds = [$userIds];
        }

        if(!is_array($tags)){
            $tags = [$tags];
        }

        $users = [];

        foreach($userIds as $userId){
            $users[] = [
                'user_id' => $userId,
                'untag' => $untag
            ];
        }

        foreach($tags as $tag){
            $this->intercomClient->tags->tag([
                'name' => $tag,
                'users' => $users
            ]);
        }
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