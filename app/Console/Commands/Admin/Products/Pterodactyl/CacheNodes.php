<?php

namespace App\Console\Commands\Admin\Products\Pterodactyl;

use App\Models\Pterodactyl\Node;
use Illuminate\Console\Command;

class CacheNodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:products:pterodactyl:cache-nodes {page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache nodes from pterodactyl driver.';

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
        $this->info('Caching nodes to the database...');

        Node::syncNode($this->argument('page'));

        $this->info('All nodes cached.');

        $this->warn('You need modify name after this operation.');
    }
}
