<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    protected $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function collection()
    {
        return Product::where('shop_id', $this->shopId)
            ->with('category')
            ->get()
            ->map(function ($product) {
                return [
                    'ID' => $product->id,
                    'Nom' => $product->name,
                    'Slug' => $product->slug,
                    'Description' => $product->description,
                    'Prix' => $product->price,
                    'Stock' => $product->stock,
                    'Catégorie' => $product->category->name ?? 'N/A',
                    'Date' => $product->created_at->format('d/m/Y'),
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Nom', 'Slug', 'Description', 'Prix', 'Stock', 'Catégorie', 'Date'];
    }
}
