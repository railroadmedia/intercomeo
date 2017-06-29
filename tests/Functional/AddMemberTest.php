<?php

namespace Railroad\Intercomeo\Tests;

use Railroad\Intercomeo\Events\AddMember;

class AddMemberTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testAddMemberEvent()
    {
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);

        $tags = [$this->faker->word];

        event(new AddMember($userId, $email, $tags));

        $this->assertTrue(true);
    }
}