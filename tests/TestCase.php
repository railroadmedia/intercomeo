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

    /** @var IntercomUsersRepositoryTest */
    protected $usersRepository;

    /** @var UserService */
    protected $userService;

    protected $userIds;
    protected $email;
    protected $tags;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', []);
        $this->artisan('cache:clear', []);

        $this->faker = $this->app->make(Generator::class);

        $this->userService = $this->app->make(UserService::class);
        $this->tagService = $this->app->make(TagService::class);
        $this->usersRepository = $this->app->make(IntercomUsersRepository::class);

        Carbon::setTestNow(Carbon::now());
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteUsers();
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

        $app['config']->set(
            'intercomeo.last_request_buffer_amount',
            (integer) env('LAST_REQUEST_BUFFER_AMOUNT')
        );
        $app['config']->set('intercomeo.last_request_buffer_unit', env('LAST_REQUEST_BUFFER_UNIT'));
        $app['config']->set('intercomeo.level_to_round_down_to', env('LEVEL_TO_ROUND_DOWN_TO'));
    }

    /**
     *
     */
    protected function deleteUsers(){
        $user = null;
        $userDeleted = false;
        $atLeastOneDeleteFailed = false;

        foreach($this->userIds as $userId){
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
                $atLeastOneDeleteFailed = true;
            }
        }

        if($atLeastOneDeleteFailed){
            // No need to add another assertion to every test. Just cause to test to fail if this does.
            $this->assertFalse($atLeastOneDeleteFailed);
        }
    }

    // ↑ Methods above run automatically on every test.

    // ↓ Methods above are called only where they were added by the developer in specific test-cases.

    /**
     * @return IntercomClient
     */
    protected function instantiateIntercomClient(){
        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        return resolve('Intercom\IntercomClient');
    }

    /**
     * @param null $userId
     * @param null $email
     * @param null $tags
     * @param null $numberOfTagsToAdd
     * @return array
     */
    protected function generateUserDetails(
        $userId = null,
        $email = null,
        $tags = null,
        $numberOfTagsToAdd = null
    ){

        if(empty($userId)){
            $userId = $this->faker->randomNumber(6);
        }

        if(empty($email)){
            $email = $this->faker->email;
        }


        if(empty($tags)){
            $tags = [];
            if(empty($numberOfTagsToAdd)){
                $numberOfTagsToAdd = rand(1, 3);
            }
            for($i = 0; $i < $numberOfTagsToAdd; $i++){
                $tags[] = $this->faker->word;
            }
        }

        return ['userId' => $userId, 'email' => $email, 'tags' => $tags];
    }

    /**
     * @param $userId
     * @param $email
     * @param $tags
     *
     * creates in external service for integration testing
     */
    protected function createUser($userId, $email, $tags){
        $this->userIds[] = $userId;

        event(new MemberAdded($userId, $email, $tags));
    }

}
