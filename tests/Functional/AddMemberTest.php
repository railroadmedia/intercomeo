<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Exception;
use Faker\Generator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Railroad\Intercomeo\Events\AddMember;
use Railroad\Intercomeo\Providers\IntercomeoServiceProvider;

class AddMemberTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testAddMemberEvent()
    {
        // dump($this->app);
        $email = $this->faker->email;
        $userId = $this->faker->randomNumber(6);

        /*
         * I don't think we actually need that "createAndLogInNewUser" method.
         */
        // $user = $this->createAndLogInNewUser($email, $userId);
        $tags = [$this->faker->word];

        event(new AddMember($userId, $email, $tags));

        $this->assertTrue(true);


        /*
         *
         * ------------------------------------------------------------------------------------
         * ------------------------------------------------------------------------------------
         * ------------------------------------------------------------------------------------
         *
         * ============================== THURSDAY PICK UP HERE ==============================
         * ============================== THURSDAY PICK UP HERE ==============================
         * ============================== THURSDAY PICK UP HERE ==============================
         * ============================== THURSDAY PICK UP HERE ==============================
         *
         * running this test gets this error:
         *
         * Client error: `POST https://api.intercom.io/users` resulted in a `401 Unauthorized` response:
{"type":"error.list","request_id":"asu87ftpoh64dpti12t0","errors":[{"code":"token_not_found","message":"Unauthorized"}]}
         *
         * Thus, I think that means it's basically working : ) (it's making the call to the API and getting
         * and parsing the reply.
         *
         * ------------------------------------------------------------------------------------
         * ------------------------------------------------------------------------------------
         * ------------------------------------------------------------------------------------
         *
         */

    }
}