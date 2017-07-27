<?php

namespace Railroad\Intercomeo\Services;

use Intercom\IntercomClient;

class TagService
{
    private $intercomClient;
    private $userService;

    public function __construct(
        IntercomClient $intercomClient,
        UserService $userService
    )
    {
        $this->intercomClient = $intercomClient;
        $this->userService = $userService;
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