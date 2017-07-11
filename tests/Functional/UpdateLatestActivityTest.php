<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use Railroad\Intercomeo\Services\UpdateLatestActivity;

class UpdateLatestActivityTest extends TestCase
{
    public function test_sending_user_attribute_last_request_at()
    {
        $this->updateLatestActivity->store($this->userId);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);
        $timeReturned = Carbon::createFromTimestampUTC($userReturnedFromIntercom->last_request_at);
        $anHourAgo = Carbon::now()->subHour();

        $this->assertTrue($timeReturned->gt($anHourAgo));
    }

    public function test_sending_user_attribute_last_request_at_passing_in_timestamp()
    {
        $knownDate = time();

        $this->updateLatestActivity->store($this->userId, $knownDate);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);

        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);
    }

    public function test_update_last_request_at_attribute_for_user_do_not_specify_time()
    {
        $this->updateLatestActivity->store($this->userId);

        $this->assertTrue((time() - 10 ) < $this->usersRepository->getLastRequestAt($this->userId));
        $this->assertTrue((time() + 10 ) > $this->usersRepository->getLastRequestAt($this->userId));
    }

    public function test_store_last_updated_in_database()
    {
        $knownDate = time();
        $this->updateLatestActivity->store($this->userId, $knownDate);

        $this->assertEquals($knownDate, $this->usersRepository->getLastRequestAt($this->userId));
    }

    public function test_store_update_last_request_at_when_required()
    {
        $this->markTestIncomplete();
    }

    public function test_do_no_store_update_last_request_at_when_not_required()
    {
        $this->markTestIncomplete();
    }
}
