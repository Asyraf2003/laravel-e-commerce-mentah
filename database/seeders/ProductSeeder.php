<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 8 produk contoh
        Product::factory()->count(8)->create();

        // Beberapa produk fixed (opsional)
        Product::query()->create([
            'name' => 'Kaos Polos',
            'slug' => 'kaos-polos',
            'description' => 'Kaos katun 24s nyaman dipakai.',
            'price' => 65000,
            'weight' => 200,
            'image_url' => 'https://picsum.photos/seed/kaospolos/600/400',
        ]);

        Product::query()->create([
            'name' => 'Jaket Hoodie',
            'slug' => 'jaket-hoodie',
            'description' => 'Hoodie fleece hangat.',
            'price' => 180000,
            'weight' => 700,
            'image_url' => 'https://picsum.photos/seed/hoodie/600/400',
        ]);
    }
}
