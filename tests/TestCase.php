<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Exception;
use Faker\Generator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Railroad\Intercomeo\Providers\IntercomeoServiceProvider;
use Illuminate\Auth\AuthManager;

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

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', []);
        $this->artisan('cache:clear', []);

        $this->faker = $this->app->make(Generator::class);
        $this->databaseManager = $this->app->make(DatabaseManager::class);

        Carbon::setTestNow(Carbon::now());
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

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );

        $app->register(IntercomeoServiceProvider::class);
    }

    /*
     * Hey, I'm pretty sure we don't actually need this. (Jonathan, Wed June 28th)
     */
    /**
     * @param null $email
     * @param null $userId
     * @return array
     */
//    public function createAndLogInNewUser($email = null, $userId = null)
//    {
//        if(empty($email)){
//            $email = $this->faker->email;
//        }
//
//        if(empty($userId)){
//            $userId = $this->faker->randomNumber(6);
//        }
//
//        // from "users" or "intercom_users" ?
//        $id = $this->databaseManager->connection()->query()->from('intercom_users')->insertGetId(
//            [
//                'email' => $email,
//                'user_id' => $userId
//            ]
//        );
//
//        return [
//            'id' => $id,
//            'email' => $email,
//            'user_id' => $userId
//        ];
//    }

}
