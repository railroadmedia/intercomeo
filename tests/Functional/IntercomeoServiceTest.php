<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\ApplicationReceivedRequest;
use Railroad\Intercomeo\Services\IntercomeoService;

class IntercomeoServiceTest extends TestCase
{
    public function test_create_single_user_using_storeUser_method()
    {
        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $email = $this->getEmailForGeneratedUser($userDetails);

        $this->storeUser($userId, $email);

        $userReturnedFromIntercom = $this->intercomeoService->getUser($userId);

        $this->assertTrue(!empty($userReturnedFromIntercom));
        $this->assertTrue(empty($userReturnedFromIntercom->error));
    }

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

            $this->assertTrue(!empty($userReturnedFromIntercom));
            $this->assertTrue(empty($userReturnedFromIntercom->error));
        }
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

    public function test_get_tags_for_user(){

        // setup

        $generatedTags = [];
        for ($i = 1; $i <= rand(2,5); $i++) {
            $generatedTags[] = $this->faker->word;
        }
        $user = $this->createUser('', '', $generatedTags);

        // actual testing

        $tagsStored = $this->getTagsFromUser($user);

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

        $this->intercomeoService->tagUsers($user, $moreGeneratedTags);

        $combinedTags = array_merge($generatedTags, $moreGeneratedTags);

        $tagsStored = $this->getTagsFromUser($user);

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

        $tagsStoredForUserOne = $this->getTagsFromUser($userOne);
        $tagsStoredForUserTwo = $this->getTagsFromUser($userTwo);

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

        $tagsStoredForUserOne = $this->getTagsFromUser($userOne);
        $tagsStoredForUserTwo = $this->getTagsFromUser($userTwo);

        $this->assertEquals($tagsStoredForUserOne, $tagsStoredForUserTwo);

        $combinedTags = array_merge(
            $generatedTags,
            $additionalTags
        );

        sort($combinedTags);
        array_unique($combinedTags);
        $tagsStored = array_unique(
            array_merge($tagsStoredForUserOne, $tagsStoredForUserTwo),
            SORT_STRING
        );
        sort($tagsStored);

        $this->assertEquals($combinedTags, $tagsStored);
    }

    public function test_remove_tags_from_user(){

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

        $this->intercomeoService->tagUsers([$user], $moreGeneratedTags);

        $combinedTags = array_merge($generatedTags, $moreGeneratedTags);

        $tagsStored = $this->getTagsFromUser($user);

        sort($combinedTags);
        sort($tagsStored);

        $this->assertEquals($combinedTags, $tagsStored);

        // actual testing

        $tagToRemove = array_splice($combinedTags, 0, 1);

        $this->intercomeoService->untagUsers($user, $tagToRemove);

        $usersTagsAfterRemoval = $this->getTagsFromUser($user);

        sort($usersTagsAfterRemoval);

        $this->assertEquals(
            $combinedTags,
            $usersTagsAfterRemoval
        );
    }

    private function last_request_at_processing($minutesAddedToNewRequestTime)
    {
        // ------------------------------ step 0 ------------------------------

        if(
            config('intercomeo.level_to_round_down_to') !== IntercomeoService::$timeUnits['hour']
            ||
            config('intercomeo.last_request_buffer_unit') !== IntercomeoService::$timeUnits['hour']
            ||
            config('intercomeo.last_request_buffer_amount') !== 1
        ){
            $this->fail(
                'Default "time block interval" of one hour overridden, ' .
                'test only works with default package values.'
            );
        }

        // ------------------------------ step 1: setup ------------------------------

        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $email = $this->getEmailForGeneratedUser($userDetails);

        $this->storeUser($userId, $email);

        $randomTime = Carbon::createFromTimestampUTC(rand(1000000000, 2000000000));
        $randomTimeRoundedToHour = Carbon::create(
            $randomTime->year, $randomTime->month, $randomTime->day, $randomTime->hour
        );

        $timeOne = $randomTimeRoundedToHour->copy()->addMinutes(rand(1,30))->getTimestamp();
        $timeTwo = $randomTimeRoundedToHour->copy()->addMinutes($minutesAddedToNewRequestTime)->getTimestamp();

        $user = $this->intercomeoService->getUser($userId);

        $this->intercomeoService->storeLatestActivity(
            $user,
            $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeOne)
        );

        // ------------------------------ step 2: test ------------------------------

        event(new ApplicationReceivedRequest($userId, $email, $timeOne, $timeTwo));

        // ------------------------------ step 3: assertions ------------------------------

        $user = $this->intercomeoService->getUser($userId);

        $timeTwoRoundedDown = $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeTwo);

        return $timeTwoRoundedDown === $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeOne);
    }

    public function test_last_request_at_processing_no_update()
    {
        $this->assertTrue($this->last_request_at_processing(rand(31,59)));
    }

    public function test_last_request_at_processing_update()
    {
        $this->assertFalse($this->last_request_at_processing(rand(60,666)));
    }

    public function test_can_pass_value_through_roundTimeDownForLatestActivityRecord_repeatedly_without_change()
    {
        $randomTime = Carbon::createFromTimestampUTC(rand(1000000000, 2000000000));
        $randomTimeRoundedToHour = Carbon::create(
            $randomTime->year, $randomTime->month, $randomTime->day, $randomTime->hour
        );

        $timeOne = $randomTimeRoundedToHour->copy()->getTimestamp();
        $timeTwo = $randomTimeRoundedToHour->copy()->addMinutes(rand(1,59))->getTimestamp();

        $timeThree = $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeOne);
        $timeFour = $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeTwo);
        $timeFive = $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeFour);
        $timeSix = $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeFive);
        $timeSeven = $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeSix);

        $arrayOfValuesThatShouldAllBeTheSame = [
            $timeTwo,
            $timeThree,
            $timeFour,
            $timeFive,
            $timeSix,
            $timeSeven
        ];

        $this->assertTrue(
            count(array_unique([$arrayOfValuesThatShouldAllBeTheSame])) === 1
        );

    }

    public function test_processing_correctly_sub_hour()
    {
        $minutesAddedToNewRequestTime = rand(1,59);

        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $email = $this->getEmailForGeneratedUser($userDetails);

        $this->storeUser($userId, $email);

        $randomTime = Carbon::createFromTimestampUTC(rand(1000000000, 2000000000));
        $randomTimeRoundedToHour = Carbon::create(
            $randomTime->year, $randomTime->month, $randomTime->day, $randomTime->hour
        );

        $timeOne = $randomTimeRoundedToHour->copy()->addMinutes(rand(1,30))->getTimestamp();
        $timeTwo = $randomTimeRoundedToHour->copy()->addMinutes($minutesAddedToNewRequestTime)->getTimestamp();

        $this->assertEquals(
            $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeOne),
            $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeTwo)
        );
    }

    public function test_processing_correctly_over_hour()
    {
        $minutesAddedToNewRequestTime = rand(60, 666);

        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $email = $this->getEmailForGeneratedUser($userDetails);

        $this->storeUser($userId, $email);

        $randomTime = Carbon::createFromTimestampUTC(rand(1000000000, 2000000000));
        $randomTimeRoundedToHour = Carbon::create(
            $randomTime->year, $randomTime->month, $randomTime->day, $randomTime->hour
        );

        $timeOne = $randomTimeRoundedToHour->copy()->addMinutes(rand(1,30))->getTimestamp();
        $timeTwo = $randomTimeRoundedToHour->copy()->addMinutes($minutesAddedToNewRequestTime)->getTimestamp();

        $this->assertNotEquals(
            $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeOne),
            $this->intercomeoService->roundTimeDownForLatestActivityRecord($timeTwo)
        );
    }
}
