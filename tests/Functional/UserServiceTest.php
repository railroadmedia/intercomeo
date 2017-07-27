<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Services\UserService;

class UserServiceTest extends TestCase
{
    /** @var UserService $userService */
    private $userService;

    public function setUp()
    {
        parent::setUp();

        $this->userService = $this->app->make(UserService::class);
    }

    public function test_create_users_single_users()
    {
        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);

        $this->userService->createUsers($userId);

        $this->markTestIncomplete();
    }

    public function test_create_users_multiple_users()
    {
        $this->markTestIncomplete();
    }

    public function test_store_last_updated_in_database()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $knownDate = time();
//        $this->userService->storeLatestActivity($this->userId, $knownDate);
//
//        $this->assertEquals($knownDate, $this->usersRepository->getLastRequestAt($this->userId));
//    }

    public function test_first_store_user_attribute_last_request_at()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $this->userService->storeLatestActivity($this->userId);
//
//        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);
//        $timeReturned = Carbon::createFromTimestampUTC($userReturnedFromIntercom->last_request_at);
//        $anHourAgo = Carbon::now()->subHour();
//
//        $this->assertTrue($timeReturned->gt($anHourAgo));
//    }

    public function test_first_store_user_attribute_last_request_at_passing_in_timestamp()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $knownDate = time();
//
//        $this->userService->storeLatestActivity($this->userId, $knownDate);
//
//        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);
//
//        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);
//    }

    public function test_update_last_request_at_attribute_for_user_do_not_specify_time()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $knownDate = time()-10000;
//
//        $this->userService->storeLatestActivity($this->userId, $knownDate);
//
//        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);
//        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);
//
//        $this->userService->storeLatestActivity($this->userId);
//
//        $this->assertTrue((time() - 10 ) < $this->usersRepository->getLastRequestAt($this->userId));
//        $this->assertTrue((time() + 10 ) > $this->usersRepository->getLastRequestAt($this->userId));
//    }

    public function test_store_update_last_request_at_when_required()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $knownTime = Carbon::createFromTimestampUTC(time());
//
//        $this->userService->storeLatestActivity($this->userId, $this->userService->calculateLatestActivityTimeToStore(
//            $knownTime->copy()->subDay()->getTimestamp()
//        ));
//
//        if(config('intercomeo.last_request_buffer_unit') !== UserService::$timeUnits['hour']){
//            $this->fail('Expected default config value has been overridden somewhere');
//        }
//
//        $valueBeforeUpdate = $this->userService->getLastRequestAt($this->userService->getUser($this->userId));
//
//        event(new ApplicationReceivedRequest($this->userId, $knownTime->getTimestamp()));
//
//        $this->assertEquals(
//            Carbon::createFromTimestampUTC($valueBeforeUpdate)->addDay()->getTimestamp(),
//            $this->userService->getLastRequestAt($this->userService->getUser($this->userId))
//        );
//    }

    public function test_do_no_store_update_last_request_at_when_not_required()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $knownTime = Carbon::createFromTimestampUTC(time());
//
//        // some random time rounded down to the hour, and then half an hour added.
//        $knownTimeRoundedDownToHour = $knownTime->copy()->minute(0)->second(0);
//        $firstTimeSet = $this->userService->calculateLatestActivityTimeToStore(
//            $knownTimeRoundedDownToHour->copy()->addMinutes(30)->getTimestamp()
//        );
//
//        $this->userService->storeLatestActivity($this->userId, $firstTimeSet);
//
//        if(config('intercomeo.last_request_buffer_unit') !== UserService::$timeUnits['hour']){
//            $this->fail('Expected default config value has been overridden somewhere');
//        }
//
//        $secondTimeSet = $knownTimeRoundedDownToHour->copy()->addMinutes(rand(32, 58))->getTimestamp();
//
//        event(new ApplicationReceivedRequest($this->userId, $secondTimeSet));
//
//        $this->assertNotEquals(
//            $secondTimeSet,
//            $this->userService->getLastRequestAt($this->userService->getUser($this->userId))
//        );
//
//        $this->assertEquals(
//            $firstTimeSet,
//            $this->userService->getLastRequestAt($this->userService->getUser($this->userId))
//        );
//    }
}
