<?php

namespace Railroad\Intercomeo\Services;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Intercom\IntercomClient;

class IntercomeoService
{
    /**
     * @var IntercomClient
     */
    private $intercomClient;

    /**
     * IntercomeoService constructor.
     *
     * @param  IntercomClient  $intercomClient
     */
    public function __construct(IntercomClient $intercomClient)
    {
        $this->intercomClient = $intercomClient;
    }

    /**
     *
     * Returns object with user data. Ex:
     * {
     *     "type": "user",
     *     "id": "530370b477ad7120001d",
     *     "user_id": "25",
     *     "email": "wash@serenity.io",
     *     "phone": "+1123456789",
     *     "name": "Hoban Washburne",
     *     "updated_at": 1392734388,
     *     "unsubscribed_from_emails": false,
     *     "last_request_at": 1397574667,
     *     "signed_up_at": 1392731331,
     *     "created_at": 1392734388,
     *     "session_count": 179,
     *     "pseudonym": null,
     *     "anonymous": false,
     *     "referrer": "https://example.org",
     *     "utm_campaign": null,
     *     "utm_content": null,
     *     "utm_medium": null,
     *     "utm_source": null,
     *     "utm_term": null,
     *     "custom_attributes": {
     *         "paid_subscriber" : true,
     *         "monthly_spend": 155.5,
     *         "team_mates": 1
     *     },
     *     "avatar": {
     *         "type":"avatar",
     *         "image_url": "https://example.org/128Wash.jpg"
     *     },
     *     "location_data": {
     *         "type": "location_data",
     *         "city_name": "Dublin",
     *         "continent_code": "EU",
     *         "country_code": "IRL",
     *         "country_name": "Ireland",
     *         "postal_code": null,
     *         "region_name": "Dublin",
     *         "timezone": "Europe/Dublin"
     *     },
     *     "social_profiles": {
     *         "type":"social_profile.list",
     *         "social_profiles": [
     *             {
     *                 "name": "Twitter",
     *                 "id": "1235d3213",
     *                 "username": "th1sland",
     *                 "url": "http://twitter.com/th1sland"
     *             }
     *         ]
     *     },
     *     "companies": {
     *         "type": "company.list",
     *         "companies": [
     *             {
     *                 "id" : "530370b477ad7120001e"
     *             }
     *         ]
     *     },
     *     "segments": {
     *         "type": "segment.list",
     *         "segments": [
     *             {
     *                 "id" : "5310d8e7598c9a0b24000002"
     *             }
     *         ]
     *     },
     *     "tags": {
     *         "type": "tag.list",
     *         "tags": [
     *             {
     *                 "id": "202"
     *             }
     *         ]
     *     }
     * }
     *
     * @param $userId
     *
     * @throws GuzzleException
     * @return object
     */
    public function getUser($userId)
    {
        return $this->intercomClient->get('users', ['user_id' => $userId]);
    }

    /**
     * Returns object with user data. Ex:
     * {
     *     "type": "user",
     *     "id": "530370b477ad7120001d",
     *     "user_id": "25",
     *     "email": "wash@serenity.io",
     *     "phone": "+1123456789",
     *     "name": "Hoban Washburne",
     *     "updated_at": 1392734388,
     *     "unsubscribed_from_emails": false,
     *     "last_request_at": 1397574667,
     *     "signed_up_at": 1392731331,
     *     "created_at": 1392734388,
     *     "session_count": 179,
     *     "pseudonym": null,
     *     "anonymous": false,
     *     "referrer": "https://example.org",
     *     "utm_campaign": null,
     *     "utm_content": null,
     *     "utm_medium": null,
     *     "utm_source": null,
     *     "utm_term": null,
     *     "custom_attributes": {
     *         "paid_subscriber" : true,
     *         "monthly_spend": 155.5,
     *         "team_mates": 1
     *     },
     *     "avatar": {
     *         "type":"avatar",
     *         "image_url": "https://example.org/128Wash.jpg"
     *     },
     *     "location_data": {
     *         "type": "location_data",
     *         "city_name": "Dublin",
     *         "continent_code": "EU",
     *         "country_code": "IRL",
     *         "country_name": "Ireland",
     *         "postal_code": null,
     *         "region_name": "Dublin",
     *         "timezone": "Europe/Dublin"
     *     },
     *     "social_profiles": {
     *         "type":"social_profile.list",
     *         "social_profiles": [
     *             {
     *                 "name": "Twitter",
     *                 "id": "1235d3213",
     *                 "username": "th1sland",
     *                 "url": "http://twitter.com/th1sland"
     *             }
     *         ]
     *     },
     *     "companies": {
     *         "type": "company.list",
     *         "companies": [
     *             {
     *                 "id" : "530370b477ad7120001e"
     *             }
     *         ]
     *     },
     *     "segments": {
     *         "type": "segment.list",
     *         "segments": [
     *             {
     *                 "id" : "5310d8e7598c9a0b24000002"
     *             }
     *         ]
     *     },
     *     "tags": {
     *         "type": "tag.list",
     *         "tags": [
     *             {
     *                 "id": "202"
     *             }
     *         ]
     *     }
     * }
     *
     * @param $userId
     * @param  array  $attributes
     *
     * @throws GuzzleException
     * @return object
     */
    public function syncUser($userId, array $attributes)
    {
        return $this->intercomClient->users->create(
            array_merge(["user_id" => $userId], $attributes)
        );
    }

    /**
     * Makes one request *per* tag.
     *
     * Returns object containing tag data. Ex:
     * {
     *     "type": "tag",
     *     "name": "my_tag",
     *     "id": "17513"
     * }
     *
     * @param  array  $userIds
     * @param  string  $tagName
     *
     * @throws GuzzleException
     * @return object
     */
    public function tagUsers(array $userIds, string $tagName)
    {
        $users = [];

        foreach ($userIds as $userId) {
            $users[] = ['user_id' => $userId];
        }

        return $this->intercomClient->tags->tag(['name' => $tagName, 'users' => $users]);
    }

    /**
     * Makes one request *per* tag.
     *
     * Returns object containing tag data. Ex:
     * {
     *     "type": "tag",
     *     "name": "my_tag",
     *     "id": "17513"
     * }
     *
     * @param  array  $userIds
     * @param  string  $tagName
     *
     * @throws GuzzleException
     * @return object
     */
    public function unTagUsers(array $userIds, string $tagName)
    {
        $users = [];

        foreach ($userIds as $userId) {
            $users[] = ['user_id' => $userId, "untag" => true];
        }

        return $this->intercomClient->tags->tag(['name' => $tagName, 'users' => $users]);
    }

    /**
     * @param $name
     * @param $dateTimeString
     * @param $userId
     *
     * @throws GuzzleException
     * @return void
     */
    public function triggerEventForUser($userId, string $name, string $dateTimeString)
    {
        $this->intercomClient->events->create(
            [
                'event_name' => $name,
                'created_at' => Carbon::parse($dateTimeString)->timestamp,
                'user_id' => $userId,
            ]
        );
    }
}
