<?php

namespace App\Console\Commands\Admin\Products;

use App\Models\Product;
use Illuminate\Console\Command;

class EditProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:products:edit {product_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit a product';

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
        $product_id = $this->argument('product_id');

        $product = new Product();

        $product = $product->find($product_id);

        if ($product) {
            displayAndEditModel($this, $product);
        } else {
            $this->error('Product not found');
        }

        return;
    }
}
