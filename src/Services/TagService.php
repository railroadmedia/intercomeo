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
     * @param int|string $userIds
     * @param array|string $tag
     */
    public function addTagToUsers($userIds, $tag)
    {
        if(!is_array($userIds)){
            $userIds = [$userIds];
        }

        $users = [];

        foreach($userIds as $userId){
            $users[] = ['user_id' => $userId];
        }

        $this->intercomClient->tags->tag([
            'name' => $tag,
            'users' => $users
        ]);
    }

    /**
     * @param array $userIds
     * @param string $tag
     */
//    public function addTagToUsers($userIds, $tag)
//    {
//        $this->intercomClient->users->update
//    }
//
//    public function untagUsers($userIds, $tags)
//    {
//        foreach($userIds as $userId){
//
//        }
//    }
}