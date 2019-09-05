<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Railroad\Intercomeo\Jobs\IntercomSyncUser;
use Railroad\Intercomeo\Jobs\IntercomTagUsers;
use Railroad\Intercomeo\Jobs\IntercomTriggerEventForUser;
use Railroad\Intercomeo\Jobs\IntercomUnTagUsers;

class JobDispatchTest extends IntercomeoTestCase
{
    public function test_sync_user()
    {
        $userId = rand();
        $attributes = ['email' => $this->faker->email];

        $this->intercomeoClientMock->expects($this->once())
            ->method('create')
            ->with(array_merge(["user_id" => $userId], $attributes))
            ->willReturn((object) ['user_id' => $userId]);

        dispatch(new IntercomSyncUser($userId, $attributes));
    }

    public function test_tag_users()
    {
        $userIds = [
            rand(),
            rand(),
        ];
        $tagName = $this->faker->word;

        $this->intercomeoClientMock->expects($this->once())
            ->method('tag')
            ->with([
                'name' => $tagName,
                'users' => [
                    ['user_id' => $userIds[0]],
                    ['user_id' => $userIds[1]],
                ],
            ])
            ->willReturn((object) [
                'type' => 'tag',
                'name' => $tagName,
                'id' => rand(),
            ]);

        dispatch(new IntercomTagUsers($userIds, $tagName));
    }

    public function test_un_tag_users()
    {
        $userIds = [
            rand(),
            rand(),
        ];
        $tagName = $this->faker->word;

        $this->intercomeoClientMock->expects($this->once())
            ->method('tag')
            ->with([
                'name' => $tagName,
                'users' => [
                    [
                        'user_id' => $userIds[0],
                        "untag" => true,
                    ],
                    [
                        'user_id' => $userIds[1],
                        "untag" => true,
                    ],
                ],
            ])
            ->willReturn((object) [
                'type' => 'tag',
                'name' => $tagName,
                'id' => rand(),
            ]);

        dispatch(new IntercomUnTagUsers($userIds, $tagName));
    }

    public function test_trigger_event_for_user()
    {
        $userId = rand();
        $eventName = $this->faker->word;
        $eventDateTimeString = Carbon::instance($this->faker->dateTime)
            ->toDateTimeString();

        $this->intercomeoClientMock->expects($this->once())
            ->method('create')
            ->with([
                'event_name' => $eventName,
                'created_at' => Carbon::parse($eventDateTimeString)->timestamp,
                'user_id' => $userId,
            ]);

        dispatch(new IntercomTriggerEventForUser($userId, $eventName, $eventDateTimeString));
    }
}
