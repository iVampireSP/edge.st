<?php

namespace App\Console\Commands\Admin\Products\Pterodactyl;

use App\Models\Pterodactyl\Location;
use Illuminate\Console\Command;

class CacheLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:products:pterodactyl:cache-locations {page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache locations.';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $this->info('Caching locations to the database...');

        Location::cacheLocations($this->argument('page'));

        $this->info('All locations cached.');

        $this->warn('You need modify name after this operation.');
    }
}
