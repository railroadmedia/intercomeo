<?php

namespace Railroad\Intercomeo\Commands;

use Illuminate\Console\Command;

class PaymentMethodUpcomingExpiryCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PaymentMethodUpcomingExpiryCheck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find all users with a payment method expiring soon.';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();


    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('PaymentMethodUpcomingExpiryCheck->handle() was successfully triggered');

        return 'PaymentMethodUpcomingExpiryCheck->handle() was successfully triggered';
    }
}