<?php

namespace Railroad\Intercomeo\Tests;

use Railroad\Intercomeo\Events\MemberAdded;

class TagServiceTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_get_tags_for_user(){
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);

        $numberOfTagsToAdd = rand(1, 9);
        $tags = [];

        for($i = 0; $i <= $numberOfTagsToAdd; $i++){
            $tags[] = $this->faker->word;
        }

        event(new MemberAdded($userId, $email, $tags));

        $tagsStored = $this->tagService->getTagsForUser($userId);

        $this->assertEquals(sort($tags), sort($tagsStored));
    }
}
