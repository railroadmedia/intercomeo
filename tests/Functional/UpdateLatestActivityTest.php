<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\MemberAdded;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use Railroad\Intercomeo\Services\UpdateLatestActivity;

class UpdateLatestActivityTest extends TestCase
{
    /** @var UpdateLatestActivity */
    protected $updateLatestActivity;

    /** @var IntercomUsersRepository */
    protected $usersRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->updateLatestActivity = $this->app->make(UpdateLatestActivity::class);
        $this->usersRepository = $this->app->make(IntercomUsersRepository::class);
    }

    public function test_sending_user_attribute_last_request_at()
    {
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);
        event(new MemberAdded($userId, $email, [])); // creates user for test

        $this->updateLatestActivity->store($userId);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $userId]);
        $timeReturned = Carbon::createFromTimestampUTC($userReturnedFromIntercom->last_request_at);
        $anHourAgo = Carbon::now()->subHour();

        $this->assertTrue($timeReturned->gt($anHourAgo));

        $this->delete($userId);
    }

    public function test_sending_user_attribute_last_request_at_passing_in_timestamp()
    {
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);
        event(new MemberAdded($userId, $email, []));  // creates user for test

        $knownDate = time();

        $this->updateLatestActivity->store($userId, $knownDate);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $userId]);
        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);

        $this->delete($userId);
    }

    public function test_create_and_get_database_record()
    {
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);
        event(new MemberAdded($userId, $email, []));  // creates user for test

        $knownDate = time();

        $this->updateLatestActivity->store($userId, $knownDate);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $userId]);

        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);

        // ↑ same as other test-cases above, below is new ↓

        $this->assertEquals($knownDate, $this->usersRepository->getLastRequestAt($userId));

        $this->delete($userId);
    }
}
