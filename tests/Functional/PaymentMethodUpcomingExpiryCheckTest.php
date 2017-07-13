<?php

namespace Railroad\Intercomeo\Tests;


class PaymentMethodUpcomingExpiryCheckTest extends TestCase
{
    public function test_its_alive_ok_delete_this_test_now()
    {
        $expected = 'PaymentMethodUpcomingExpiryCheck->handle() was successfully triggered';
        $result = $this->paymentMethodUpcomingExpiryCheck->handle();

        $this->assertEquals($expected, $result);
    }

    public function test_ignore_user_with_payment_method_that_is_not_expiring()
    {
        $this->markTestIncomplete();
    }

    public function test_collect_user_with_payment_method_that_is_not_expiring()
    {
        $this->markTestIncomplete();
    }

    public function test_multiple_users_both_expiring_and_not()
    {
        $this->markTestIncomplete();
    }
}