<?php

namespace Railroad\Intercomeo\Tests;

class MemberAddedTest extends TestCase
{
    public function testMemberAddedEvent()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');

//        $user = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);
//        $this->assertEquals($this->userId, (int) $user->user_id);
//        $this->assertEquals($this->email, $user->email);
    }
}
