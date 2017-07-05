<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\MemberAdded;
use Railroad\Intercomeo\Services\UpdateLatestActivity;

class UpdateLatestActivityTest extends TestCase
{
    /** @var  UpdateLatestActivity */
    protected $updateLatestActivity;

    protected function setUp()
    {
        parent::setUp();

        $this->updateLatestActivity = $this->app->make(UpdateLatestActivity::class);
    }

    public function test_sending_user_attribute_last_request_at()
    {
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);

        /*
         * We need a user for this test
         */
        event(new MemberAdded($userId, $email, []));

        $intercomUserId = $this->queryIntercomUsersTable->select()->where([
            ['email', $email],
            ['user_id', $userId]
        ])->get()->first()->intercom_user_id;

        $this->updateLatestActivity->send($userId);

        $userReturnedFromIntercom = $this->intercomClient->users->getUser($intercomUserId);
        $timeReturned = Carbon::createFromTimestampUTC($userReturnedFromIntercom->last_request_at);
        $anHourAgo = Carbon::now()->subHour();

        $this->assertTrue($timeReturned->gt($anHourAgo));

        $this->delete($userId);
    }

    public function test_sending_user_attribute_last_request_at_passing_in_timestamp()
    {
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);

        /*
         * We need a user for this test
         */
        event(new MemberAdded($userId, $email, []));

        $intercomUserId = $this->queryIntercomUsersTable->select()->where([['email', $email], ['user_id', $userId]])->get()->first()->intercom_user_id;

        /*
         * Not really necessary, we could have just taken the current time with `time()`, but might be
         * handy to have this available to copy-pasta in the coming days
         */
        $knownDate = Carbon::parse('1:37pm July 2st 2017', 'America/Vancouver');
        $knownDate = $knownDate->timezone('UTC');
        Carbon::setTestNow($knownDate);

        $this->updateLatestActivity->send($userId, $knownDate->timestamp);

        $userReturnedFromIntercom = $this->intercomClient->users->getUser($intercomUserId);

        $this->assertEquals($knownDate->timestamp, $userReturnedFromIntercom->last_request_at);

        $this->delete($userId);
    }
}
