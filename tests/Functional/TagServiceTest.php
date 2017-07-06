<?php

namespace Railroad\Intercomeo\Tests;

use Railroad\Intercomeo\Events\MemberAdded;

class TagServiceTest extends TestCase
{
    protected $tagService;

    protected function setUp()
    {
        parent::setUp();
    }

    public function test_get_tags_for_user(){
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);

        $numberOfTagsToAdd = rand(1, 9);
        $tags = [];

        for($i = 0; $i < $numberOfTagsToAdd; $i++){
            $tags[] = $this->faker->word;
        }

        event(new MemberAdded($userId, $email, $tags));

        $tagsStored = $this->tagService->getTagsForUser($userId);

        sort($tags);
        sort($tagsStored);

        $this->assertEquals($tags, $tagsStored);
    }

    public function test_add_tags_to_user(){
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);

        $numberOfTagsToAdd = rand(1, 3);
        $tags = [];

        for($i = 0; $i < $numberOfTagsToAdd; $i++){
            $tags[] = $this->faker->word;
        }

        event(new MemberAdded($userId, $email, $tags));

        $tagsStored = $this->tagService->getTagsForUser($userId);

        sort($tags);
        sort($tagsStored);

        $this->assertEquals($tags, $tagsStored);

        /*
         * ↑ is same as test_get_tags_for_user. New stuff below ↓
         */

        $numberOfTagsToAddInSecondBatch = rand(1, 3);
        $tagsSecondBatch = [];

        for($i = 0; $i < $numberOfTagsToAddInSecondBatch; $i++){
            $tagsSecondBatch[] = $this->faker->word;
        }

        foreach($tagsSecondBatch as $tagInSecondBatch){
            $this->tagService->tagUsers($userId, $tagInSecondBatch);
        }

        $tags = array_merge($tags, $tagsSecondBatch);

        $tagsStored = $this->tagService->getTagsForUser($userId);

        sort($tags);
        sort($tagsStored);

        $this->assertEquals($tags, $tagsStored);
    }

    public function test_remove_tags_from_user(){
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);

        $numberOfTagsToAdd = rand(1, 3);
        $tags = [];

        for($i = 0; $i < $numberOfTagsToAdd; $i++){
            $tags[] = $this->faker->word;
        }

        event(new MemberAdded($userId, $email, $tags));

        $tagsStored = $this->tagService->getTagsForUser($userId);

        sort($tags);
        sort($tagsStored);

        $this->assertEquals($tags, $tagsStored);

        /*
         * ↑ is same as test_get_tags_for_user. New stuff below ↓
         */

        $randomIndexValue = rand(0, count($tags) - 1);

        $tagToRemove = $tags[$randomIndexValue];

        $this->tagService->tagUsers($userId, $tagToRemove, true);

        $tagsStoredAfterUntag = $this->tagService->getTagsForUser($userId);

        $tagsAfterUntag = $tags;

        array_splice($tagsAfterUntag, $randomIndexValue, 1);

        sort($tagsAfterUntag);
        sort($tagsStoredAfterUntag);

        $this->assertEquals($tagsAfterUntag, $tagsStoredAfterUntag);
    }
}
