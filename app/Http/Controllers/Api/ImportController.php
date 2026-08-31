<?php

namespace App\Http\Controllers\Api;

use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ImportController
{
    public function products(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $shop = Auth::user()->shop;

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $import = new ProductsImport($shop->id);
        Excel::import($import, $request->file('file'));

        return response()->json([
            'message' => 'Produits importés avec succès',
        ]);
    }
}
