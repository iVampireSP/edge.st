<?php

namespace App\Console\Commands\Admin\Products;

use App\Drivers\Driver;
use App\Drivers\Product\Test;
use App\Models\Product;
use Illuminate\Console\Command;

class AddProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:products:add {parent_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new product.';

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
        $product_id = $this->argument('parent_id');

        $product = new Product();

        if ($product_id) {
            $product = $product->find($product_id);

            if ($product) {
                $this->info('This product is child of ' . $product->name);
            } else {
                $this->error('Product not found.');
                return;
            }
        }

        // 新产品的名称
        $name = $this->ask('What is the name of the new product ?');

        $price = null;

        if ($product_id) {
            $price = $this->ask('Price of the new product.');
        }

        // 介绍
        $description = $this->ask('Description of the new product.');

        $hidden = $this->confirm('Hidden this product?', true);

        $feature = $this->confirm('Make this product feature?', false);

        if ($product_id) {
            $this->warn('You need fill controller column in database manually.');
        }
        // $driver = null;
        // if ($product_id) {
        //     $driver = $this->ask('The driver of this product.(not include App\Drivers)');

        //     $driver = 'App\Drivers\\' . $driver;

        //     // 检测Driver
        //     if (class_exists($driver)) {
        //         $hello = Driver::call($driver, 'hello');
        //         $this->info($hello);
        //     } else {
        //         $this->error('Driver not found.');
        //         return;
        //     }
        // }

        $product = $product->create([
            'name' => $name,
            'description' => $description,
            'product_id' => $product_id,
            'hidden' => $hidden,
            'price' => $price,
            'feature' => $feature,
        ]);

        $this->info("Product {$product->id}#{$product->name} added.");
    }
}
