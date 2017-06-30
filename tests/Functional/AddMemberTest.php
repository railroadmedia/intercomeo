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

        $results = $this->queryIntercomUsersTable->select(['email', 'user_id'], [$email, $userId])->get();

        $this->assertCount(1, $results);

        // convert stdClass to array (https://stackoverflow.com/a/18576902)
        $row = json_decode(json_encode($results->first()), true);

        $this->assertCount(3, $row);
        $this->assertEquals($userId, $row['user_id']);
        $this->assertEquals($email, $row['email']);
        $this->assertTrue(array_key_exists('intercom_user_id', $row));
    }
}