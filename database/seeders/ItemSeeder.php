<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Laptop Gamer',
                'description' => 'High-performance gaming laptop with RGB keyboard',
                'price' => 1299.99,
                'stock_quantity' => 10,
            ],
            [
                'name' => 'Smartphone Premium',
                'description' => 'Latest smartphone with high-resolution camera',
                'price' => 899.99,
                'stock_quantity' => 15,
            ],
            [
                'name' => 'Wireless Headphones',
                'description' => 'Noise-cancelling wireless headphones with 30-hour battery life',
                'price' => 249.99,
                'stock_quantity' => 20,
            ],
            [
                'name' => 'Smart Watch',
                'description' => 'Fitness tracker with heart rate monitor and sleep analysis',
                'price' => 199.99,
                'stock_quantity' => 30,
            ],
            [
                'name' => 'Ultra HD Monitor',
                'description' => '32-inch 4K monitor with HDR support',
                'price' => 499.99,
                'stock_quantity' => 8,
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with long battery life',
                'price' => 49.99,
                'stock_quantity' => 50,
            ],
            [
                'name' => 'External SSD 1TB',
                'description' => 'Fast external SSD with USB-C connection',
                'price' => 179.99,
                'stock_quantity' => 25,
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB mechanical keyboard with customizable keys',
                'price' => 129.99,
                'stock_quantity' => 15,
            ],
            [
                'name' => 'Wireless Earbuds',
                'description' => 'True wireless earbuds with charging case',
                'price' => 99.99,
                'stock_quantity' => 40,
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
