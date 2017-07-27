<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Events\ApplicationReceivedRequest;

class IntercomeoServiceTest extends TestCase
{

    public function test_create_users_single_users()
    {
        $userDetails = $this->generateUserDetails();
        $userId = $this->getUserIdForGeneratedUser($userDetails);

        $this->intercomeoService->createUsers($userId);

        $userReturnedFromIntercom = $this->intercomeoService->getUser($userId);

        $this->assertIsUser($userReturnedFromIntercom, $userId);
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

        $this->intercomeoService->createUsers(array_keys($userDetails));

        foreach($userDetails as $_userDetails){
            $userId = $this->getUserIdForGeneratedUser($_userDetails);

            $userReturnedFromIntercom = $this->intercomeoService->getUser($userId);

        $this->markTestIncomplete();
    }

    public function test_store_last_updated_in_database()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $knownDate = time();
//        $this->intercomeoService->storeLatestActivity($this->userId, $knownDate);
//
//        $this->assertEquals($knownDate, $this->usersRepository->getLastRequestAt($this->userId));
//    }

    public function test_first_store_user_attribute_last_request_at()
    {
        $this->markTestIncomplete('broken because of changes to TestCase in commit eb26f16a');
    }
//    {
//        $this->intercomeoService->storeLatestActivity($this->userId);
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
//        $this->intercomeoService->storeLatestActivity($this->userId, $knownDate);
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
//        $this->intercomeoService->storeLatestActivity($this->userId, $knownDate);
//
//        $userReturnedFromIntercom = $this->intercomClient->users->getUsers(['user_id' => $this->userId]);
//        $this->assertEquals($knownDate, $userReturnedFromIntercom->last_request_at);
//
//        $this->intercomeoService->storeLatestActivity($this->userId);
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
//        $this->intercomeoService->storeLatestActivity($this->userId, $this->intercomeoService->calculateLatestActivityTimeToStore(
//            $knownTime->copy()->subDay()->getTimestamp()
//        ));
//
//        if(config('intercomeo.last_request_buffer_unit') !== IntercomeoService::$timeUnits['hour']){
//            $this->fail('Expected default config value has been overridden somewhere');
//        }
//
//        $valueBeforeUpdate = $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($this->userId));
//
//        event(new ApplicationReceivedRequest($this->userId, $knownTime->getTimestamp()));
//
//        $this->assertEquals(
//            Carbon::createFromTimestampUTC($valueBeforeUpdate)->addDay()->getTimestamp(),
//            $this->intercomeoService->getLastRequestAt($this->intercomeoService->getUser($this->userId))
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
        $userDetails = $this->generateUserDetails();

        $userId = $this->getUserIdForGeneratedUser($userDetails);
        $tags = $this->getTagsForGeneratedUser($userDetails);

        $this->intercomeoService->tagUsers([$userId], $tags);

        $user = $this->intercomeoService->getUser($userId);

        $this->assertIsUser($user);

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
