<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Railroad\Intercomeo\Providers\IntercomeoServiceProvider;
use Railroad\Intercomeo\Services\TagService;

class TestCase extends BaseTestCase
{
    /**
     * @var Generator
     */
    protected $faker;
    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var $intercomClient \Intercom\IntercomClient
     */
    protected $intercomClient;

    /**
     * @var $tagService TagService
     */
    protected $tagService;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('cache:clear', []);

        $this->faker = $this->app->make(Generator::class);

        Carbon::setTestNow(Carbon::now());

        /*
         * created as singleton in service provide because we need to set the api credentials
         */
        $intercomClient = resolve('Intercom\IntercomClient');
        $this->intercomClient = $intercomClient;

        $this->tagService = $this->app->make(TagService::class);
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
