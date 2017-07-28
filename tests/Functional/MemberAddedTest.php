<?php

namespace Railroad\Intercomeo\Tests;

class MemberAddedTest extends TestCase
{
    public function testMemberAddedEvent()
    {
        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $email = $this->getEmailForGeneratedUser($userDetails);
        $this->createUser($userId, $email);

        $user = $this->intercomClient->users->getUsers(['user_id' => $userId]);
        $this->assertEquals($userId, (int) $user->user_id);
        $this->assertEquals($email, $user->email);
    }
}
