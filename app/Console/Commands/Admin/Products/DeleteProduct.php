<?php

namespace App\Console\Commands\Admin\Products;

use App\Models\Product;
use Illuminate\Console\Command;

class DeleteProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:products:delete {product_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the product.';

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

        $product = (new Product())->find($product_id);

        if ($product) {
            $this->info('If you delete parent product, the child will be delete too.');
            if ($this->confirm('Are you sure delete product: ' . $product->name . ' ?')) {
                $product->delete();
                $this->info('Product ' . $product->name . ' Deleted.');
            }
        } else {
            $this->error('Product not found.');
        }
        
    }
}
