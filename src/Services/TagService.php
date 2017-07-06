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
}