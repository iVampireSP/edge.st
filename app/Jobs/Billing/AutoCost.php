<?php

namespace App\Jobs\Billing;

use App\Jobs\Job;
use App\Models\Invoice;

class AutoCost extends Job
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
        // ongoing 扣费

        Invoice::autoCost();
    }
}
