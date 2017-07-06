<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Railroad\Intercomeo\Providers\IntercomeoServiceProvider;

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
     * @param string|integer $emailOrUserId
     *
     * If passing the user_id it must be an integer — any string passed will be assumed to be email
     */
    protected function deleteUser($emailOrUserId){
        $user = null;
        $userDeleted = false;

        $identifier = 'email';

        if(is_integer($emailOrUserId)){
            $identifier = 'user_id';
        }

        $this->intercomClient->users->deleteUser('', [$identifier => $emailOrUserId]);

        try{
            $user = $this->intercomClient->users->getUser('', [$identifier => $emailOrUserId]);
        }catch (\GuzzleHttp\Exception\RequestException $e){

            $classIsCorrect = get_class($e) === \GuzzleHttp\Exception\ClientException::class;

            $strposOne = strpos(
                $e->getMessage(),
                'Client error: `GET https://api.intercom.io/users/?email'
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
            // No need to add another assertion to every test. Just cause to test to fail if this.
            $this->assertTrue($successfulDelete);
        }
    }
}
