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
        Carbon::setTestNow(Carbon::createFromTimestampUTC(time()));

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
        Carbon::setTestNow(Carbon::createFromTimestampUTC(time()));
        $user = $this->createUser();
        $userId = $user->user_id;

        $knownTime = Carbon::now();

        $timeThatWillBeReplaced = $knownTime->copy()->subDay()->timestamp;

        $this->intercomeoService->storeLatestActivity(
            $user,
            $this->intercomeoService->calculateLatestActivityTimeToStore($timeThatWillBeReplaced)
        );

        // end of set up, test case proper start

        $valueBeforeUpdate = $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($userId));

        event(new ApplicationReceivedRequest($userId, $knownTime->timestamp));

        $valueBeforeUpdateDayAdded = Carbon::createFromTimestampUTC($valueBeforeUpdate)->addDay()->getTimestamp();

        $this->assertEquals(
            $valueBeforeUpdateDayAdded,
            $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($userId))
        );
    }

    public function test_do_no_store_update_last_request_at_when_not_required()
    {
        // set up
        Carbon::setTestNow(Carbon::createFromTimestampUTC(time()));
        $user = $this->createUser();
        $userId = $user->user_id;

        $knownTime = Carbon::now();

        $timeThatWillNotBeReplaced = $knownTime->copy()->timestamp;

        $this->intercomeoService->storeLatestActivity(
            $user,
            $this->intercomeoService->calculateLatestActivityTimeToStore($timeThatWillNotBeReplaced)
        );

        // end of set up, test case proper start

        $valueBeforeUpdate = $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($userId));

        event(new ApplicationReceivedRequest($userId, $knownTime->timestamp));

        $valueBeforeUpdateUnmodified = Carbon::createFromTimestampUTC($valueBeforeUpdate)->getTimestamp();

        $this->assertEquals(
            $valueBeforeUpdateUnmodified,
            $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($userId))
        );
    }

    public function test_get_tags_for_user(){

        // setup

        $generatedTags = [];
        for ($i = 1; $i <= rand(2,5); $i++) {
            $generatedTags[] = $this->faker->word;
        }
        $user = $this->createUser('', '', $generatedTags);

        // actual testing

        $tagsStored = $this->intercomeoService->getTagsFromUser($user);

        sort($generatedTags);
        sort($tagsStored);

        $this->assertEquals($generatedTags, $tagsStored);
    }

    public function test_add_tags_to_user(){

        // setup

        $generatedTags = [];
        for ($i = 1; $i <= rand(1,3); $i++) {
            $generatedTags[] = $this->faker->word;
        }

        $moreGeneratedTags = [];
        for ($i = 1; $i <= rand(1,3); $i++) {
            $moreGeneratedTags[] = $this->faker->word;
        }

        $user = $this->createUser('', '', $generatedTags);

        // actual testing

        $this->intercomeoService->tagUsers([$user], $moreGeneratedTags);

        $combinedTags = array_merge($generatedTags, $moreGeneratedTags);

        $tagsStored = $this->intercomeoService->getTagsFromUser($user);

        sort($combinedTags);
        sort($tagsStored);

        $this->assertEquals($combinedTags, $tagsStored);
    }

    public function test_add_single_tag_to_multiple_user()
    {
        // setup

        $generatedTags = [];
        for ($i = 1; $i <= rand(1,3); $i++) {
            $generatedTags[] = $this->faker->word;
        }

        $additionalTag = $this->faker->word;

        $userOne = $this->createUser('', '', $generatedTags);
        $userTwo = $this->createUser('', '', $generatedTags);

        // actual testing

        $this->intercomeoService->tagUsers([$userOne, $userTwo], $additionalTag);

        $tagsStoredForUserOne = $this->intercomeoService->getTagsFromUser($userOne);
        $tagsStoredForUserTwo = $this->intercomeoService->getTagsFromUser($userTwo);

        $this->assertEquals($tagsStoredForUserOne, $tagsStoredForUserTwo);

        $combinedTags = array_merge(
            $generatedTags,
            [$additionalTag]
        );

        sort($combinedTags);
        $tagsStored = array_unique(
            array_merge($tagsStoredForUserOne, $tagsStoredForUserTwo),
            SORT_STRING
        );
        sort($tagsStored);

        $this->assertEquals($combinedTags, $tagsStored);
    }

    public function test_add_multiple_tags_to_multiple_users()
    {
        // setup

        $generatedTags = [];
        $additionalTags = [];

        for ($i = 1; $i <= rand(1,3); $i++) {
            $generatedTags[] = $this->faker->word;
        }

        for ($i = 1; $i <= rand(2,3); $i++) {
            $additionalTags[] = $this->faker->word;
        }

        $userOne = $this->createUser('', '', $generatedTags);
        $userTwo = $this->createUser('', '', $generatedTags);

        // actual testing

        $this->intercomeoService->tagUsers([$userOne, $userTwo], $additionalTags);

        $tagsStoredForUserOne = $this->intercomeoService->getTagsFromUser($userOne);
        $tagsStoredForUserTwo = $this->intercomeoService->getTagsFromUser($userTwo);

        $this->assertEquals($tagsStoredForUserOne, $tagsStoredForUserTwo);

        $combinedTags = array_merge(
            $generatedTags,
            $additionalTags
        );

        sort($combinedTags);
        $tagsStored = array_unique(
            array_merge($tagsStoredForUserOne, $tagsStoredForUserTwo),
            SORT_STRING
        );
        sort($tagsStored);

        $this->assertEquals($combinedTags, $tagsStored);
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
