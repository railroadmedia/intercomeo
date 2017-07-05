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

        $results = $this->queryIntercomUsersTable->select()->where([['email', $email], ['user_id', $userId]])->get();

        $this->assertCount(1, $results);

        $this->assertEquals($userId, $results->first()->user_id);
        $this->assertEquals($email, $results->first()->email);
        $this->assertTrue(!empty($results->first()->intercom_user_id));

        $this->deleteUser($email);
    }
}
