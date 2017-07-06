<?php

namespace Railroad\Intercomeo\Tests;

use Railroad\Intercomeo\Events\MemberAdded;

class MemberAddedTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testMemberAddedEvent()
    {
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);
        $tags = [$this->faker->word];

        event(new MemberAdded($userId, $email, $tags));

        $user = $this->intercomClient->users->getUsers(['user_id' => $userId]);
        $this->assertEquals($userId, (int) $user->user_id);
        $this->assertEquals($email, $user->email);

        $this->deleteUser($email);
    }
}
