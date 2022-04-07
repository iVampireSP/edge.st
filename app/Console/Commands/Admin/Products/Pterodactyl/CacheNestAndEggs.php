<?php

namespace App\Console\Commands\Admin\Products\Pterodactyl;

use Illuminate\Console\Command;
use App\Models\Pterodactyl\Nest;
use App\Models\Pterodactyl\NestEgg;
use App\Http\Controllers\Pterodactyl\PanelController;

class CacheNestAndEggs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:products:pterodactyl:cache-all-nests-and-eggs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache all nests and eggs.';

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
        $panel = new PanelController();
        $this->info('Caching nests to the database...');

        $panel->refresh_nests();

        $this->info('Caching nest eggs to the database...');

        $panel->refresh_eggs();

        $this->info('All nests and egg are now cached.');

    }
}
