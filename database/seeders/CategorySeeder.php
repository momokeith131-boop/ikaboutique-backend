<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Électronique', 'slug' => 'electronique', 'description' => 'Produits électroniques'],
            ['name' => 'Vêtements', 'slug' => 'vetements', 'description' => 'Vêtements et accessoires'],
            ['name' => 'Maison', 'slug' => 'maison', 'description' => 'Articles de maison'],
            ['name' => 'Beauté', 'slug' => 'beaute', 'description' => 'Produits de beauté et soins'],
            ['name' => 'Alimentaire', 'slug' => 'alimentaire', 'description' => 'Produits alimentaires'],
            ['name' => 'Livres', 'slug' => 'livres', 'description' => 'Livres et publications'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
