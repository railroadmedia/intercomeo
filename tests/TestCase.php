<?php

namespace Railroad\Intercomeo\Tests;

use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\DatabaseManager;
use Intercom\IntercomClient;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Railroad\Intercomeo\Events\UserCreated;
use Railroad\Intercomeo\Providers\IntercomeoServiceProvider;
use Railroad\Intercomeo\Services\IntercomeoService;
use stdClass;

class TestCase extends BaseTestCase
{
    /** * @var Generator */
    protected $faker;

    /** * @var DatabaseManager */
    protected $databaseManager;

    /** * @var IntercomClient */
    protected $intercomClient;

    /** @var IntercomeoService $intercomeoService */
    protected $intercomeoService;

    protected $idsOfUsersAddedToIntercomToDeleteAfterTests;
    protected $email;
    protected $tags;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('cache:clear', []);

        // created as singleton in service provide because we need to set the api credentials
        $this->intercomClient = resolve('Intercom\IntercomClient');

        $this->faker = $this->app->make(Generator::class);
        $this->intercomeoService = $this->app->make(IntercomeoService::class);

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
//        $defaultConfig = require(__DIR__ . '/../config/intercomeo.php');

        $app['config']->set('intercomeo.app_id', env('INTERCOM_APP_ID'));
        $app['config']->set('intercomeo.hmac_secret', env('INTERCOM_HMAC_SECRET'));
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

        if(!empty($this->idsOfUsersAddedToIntercomToDeleteAfterTests)){
            foreach($this->idsOfUsersAddedToIntercomToDeleteAfterTests as $userId){

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
        }

        if($atLeastOneDeleteFailed){
            // No need to add another assertion to every test. Just cause to test to fail if this does.
            $this->assertFalse($atLeastOneDeleteFailed);
        }
    }

    // ↑ Methods above run automatically on every test.

    // ↓ Methods above are called only where they were added by the developer in specific test-cases.

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

    protected function getUserIdForGeneratedUser($user){
            return $user['userId'];
    }

    protected function getEmailForGeneratedUser($user){
            return $user['email'];
    }

    protected function getTagsForGeneratedUser($user){
            return $user['tags'];
    }

    /**
     * @param string|int $userId
     * @param $email
     * @param $tags
     * @return stdClass|false
     *
     * creates in external service for integration testing and adds to list to delete from Intercom at end of test
     *
     * very very similar to method storeUser below
     */
    protected function createUser($userId = '', $email = '', $tags = []){

        $userDetails = $this->generateUserDetails();

        if(empty($userId)){
            $userId = $this->getUserIdForGeneratedUser($userDetails);
        }
        if(empty($email)){
            $email = $this->getEmailForGeneratedUser($userDetails);
        }
        if(empty($tags)){
            $tags = $this->getTagsForGeneratedUser($userDetails);
        }

        event(new UserCreated($userId, $email, $tags));
        $user = $this->intercomeoService->getUser($userId);
        if(!is_object($user)){
            $this->assertIsUser($user);
        }
        $this->deleteFromIntercomAtEndOfTest($user);
        return $user;
    }

    /**
     * @param $userId
     * @param $email
     * @return bool|mixed
     *
     * Shortcut to storeUser method used in application, but adds to list to delete from Intercom at end of test
     *
     * very very similar to method createUser above
     */
    protected function storeUser($userId, $email){
        $user = $this->intercomeoService->storeUser($userId, $email);
        $this->deleteFromIntercomAtEndOfTest($user);
        return $user;
    }

    /**
     * @param stdClass $user
     * @return stdClass
     */
    protected function deleteFromIntercomAtEndOfTest(stdClass $user){
        $this->idsOfUsersAddedToIntercomToDeleteAfterTests[] = $user->user_id;
        return $user;
    }

    /**
     * @param stdClass $user
     * @param null|string|int $userId
     */
    protected function assertIsUser(stdClass $user, $userId = null){
        $this->assertTrue(
            (
                ($user->type === 'user')
                &&
                !empty($user->id)
                &&
                !is_null($userId) ? $user->user_id == $userId : true
                &&
                ($user->app_id === config('intercomeo.app_id'))
            )
        );
    }

    protected function assertIsTag(stdClass $tag){
        $this->assertTrue(
            (
                ($tag->type === 'tag')
                &&
                !empty($tag->id)
                &&
                ($tag->app_id === config('intercomeo.app_id'))
            )
        );
    }

    /**
     * @param $apiCallResult
     * @param $userId
     * @param null $email
     * @return bool
     */
    public function validUserCreated($apiCallResult, $userId, $email = null)
    {
        if (is_object($apiCallResult)) {
            if (get_class($apiCallResult) == stdClass::class) {
                return ($apiCallResult->type === 'user') &&
                    !empty($apiCallResult->id) &&
                    (!is_null($userId) ? $apiCallResult->user_id == $userId : true) &&
                    (!is_null($email) ? $apiCallResult->email == $email : true) &&
                    ($apiCallResult->app_id === config('intercomeo.app_id'));
            }
        }

        return false;
    }

    /**
     * @param stdClass $user
     * @return array
     */
    protected function getTagsFromUser($user)
    {
        try{
            $user = $this->intercomeoService->getUser($user->user_id);
        }catch(\Exception $exception){
            $this->fail('Exception caught by TestCase::getTagsFromUser');
        }

        $tags = $user->tags->tags;

        $tagsSimple = [];
        foreach ($tags as $tag) {
            $tagsSimple[] = $tag->name;
        }

        return $tagsSimple;
    }
}
