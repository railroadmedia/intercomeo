<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Intercom\IntercomClient;
use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Intercomeo\Providers\IntercomeoServiceProvider;
use Railroad\Intercomeo\Services\IntercomeoService;

class IntercomeoTestCase extends BaseTestCase
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
     * @var PHPUnit_Framework_MockObject_MockObject|IntercomClient
     */
    protected $intercomeoClientMock;

    /**
     * @var IntercomeoService $intercomeoService
     */
    protected $intercomeoService;

    protected function setUp()
    {
        parent::setUp();

        $this->intercomeoClientMock = $this->getMockBuilder(IntercomClient::class)
            ->setMethods(['create', 'tag'])
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();

        $this->intercomeoClientMock->users = $this->intercomeoClientMock;
        $this->intercomeoClientMock->tags = $this->intercomeoClientMock;
        $this->intercomeoClientMock->events = $this->intercomeoClientMock;

        $this->app->instance(
            IntercomClient::class,
            $this->intercomeoClientMock
        );

        $this->faker = $this->app->make(Generator::class);
        $this->intercomeoService = $this->app->make(IntercomClient::class);

        Carbon::setTestNow(Carbon::now());
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [IntercomeoServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {

    }
}
