<?php

namespace App\Jobs\Billing\Order;

use App\Jobs\Job;
use App\Models\Order;

class AutoClose extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Order::autoClose();
    }
}
