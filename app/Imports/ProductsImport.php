<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    protected $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function model(array $row)
    {
        // Trouver ou créer la catégorie
        $category = Category::firstOrCreate(
            ['name' => $row['categorie'] ?? 'Général'],
            ['slug' => Str::slug($row['categorie'] ?? 'General')]
        );

        return new Product([
            'shop_id' => $this->shopId,
            'category_id' => $category->id,
            'name' => $row['nom'],
            'slug' => $row['slug'] ?? Str::slug($row['nom']),
            'description' => $row['description'] ?? null,
            'price' => $row['prix'] ?? 0,
            'stock' => $row['stock'] ?? 0,
        ]);
    }
}
