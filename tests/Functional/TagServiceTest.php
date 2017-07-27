<?php

namespace Railroad\Intercomeo\Tests;

use Railroad\Intercomeo\Services\TagService;

class TagServiceTest extends TestCase
{
    /** @var TagService $tagService */
    private $tagService;

    public function setUp()
    {
        parent::setUp();

        $this->tagService = $this->app->make(TagService::class);
    }

    public function test_get_tags_for_user(){
        $tagsStored = $this->tagService->getTagsForUser($this->userId);

        sort($this->tags);
        sort($tagsStored);

        $this->assertEquals($this->tags, $tagsStored);
    }

    public function test_add_tags_to_user(){
        $numberOfTagsToAddInSecondBatch = rand(1, 3);
        $tagsSecondBatch = [];

        for($i = 0; $i < $numberOfTagsToAddInSecondBatch; $i++){
            $tagsSecondBatch[] = $this->faker->word;
        }

        foreach($tagsSecondBatch as $tagInSecondBatch){
            $this->tagService->tagUsers($this->userId, $tagInSecondBatch);
        }

        $tags = array_merge($this->tags, $tagsSecondBatch);

        $tagsStored = $this->tagService->getTagsForUser($this->userId);

        sort($tags);
        sort($tagsStored);

        $this->assertEquals($tags, $tagsStored);
    }

    public function test_remove_tags_from_user(){
        $randomIndexValue = rand(0, count($this->tags) - 1);

        $tagToRemove = $this->tags[$randomIndexValue];

        $this->tagService->tagUsers($this->userId, $tagToRemove, true);

        $tagsStoredAfterUntag = $this->tagService->getTagsForUser($this->userId);

        $tagsAfterUntag = $this->tags;

        array_splice($tagsAfterUntag, $randomIndexValue, 1);

        sort($tagsAfterUntag);
        sort($tagsStoredAfterUntag);

        $this->assertEquals($tagsAfterUntag, $tagsStoredAfterUntag);
    }
}
