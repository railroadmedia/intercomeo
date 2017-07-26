<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;

class IntercomUsersRepositoryTest extends TestCase
{
    public function test_store_new_user(){
        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);

        $this->assertTrue($this->usersRepository->store($userId));
        $userRow = $this->usersRepository->get($userId);

        $this->assertTrue($userRow->user_id == $userId);

        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        $createdWithInLastFiveMinutes = Carbon::createFromTimestampUTC($userRow->last_request_at)
            ->gt($fiveMinutesAgo);
        $createdNotAMillionYearsInTheFuture = Carbon::createFromTimestampUTC($userRow->last_request_at)
            ->gt(Carbon::now());
        $this->assertTrue($createdWithInLastFiveMinutes && $createdNotAMillionYearsInTheFuture);
    }

    public function test_store_new_user_with_last_request_time_specified(){
        $this->markTestIncomplete();
    }

    public function test_store_last_request_time_for_existing_user(){
        $this->markTestIncomplete();
    }

    public function test_store_fails_gracefully_if_last_request_input_malformed(){
        $this->markTestIncomplete();
    }
}