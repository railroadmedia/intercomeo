<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;

class IntercomUsersRepositoryTest extends TestCase
{
    public function test_store_new_user(){
        $userId = $this->getUserIdForGeneratedUser($this->generateUserDetails());

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
        $userId = $this->getUserIdForGeneratedUser($this->generateUserDetails());
        $randomTime = rand(2000000000, 1000000000);
        $this->assertTrue($this->usersRepository->store($userId, $randomTime));

        $user = $this->usersRepository->get($userId);
        $this->assertEquals($randomTime, $user->last_request_at);
    }

    public function test_store_last_request_time_for_existing_user(){
        // set up
        $userId = $this->getUserIdForGeneratedUser($this->generateUserDetails());

        $this->assertTrue($this->usersRepository->store($userId));
        $this->assertTrue($this->usersRepository->get($userId)->user_id == $userId);

        $randomTime = rand(2000000000, 1000000000);

        // this is relevant part

        $this->assertTrue($this->usersRepository->store($userId, $randomTime));

        $user = $this->usersRepository->get($userId);
        $this->assertEquals($randomTime, $user->last_request_at);
    }

    public function test_store_fails_gracefully_if_last_request_input_malformed(){
        $this->markTestIncomplete();
    }
}