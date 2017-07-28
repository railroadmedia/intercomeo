<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Services\IntercomeoService;

class IntercomeoServiceTest extends TestCase
{
    /**
     * interacts with Intercom API
     */
    public function test_create_single_user_using_storeUser_method()
    {
        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $email = $this->getEmailForGeneratedUser($userDetails);

        $this->storeUser($userId, $email);

        $userReturnedFromIntercom = $this->intercomeoService->getUser($userId);
        $this->assertTrue($this->intercomeoService->validUserCreated($userReturnedFromIntercom, $userId));
    }

    /**
     * interacts with Intercom API
     */
    public function test_create_users_multiple_users()
    {
        $numberToCreate = rand(3, 6);
        $userDetails = [];
        for ($i = 1; $i <= $numberToCreate; $i++) {
            $_userDetails = $this->generateUserDetails();
            $userId = $this->getUserIdForGeneratedUser($_userDetails);
            $userDetails[$userId] = $_userDetails;
        }

        foreach($userDetails as $_userDetails){
            $userId = $this->getUserIdForGeneratedUser($_userDetails);
            $email = $this->getEmailForGeneratedUser($_userDetails);

            $this->storeUser($userId, $email);
        }

        foreach($userDetails as $_userDetails){
            $userId = $this->getUserIdForGeneratedUser($_userDetails);

            $userReturnedFromIntercom = $this->intercomeoService->getUser($userId);

            $this->assertTrue($this->intercomeoService->validUserCreated($userReturnedFromIntercom, $userId));
        }
    }

    /**
     * interacts with database
     * does *not* interact with Intercom API
     */
    public function test_store_last_updated_in_database()
    {
        $user = $this->createUser();

        $knownDate = time();
        $this->intercomeoService->storeLatestActivity($user, $knownDate);

        $this->assertEquals($knownDate, $this->usersRepository->getLastRequestAt($user->user_id));
    }

    /**
     * interacts with database
     * does *not* interact with Intercom API
     */
    public function test_when_user_already_exists_intercom_user_create_updates_only_supplied_fields()
    {
        $user = $this->createUser();

        $this->intercomeoService->storeLatestActivity($user, time());

        $userReturned = $this->intercomeoService->getUser($user->user_id);

        $this->assertEquals($user->email, $userReturned->email);
    }

    public function test_store_user_attribute_last_request_at_for_first_time()
    {
        $user = $this->createUser();

        $this->intercomeoService->storeLatestActivity($user);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $user->user_id]);
        $timeReturned = Carbon::createFromTimestampUTC($userReturnedFromIntercom->last_request_at);
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        $fiveSecondsFromNow = Carbon::now()->addSeconds(5);

        $this->assertTrue($timeReturned->gt($fiveMinutesAgo) && $timeReturned->lt($fiveSecondsFromNow));
    }

    public function test_store_user_attribute_last_request_at_for_first_time_and_passing_in_timestamp()
    {
        $user = $this->createUser();
        $knownDate = rand(1000000000, 2000000000);

        $this->intercomeoService->storeLatestActivity($user, $knownDate);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $user->user_id]);

        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);
    }

    public function test_update_last_request_at_attribute_for_user_do_not_specify_time()
    {
        Carbon::setTestNow(Carbon::createFromTimestampUTC(time()));
        $user = $this->createUser();
        $firstTime = Carbon::now()->subWeek();
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        $fiveSecondsFromNow = Carbon::now()->addSeconds(5);

        $this->intercomeoService->storeLatestActivity($user, $firstTime->getTimestamp());

        $this->intercomeoService->storeLatestActivity($user);

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $user->user_id]);
        $timeReturned = Carbon::createFromTimestampUTC($userReturnedFromIntercom->last_request_at);

        $foo = $timeReturned->gt($fiveMinutesAgo);
        $bar = $timeReturned->lt($fiveSecondsFromNow);

        $success = $foo && $bar;

        $this->assertTrue($success);
    }

    public function test_update_last_request_at_attribute_for_user_specifying_time()
    {
        Carbon::setTestNow(Carbon::createFromTimestampUTC(rand(1000000000, 2000000000)));
        $user = $this->createUser();
        $firstTime = Carbon::now()->subWeek();
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        $fiveSecondsFromNow = Carbon::now()->addSeconds(5);

        $foo = $firstTime->getTimestamp();

        $this->intercomeoService->storeLatestActivity($user, $firstTime->getTimestamp());

        $this->intercomeoService->storeLatestActivity($user, Carbon::now()->getTimestamp());

        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $user->user_id]);
        $timeReturned = Carbon::createFromTimestampUTC($userReturnedFromIntercom->last_request_at);

        $foo = $timeReturned->gt($fiveMinutesAgo);
        $bar = $timeReturned->lt($fiveSecondsFromNow);

        $success = $foo && $bar;

        $this->assertTrue($success);
    }

    public function test_ensure_config_values_conform_to_expecations()
    {
        if(config('intercomeo.last_request_buffer_unit') !== IntercomeoService::$timeUnits['hour']){
            $this->fail(
                'Expected default config value ("intercomeo.last_request_buffer_unit") ' .
                'does not match allowed options (in "IntercomeoService::$timeUnits").'
            );
        }

        $this->assertTrue(true);
    }

    /*
     * update is required, time should be different
     * update is not required, times are the same
     */
    public function test_update_last_request_at_when_required()
    {
        // set up

        $user = $this->createUser();
        $userId = $user->user_id;

        $knownTime = Carbon::now();

        $this->intercomeoService->storeLatestActivity(
            $user,
            $this->intercomeoService->calculateLatestActivityTimeToStore($knownTime->copy()->subDay()->timestamp)
        );

        // end of set up, test case proper start

        $valueBeforeUpdate = $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($userId));

        event(new ApplicationReceivedRequest($userId, $knownTime->timestamp));

        $this->assertEquals(
            Carbon::createFromTimestampUTC($valueBeforeUpdate)->addDay()->getTimestamp(),
            $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($userId))
        );
    }

    public function test_do_no_store_update_last_request_at_when_not_required()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $knownTime = Carbon::createFromTimestampUTC(time());
//
//        // some random time rounded down to the hour, and then half an hour added.
//        $knownTimeRoundedDownToHour = $knownTime->copy()->minute(0)->second(0);
//        $firstTimeSet = $this->intercomeoService->calculateLatestActivityTimeToStore(
//            $knownTimeRoundedDownToHour->copy()->addMinutes(30)->getTimestamp()
//        );
//
//        $this->intercomeoService->storeLatestActivity($this->userId, $firstTimeSet);
//
//        if(config('intercomeo.last_request_buffer_unit') !== IntercomeoService::$timeUnits['hour']){
//            $this->fail('Expected default config value has been overridden somewhere');
//        }
//
//        $secondTimeSet = $knownTimeRoundedDownToHour->copy()->addMinutes(rand(32, 58))->getTimestamp();
//
//        event(new ApplicationReceivedRequest($this->userId, $secondTimeSet));
//
//        $this->assertNotEquals(
//            $secondTimeSet,
//            $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($this->userId))
//        );
//
//        $this->assertEquals(
//            $firstTimeSet,
//            $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($this->userId))
//        );
//    }

    // moved from TagServiceTest
    // moved from TagServiceTest
    // moved from TagServiceTest

    public function test_get_tags_for_user(){
        $this->markTestIncomplete();

        $tagsStored = $this->intercomeoService->getTagsForUser($this->userId);

        sort($this->tags);
        sort($tagsStored);

        $this->assertEquals($this->tags, $tagsStored);
    }

    public function test_add_tags_to_user(){
//        $numberOfTagsToAddInSecondBatch = rand(1, 3);
//        $tagsSecondBatch = [];
//
//        for($i = 0; $i < $numberOfTagsToAddInSecondBatch; $i++){
//            $tagsSecondBatch[] = $this->faker->word;
//        }
//
//        foreach($tagsSecondBatch as $tagInSecondBatch){
//            $this->intercomeoService->tagUsers($this->userId, $tagInSecondBatch);
//        }
//
//        $tags = array_merge($this->tags, $tagsSecondBatch);
//
//        $tagsStored = $this->intercomeoService->getTagsForUser($this->userId);
//
//        sort($tags);
//        sort($tagsStored);
//
//        $this->assertEquals($tags, $tagsStored);
    }

    public function test_add_single_tag_to_single_user_passed_as_int()
    {
        $userDetails = $this->generateUserDetails();

        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $tags = $this->getTagsForGeneratedUser($userDetails);

        $this->intercomeoService->tagUsers($userId, $tags);
    }

    public function test_add_single_tag_to_single_user_passed_in_array()
    {

        $this->markTestIncomplete();

        $userDetails = $this->generateUserDetails();

        // todo: intercomeoService->storeUser

        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $tags = $this->getTagsForGeneratedUser($userDetails);

        $this->intercomeoService->tagUsers([$userId], $tags);

        $user = $this->intercomeoService->getUser($userId);

        $this->assertTrue($this->intercomeoService->validUserCreated($user, $userId));

        $this->assertEquals($tags, $user->tags);
    }

    public function test_add_single_tag_to_multiple_user()
    {
        $this->markTestIncomplete();
    }

    public function test_add_multiple_tags_to_single_users()
    {
        $this->markTestIncomplete();
    }

    public function test_add_multiple_tags_to_multiple_users()
    {
        $this->markTestIncomplete();
    }

    public function test_remove_tags_from_user(){

        $this->markTestIncomplete();

        $randomIndexValue = rand(0, count($this->tags) - 1);

        $tagToRemove = $this->tags[$randomIndexValue];

        $this->intercomeoService->tagUsers($this->userId, $tagToRemove, true);

        $tagsStoredAfterUntag = $this->intercomeoService->getTagsForUser($this->userId);

        $tagsAfterUntag = $this->tags;

        array_splice($tagsAfterUntag, $randomIndexValue, 1);

        sort($tagsAfterUntag);
        sort($tagsStoredAfterUntag);

        $this->assertEquals($tagsAfterUntag, $tagsStoredAfterUntag);
    }

}
