<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Intercom\IntercomClient;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Railroad\Intercomeo\Events\MemberAdded;
use Railroad\Intercomeo\Providers\IntercomeoServiceProvider;
use Railroad\Intercomeo\Repositories\IntercomUsersRepository;
use Railroad\Intercomeo\Services\UserService;
use Railroad\Intercomeo\Services\TagService;
use Railroad\Intercomeo\Services\LatestActivityService;

class TestCase extends BaseTestCase
{
    /** * @var Generator */
    protected $faker;

    /** * @var DatabaseManager */
    protected $databaseManager;

    /** * @var IntercomClient */
    protected $intercomClient;

    /** * @var TagService */
    protected $tagService;

    /** @var LatestActivityService */
    protected $latestActivityService;

    /** @var IntercomUsersRepository */
    protected $usersRepository;

    /** @var UserService */
    protected $intercomService;

    protected $userId;
    protected $email;
    protected $tags;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', []);
        $this->artisan('cache:clear', []);

        $this->faker = $this->app->make(Generator::class);

        Carbon::setTestNow(Carbon::now());

        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $intercomClient = resolve('Intercom\IntercomClient');
        $this->intercomClient = $intercomClient;

        // todo: change to reflect name change to Intercomeo\UserService
        $this->intercomService = $this->app->make(UserService::class);

        $this->tagService = $this->app->make(TagService::class);

        $this->latestActivityService = $this->app->make(LatestActivityService::class);
        $this->usersRepository = $this->app->make(IntercomUsersRepository::class);

        $this->userId = $this->faker->randomNumber(6);
        $this->email = $this->faker->email;

        $numberOfTagsToAdd = rand(1, 3);
        $this->tags = [];

        for($i = 0; $i < $numberOfTagsToAdd; $i++){
            $this->tags[] = $this->faker->word;
        }

        event(new MemberAdded($this->userId, $this->email, $this->tags)); // creates user for test
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteUser($this->userId);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [IntercomeoServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $defaultConfig = require(__DIR__ . '/../config/intercomeo.php');

        $app['config']->set('intercomeo.tables', $defaultConfig['tables']);
        $app['config']->set('intercomeo.database_connection_name', 'testbench');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );
        $app['config']->set('intercomeo.access_token', env('INTERCOM_ACCESS_TOKEN'));

        $app['config']->set('intercomeo.last_request_buffer_amount', (integer) env('LAST_REQUEST_BUFFER_AMOUNT'));
        $app['config']->set('intercomeo.last_request_buffer_unit', env('LAST_REQUEST_BUFFER_UNIT'));
        $app['config']->set('intercomeo.level_to_round_down_to', env('LEVEL_TO_ROUND_DOWN_TO'));
    }

    /**
     * @param string|integer $userId
     */
    protected function deleteUser($userId){
        $user = null;
        $userDeleted = false;

        $this->intercomClient->users->deleteUser('', ['user_id' => $userId]);

        try{
            $user = $this->intercomClient->users->getUsers(['user_id' => $userId]);
        }catch (\GuzzleHttp\Exception\RequestException $e){

            $classIsCorrect = get_class($e) === \GuzzleHttp\Exception\ClientException::class;

            $strposOne = strpos(
                $e->getMessage(),
                'Client error: `GET https://api.intercom.io/users?user_id'
            );

            $strposTwo = strpos(
                $e->getMessage(),
                '","errors":[{"code":"not_found","message":"User Not Found"}]}'
            );

            // must be strict, strpos will troll u: php.net/manual/en/function.strpos.php → "Return Values"
            $errorMessageIsAsExpected = ($strposOne !== false) && ($strposTwo !== false);

            $userDeleted = $classIsCorrect && $errorMessageIsAsExpected;
        }

        $noUserFetched = is_null($user);

        $successfulDelete = $noUserFetched && $userDeleted;

        if(!$successfulDelete){
            // No need to add another assertion to every test. Just cause to test to fail if this does.
            $this->assertTrue($successfulDelete);
        }
    }
}
