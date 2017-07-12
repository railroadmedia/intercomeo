<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Services\LatestActivityService;

class LatestActivityServiceTest extends TestCase
{
    public function test_store_last_updated_in_database()
    {
        $knownDate = time();
        $this->latestActivityService->store($this->userId, $knownDate);

        $this->assertEquals($knownDate, $this->usersRepository->getLastRequestAt($this->userId));
    }

    public function test_first_store_user_attribute_last_request_at()
    {
        $this->latestActivityService->store($this->userId);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);
        $timeReturned = Carbon::createFromTimestampUTC($userReturnedFromIntercom->last_request_at);
        $anHourAgo = Carbon::now()->subHour();

        $this->assertTrue($timeReturned->gt($anHourAgo));
    }

    public function test_first_store_user_attribute_last_request_at_passing_in_timestamp()
    {
        $knownDate = time();

        $this->latestActivityService->store($this->userId, $knownDate);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);

        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);
    }

    public function test_update_last_request_at_attribute_for_user_do_not_specify_time()
    {
        $knownDate = time()-10000;

        $this->latestActivityService->store($this->userId, $knownDate);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);
        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);

        $this->latestActivityService->store($this->userId);

        $this->assertTrue((time() - 10 ) < $this->usersRepository->getLastRequestAt($this->userId));
        $this->assertTrue((time() + 10 ) > $this->usersRepository->getLastRequestAt($this->userId));
    }

    public function test_store_update_last_request_at_when_required()
    {
        $knownTime = Carbon::createFromTimestampUTC(time());

        $this->latestActivityService->store($this->userId, $this->latestActivityService->calculateTimeToStore(
            $knownTime->copy()->subDay()->getTimestamp()
        ));

        if(config('intercomeo.last_request_buffer_unit') !== LatestActivityService::$timeUnits['hour']){
            $this->fail('Expected default config value has been overridden somewhere');
        }

        $valueBeforeUpdate = $this->intercomService->getLastRequestAt($this->intercomService->getUser($this->userId));

        event(new ApplicationReceivedRequest($this->userId, $knownTime->getTimestamp()));

        $this->assertEquals(
            Carbon::createFromTimestampUTC($valueBeforeUpdate)->addDay()->getTimestamp(),
            $this->intercomService->getLastRequestAt($this->intercomService->getUser($this->userId))
        );
    }

    public function test_do_no_store_update_last_request_at_when_not_required()
    {
        $knownTime = Carbon::createFromTimestampUTC(time());

        // some random time rounded down to the hour, and then half an hour added.
        $knownTimeRoundedDownToHour = $knownTime->copy()->minute(0)->second(0);
        $firstTimeSet = $this->latestActivityService->calculateTimeToStore(
            $knownTimeRoundedDownToHour->addMinutes(30)->getTimestamp()
        );

        $this->latestActivityService->store($this->userId, $firstTimeSet);

        if(config('intercomeo.last_request_buffer_unit') !== LatestActivityService::$timeUnits['hour']){
            $this->fail('Expected default config value has been overridden somewhere');
        }

        $secondTimeSet = $firstTimeSet + (600); // 10 minutes

        event(new ApplicationReceivedRequest($this->userId, $secondTimeSet));

        $this->assertNotEquals(
            $secondTimeSet,
            $this->intercomService->getLastRequestAt($this->intercomService->getUser($this->userId))
        );

        $this->assertEquals(
            $firstTimeSet,
            $this->intercomService->getLastRequestAt($this->intercomService->getUser($this->userId))
        );
    }
}
